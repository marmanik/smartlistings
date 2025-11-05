<?php

namespace Tests\Unit;

use App\Models\CasafariProperty;
use App\Services\CasafariService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CasafariServiceTest extends TestCase
{
    use RefreshDatabase;

    protected CasafariService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new CasafariService();
    }

    public function test_can_instantiate_service(): void
    {
        $this->assertInstanceOf(CasafariService::class, $this->service);
    }

    public function test_get_properties_returns_array(): void
    {
        Http::fake([
            '*/api/v1/properties*' => Http::response([
                'data' => [
                    [
                        'id' => 'test-id-1',
                        'reference' => 'REF-001',
                        'type' => 'apartment',
                        'listing_type' => 'sale',
                        'status' => 'active',
                        'address' => [
                            'street' => '123 Main St',
                            'city' => 'Lisbon',
                            'region' => 'Lisboa',
                            'postal_code' => '1000-001',
                            'country' => 'PT',
                        ],
                        'coordinates' => [
                            'latitude' => 38.7223,
                            'longitude' => -9.1393,
                        ],
                        'price' => [
                            'amount' => 250000,
                            'currency' => 'EUR',
                        ],
                        'details' => [
                            'bedrooms' => 2,
                            'bathrooms' => 1,
                            'area_total' => 80,
                            'area_built' => 75,
                            'area_unit' => 'm2',
                        ],
                        'description' => 'Beautiful apartment',
                        'photos' => ['photo1.jpg'],
                        'features' => ['balcony', 'parking'],
                        'is_active' => true,
                    ],
                ],
                'pagination' => [
                    'current_page' => 1,
                    'next_page' => null,
                ],
            ], 200),
        ]);

        $result = $this->service->getProperties();

        $this->assertIsArray($result);
        $this->assertArrayHasKey('data', $result);
        $this->assertCount(1, $result['data']);
    }

    public function test_create_or_update_property(): void
    {
        $propertyData = [
            'id' => 'test-id-1',
            'reference' => 'REF-001',
            'type' => 'apartment',
            'listing_type' => 'sale',
            'status' => 'active',
            'address' => [
                'street' => '123 Main St',
                'city' => 'Lisbon',
                'region' => 'Lisboa',
                'postal_code' => '1000-001',
                'country' => 'PT',
            ],
            'coordinates' => [
                'latitude' => 38.7223,
                'longitude' => -9.1393,
            ],
            'price' => [
                'amount' => 250000,
                'currency' => 'EUR',
            ],
            'details' => [
                'bedrooms' => 2,
                'bathrooms' => 1,
                'area_total' => 80,
                'area_built' => 75,
                'area_unit' => 'm2',
            ],
            'description' => 'Beautiful apartment',
            'photos' => ['photo1.jpg'],
            'features' => ['balcony', 'parking'],
            'is_active' => true,
        ];

        $property = $this->service->createOrUpdateProperty($propertyData);

        $this->assertInstanceOf(CasafariProperty::class, $property);
        $this->assertEquals('test-id-1', $property->casafari_id);
        $this->assertEquals('apartment', $property->property_type);
        $this->assertEquals('Lisbon', $property->city);
        $this->assertEquals(250000, $property->price);
        $this->assertEquals(2, $property->bedrooms);
    }

    public function test_property_can_be_updated(): void
    {
        $initialData = [
            'id' => 'test-id-1',
            'reference' => 'REF-001',
            'type' => 'apartment',
            'price' => [
                'amount' => 250000,
                'currency' => 'EUR',
            ],
            'address' => ['city' => 'Lisbon'],
            'coordinates' => [],
            'details' => [],
        ];

        $property = $this->service->createOrUpdateProperty($initialData);
        $this->assertEquals(250000, $property->price);

        $updatedData = array_merge($initialData, [
            'price' => [
                'amount' => 275000,
                'currency' => 'EUR',
            ],
        ]);

        $updatedProperty = $this->service->createOrUpdateProperty($updatedData);
        $this->assertEquals(275000, $updatedProperty->price);
        $this->assertEquals($property->id, $updatedProperty->id);
    }

    public function test_test_connection_returns_boolean(): void
    {
        Http::fake([
            '*/api/v1/properties*' => Http::response(['data' => []], 200),
        ]);

        $result = $this->service->testConnection();

        $this->assertIsBool($result);
        $this->assertTrue($result);
    }

    public function test_test_connection_fails_on_error(): void
    {
        Http::fake([
            '*/api/v1/properties*' => Http::response(['error' => 'Unauthorized'], 401),
        ]);

        $result = $this->service->testConnection();

        $this->assertIsBool($result);
        $this->assertFalse($result);
    }
}
