<?php

namespace App\Services;

use App\Models\CasafariProperty;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CasafariService
{
    protected ?string $apiKey;
    protected ?string $apiSecret;
    protected string $baseUrl;
    protected int $timeout;

    public function __construct()
    {
        $this->apiKey = config('services.casafari.api_key');
        $this->apiSecret = config('services.casafari.api_secret');
        $this->baseUrl = config('services.casafari.base_url', 'https://api.casafari.com');
        $this->timeout = config('services.casafari.timeout', 30);
    }

    /**
     * Create a configured HTTP client for Casafari API requests.
     */
    protected function client(): PendingRequest
    {
        return Http::baseUrl($this->baseUrl)
            ->timeout($this->timeout)
            ->withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ])
            ->withBasicAuth($this->apiKey, $this->apiSecret);
    }

    /**
     * Get properties from Casafari API.
     *
     * @param array $filters Filters to apply (country, city, property_type, etc.)
     * @param int $page Page number for pagination
     * @param int $perPage Items per page
     * @return array
     */
    public function getProperties(array $filters = [], int $page = 1, int $perPage = 100): array
    {
        try {
            $response = $this->client()
                ->get('/api/v1/properties', array_merge($filters, [
                    'page' => $page,
                    'per_page' => $perPage,
                ]));

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('Casafari API error', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return [];
        } catch (\Exception $e) {
            Log::error('Casafari API exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [];
        }
    }

    /**
     * Get a single property by ID from Casafari API.
     *
     * @param string $propertyId
     * @return array|null
     */
    public function getProperty(string $propertyId): ?array
    {
        try {
            $response = $this->client()->get("/api/v1/properties/{$propertyId}");

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('Casafari API error fetching property', [
                'property_id' => $propertyId,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('Casafari API exception fetching property', [
                'property_id' => $propertyId,
                'message' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Sync properties from Casafari to local database.
     *
     * @param array $filters Filters to apply when fetching properties
     * @return array Statistics about the sync operation
     */
    public function syncProperties(array $filters = []): array
    {
        $stats = [
            'total' => 0,
            'created' => 0,
            'updated' => 0,
            'errors' => 0,
        ];

        $page = 1;
        $hasMorePages = true;

        while ($hasMorePages) {
            $data = $this->getProperties($filters, $page);

            if (empty($data['data'])) {
                $hasMorePages = false;
                continue;
            }

            foreach ($data['data'] as $propertyData) {
                $stats['total']++;

                try {
                    $property = $this->createOrUpdateProperty($propertyData);

                    if ($property->wasRecentlyCreated) {
                        $stats['created']++;
                    } else {
                        $stats['updated']++;
                    }
                } catch (\Exception $e) {
                    $stats['errors']++;
                    Log::error('Error syncing property', [
                        'property_id' => $propertyData['id'] ?? 'unknown',
                        'message' => $e->getMessage(),
                    ]);
                }
            }

            // Check if there are more pages
            $hasMorePages = !empty($data['pagination']['next_page']);
            $page++;
        }

        return $stats;
    }

    /**
     * Create or update a property in the local database.
     *
     * @param array $data Property data from Casafari API
     * @return CasafariProperty
     */
    public function createOrUpdateProperty(array $data): CasafariProperty
    {
        return CasafariProperty::updateOrCreate(
            ['casafari_id' => $data['id']],
            [
                'reference' => $data['reference'] ?? null,
                'property_type' => $data['type'] ?? null,
                'listing_type' => $data['listing_type'] ?? null,
                'status' => $data['status'] ?? 'active',
                'address' => $data['address']['street'] ?? null,
                'city' => $data['address']['city'] ?? null,
                'region' => $data['address']['region'] ?? null,
                'postal_code' => $data['address']['postal_code'] ?? null,
                'country' => $data['address']['country'] ?? null,
                'latitude' => $data['coordinates']['latitude'] ?? null,
                'longitude' => $data['coordinates']['longitude'] ?? null,
                'price' => $data['price']['amount'] ?? null,
                'currency' => $data['price']['currency'] ?? 'EUR',
                'bedrooms' => $data['details']['bedrooms'] ?? null,
                'bathrooms' => $data['details']['bathrooms'] ?? null,
                'area_total' => $data['details']['area_total'] ?? null,
                'area_built' => $data['details']['area_built'] ?? null,
                'area_unit' => $data['details']['area_unit'] ?? 'm2',
                'year_built' => $data['details']['year_built'] ?? null,
                'description' => $data['description'] ?? null,
                'photos' => $data['photos'] ?? [],
                'main_photo_url' => $data['main_photo'] ?? ($data['photos'][0] ?? null),
                'features' => $data['features'] ?? [],
                'raw_data' => $data,
                'last_synced_at' => now(),
                'is_active' => $data['is_active'] ?? true,
            ]
        );
    }

    /**
     * Search properties by location.
     *
     * @param string $location City or region name
     * @return array
     */
    public function searchByLocation(string $location): array
    {
        return $this->getProperties(['location' => $location]);
    }

    /**
     * Get properties by type.
     *
     * @param string $type Property type (apartment, house, etc.)
     * @return array
     */
    public function getPropertiesByType(string $type): array
    {
        return $this->getProperties(['type' => $type]);
    }

    /**
     * Get property alerts (FSBO leads).
     *
     * @return array
     */
    public function getAlerts(): array
    {
        try {
            $response = $this->client()->get('/api/v1/alerts');

            if ($response->successful()) {
                return $response->json();
            }

            return [];
        } catch (\Exception $e) {
            Log::error('Casafari API exception fetching alerts', [
                'message' => $e->getMessage(),
            ]);

            return [];
        }
    }

    /**
     * Get comparable properties.
     *
     * @param string $propertyId
     * @return array
     */
    public function getComparables(string $propertyId): array
    {
        try {
            $response = $this->client()->get("/api/v1/properties/{$propertyId}/comparables");

            if ($response->successful()) {
                return $response->json();
            }

            return [];
        } catch (\Exception $e) {
            Log::error('Casafari API exception fetching comparables', [
                'property_id' => $propertyId,
                'message' => $e->getMessage(),
            ]);

            return [];
        }
    }

    /**
     * Test the API connection.
     *
     * @return bool
     */
    public function testConnection(): bool
    {
        try {
            $response = $this->client()->get('/api/v1/properties', ['per_page' => 1]);

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('Casafari API connection test failed', [
                'message' => $e->getMessage(),
            ]);

            return false;
        }
    }
}
