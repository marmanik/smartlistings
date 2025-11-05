<?php

namespace App\Filament\Resources\CasafariProperties\Schemas;

use Filament\Forms\Components\Grid;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class CasafariPropertyForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Property Information')
                    ->schema([
                        TextInput::make('casafari_id')
                            ->label('Casafari ID')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        TextInput::make('reference')
                            ->maxLength(255),
                        Select::make('property_type')
                            ->options([
                                'apartment' => 'Apartment',
                                'house' => 'House',
                                'villa' => 'Villa',
                                'land' => 'Land',
                                'commercial' => 'Commercial',
                                'office' => 'Office',
                            ])
                            ->searchable(),
                        Select::make('listing_type')
                            ->options([
                                'sale' => 'Sale',
                                'rent' => 'Rent',
                            ]),
                        Select::make('status')
                            ->options([
                                'active' => 'Active',
                                'sold' => 'Sold',
                                'rented' => 'Rented',
                                'inactive' => 'Inactive',
                            ])
                            ->default('active')
                            ->required(),
                        Toggle::make('is_active')
                            ->label('Active')
                            ->default(true),
                    ])
                    ->columns(2),

                Section::make('Location')
                    ->schema([
                        TextInput::make('address')
                            ->maxLength(255),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('city')
                                    ->maxLength(255),
                                TextInput::make('region')
                                    ->maxLength(255),
                            ]),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('postal_code')
                                    ->maxLength(255),
                                TextInput::make('country')
                                    ->label('Country Code')
                                    ->maxLength(2)
                                    ->placeholder('e.g., PT, ES'),
                            ]),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('latitude')
                                    ->numeric()
                                    ->step(0.0000001),
                                TextInput::make('longitude')
                                    ->numeric()
                                    ->step(0.0000001),
                            ]),
                    ]),

                Section::make('Property Details')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('price')
                                    ->numeric()
                                    ->prefix('€')
                                    ->step(0.01),
                                TextInput::make('currency')
                                    ->maxLength(3)
                                    ->default('EUR'),
                            ]),
                        Grid::make(3)
                            ->schema([
                                TextInput::make('bedrooms')
                                    ->numeric()
                                    ->minValue(0),
                                TextInput::make('bathrooms')
                                    ->numeric()
                                    ->minValue(0),
                                TextInput::make('year_built')
                                    ->numeric()
                                    ->minValue(1800)
                                    ->maxValue(date('Y')),
                            ]),
                        Grid::make(3)
                            ->schema([
                                TextInput::make('area_total')
                                    ->label('Total Area')
                                    ->numeric()
                                    ->step(0.01),
                                TextInput::make('area_built')
                                    ->label('Built Area')
                                    ->numeric()
                                    ->step(0.01),
                                TextInput::make('area_unit')
                                    ->maxLength(10)
                                    ->default('m2'),
                            ]),
                        Textarea::make('description')
                            ->rows(4)
                            ->columnSpanFull(),
                    ]),

                Section::make('Media & Additional Data')
                    ->schema([
                        TextInput::make('main_photo_url')
                            ->label('Main Photo URL')
                            ->url()
                            ->maxLength(255)
                            ->columnSpanFull(),
                        KeyValue::make('photos')
                            ->label('Photo URLs')
                            ->columnSpanFull(),
                        KeyValue::make('features')
                            ->label('Property Features')
                            ->columnSpanFull(),
                    ])
                    ->collapsed(),
            ]);
    }
}
