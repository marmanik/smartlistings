# Casafari API Integration

This document describes the Casafari API integration for the SmartListings application.

## Overview

The Casafari integration allows SmartListings to fetch and manage property listings from Casafari's real estate data API. The integration includes:

- API client service for fetching property data
- Database storage for property listings
- Artisan command for syncing data
- Filament admin interface for managing properties

## Setup

### 1. Configure API Credentials

Add your Casafari API credentials to the `.env` file:

```env
CASAFARI_API_KEY=your_api_key_here
CASAFARI_API_SECRET=your_api_secret_here
CASAFARI_API_URL=https://api.casafari.com
CASAFARI_API_TIMEOUT=30
```

### 2. Run Database Migration

Run the migration to create the `casafari_properties` table:

```bash
php artisan migrate
```

### 3. Test the Connection

Test your API connection:

```bash
php artisan casafari:sync --test
```

## Features

### CasafariService

The `CasafariService` class (`app/Services/CasafariService.php`) provides methods for:

- **getProperties()** - Fetch properties from the API with filters
- **getProperty($id)** - Fetch a single property by ID
- **syncProperties()** - Sync properties from API to local database
- **createOrUpdateProperty()** - Create or update a property locally
- **searchByLocation()** - Search properties by location
- **getPropertiesByType()** - Filter properties by type
- **getAlerts()** - Get property alerts (FSBO leads)
- **getComparables()** - Get comparable properties
- **testConnection()** - Test API connectivity

### Artisan Commands

#### Sync Properties

Sync all properties from Casafari:

```bash
php artisan casafari:sync
```

Sync with filters:

```bash
# Filter by country
php artisan casafari:sync --country=PT

# Filter by city
php artisan casafari:sync --city=Lisbon

# Filter by property type
php artisan casafari:sync --type=apartment

# Combine multiple filters
php artisan casafari:sync --country=PT --city=Lisbon --type=apartment
```

### Filament Admin Interface

Access the Casafari properties in the Filament admin panel at `/admin/casafari-properties`.

Features:
- View all synced properties
- Search and filter by type, location, price, etc.
- Edit property details
- View property information including photos and features
- Bulk actions (delete, restore)

### Property Model

The `CasafariProperty` model includes:

**Attributes:**
- Property identification (casafari_id, reference)
- Property type and status
- Location data (address, city, country, coordinates)
- Property details (price, bedrooms, bathrooms, area)
- Media (photos array, main photo URL)
- Features and raw API data

**Scopes:**
- `active()` - Filter active properties
- `ofType($type)` - Filter by property type
- `inCity($city)` - Filter by city
- `inPriceRange($min, $max)` - Filter by price range

**Accessors:**
- `formatted_price` - Price with currency
- `full_address` - Complete formatted address

## API Response Structure

The integration expects the following JSON structure from Casafari API:

```json
{
  "data": [
    {
      "id": "property-id",
      "reference": "REF-001",
      "type": "apartment",
      "listing_type": "sale",
      "status": "active",
      "address": {
        "street": "123 Main St",
        "city": "Lisbon",
        "region": "Lisboa",
        "postal_code": "1000-001",
        "country": "PT"
      },
      "coordinates": {
        "latitude": 38.7223,
        "longitude": -9.1393
      },
      "price": {
        "amount": 250000,
        "currency": "EUR"
      },
      "details": {
        "bedrooms": 2,
        "bathrooms": 1,
        "area_total": 80,
        "area_built": 75,
        "area_unit": "m2",
        "year_built": 2020
      },
      "description": "Beautiful apartment in Lisbon",
      "photos": ["url1.jpg", "url2.jpg"],
      "main_photo": "url1.jpg",
      "features": ["balcony", "parking", "elevator"],
      "is_active": true
    }
  ],
  "pagination": {
    "current_page": 1,
    "next_page": 2
  }
}
```

**Note:** The actual API response structure may differ. Update the `createOrUpdateProperty()` method in `CasafariService` to match your specific API response format.

## Testing

Run the test suite:

```bash
php artisan test --filter=CasafariServiceTest
```

## Scheduled Syncing

To automatically sync properties on a schedule, add the command to `app/Console/Kernel.php`:

```php
protected function schedule(Schedule $schedule)
{
    // Sync properties daily at 2 AM
    $schedule->command('casafari:sync')->dailyAt('02:00');
}
```

Then ensure the Laravel scheduler is running:

```bash
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

## Troubleshooting

### Connection Issues

If you encounter connection issues:

1. Verify your API credentials are correct
2. Check your firewall allows outbound HTTPS connections
3. Test the connection: `php artisan casafari:sync --test`
4. Check the logs in `storage/logs/laravel.log`

### Data Not Syncing

If properties aren't syncing:

1. Check the command output for errors
2. Verify the API response structure matches expectations
3. Review logs for any exceptions
4. Ensure database migration was run successfully

## API Documentation

For full Casafari API documentation, visit: https://www.casafari.com/products/property-data-api/

## Support

For questions or issues with the integration, please contact your development team or refer to the Casafari API documentation.
