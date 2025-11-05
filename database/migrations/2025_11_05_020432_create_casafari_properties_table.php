<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('casafari_properties', function (Blueprint $table) {
            $table->id();

            // External API identifiers
            $table->string('casafari_id')->unique()->index();
            $table->string('reference')->nullable();

            // Property type and status
            $table->string('property_type')->nullable()->index();
            $table->string('listing_type')->nullable(); // sale, rent
            $table->string('status')->default('active')->index();

            // Location
            $table->string('address')->nullable();
            $table->string('city')->nullable()->index();
            $table->string('region')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('country', 2)->nullable()->index();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();

            // Property details
            $table->decimal('price', 15, 2)->nullable()->index();
            $table->string('currency', 3)->default('EUR');
            $table->integer('bedrooms')->nullable();
            $table->integer('bathrooms')->nullable();
            $table->decimal('area_total', 10, 2)->nullable();
            $table->decimal('area_built', 10, 2)->nullable();
            $table->string('area_unit', 10)->default('m2');
            $table->integer('year_built')->nullable();
            $table->text('description')->nullable();

            // Media
            $table->json('photos')->nullable();
            $table->string('main_photo_url')->nullable();

            // Additional data
            $table->json('features')->nullable();
            $table->json('raw_data')->nullable();

            // Sync tracking
            $table->timestamp('last_synced_at')->nullable();
            $table->boolean('is_active')->default(true)->index();

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['property_type', 'city', 'is_active']);
            $table->index(['price', 'bedrooms', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('casafari_properties');
    }
};
