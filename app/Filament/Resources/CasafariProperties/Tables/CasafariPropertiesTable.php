<?php

namespace App\Filament\Resources\CasafariProperties\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class CasafariPropertiesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('main_photo_url')
                    ->label('Photo')
                    ->circular()
                    ->defaultImageUrl(url('/images/placeholder-property.png')),
                TextColumn::make('casafari_id')
                    ->label('ID')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                TextColumn::make('reference')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('property_type')
                    ->badge()
                    ->searchable()
                    ->sortable(),
                TextColumn::make('listing_type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'sale' => 'success',
                        'rent' => 'warning',
                        default => 'gray',
                    })
                    ->searchable()
                    ->sortable(),
                TextColumn::make('city')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('country')
                    ->label('Country')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('price')
                    ->money(fn ($record) => $record->currency ?? 'EUR')
                    ->sortable(),
                TextColumn::make('bedrooms')
                    ->numeric()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('bathrooms')
                    ->numeric()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('area_total')
                    ->label('Area')
                    ->formatStateUsing(fn ($record) => $record->area_total ? $record->area_total . ' ' . $record->area_unit : '-')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'sold' => 'danger',
                        'rented' => 'warning',
                        'inactive' => 'gray',
                        default => 'gray',
                    })
                    ->searchable()
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->sortable(),
                TextColumn::make('last_synced_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable()
                    ->toggledHiddenByDefault(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable()
                    ->toggledHiddenByDefault(),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable()
                    ->toggledHiddenByDefault(),
            ])
            ->filters([
                SelectFilter::make('property_type')
                    ->options([
                        'apartment' => 'Apartment',
                        'house' => 'House',
                        'villa' => 'Villa',
                        'land' => 'Land',
                        'commercial' => 'Commercial',
                        'office' => 'Office',
                    ]),
                SelectFilter::make('listing_type')
                    ->options([
                        'sale' => 'Sale',
                        'rent' => 'Rent',
                    ]),
                SelectFilter::make('status')
                    ->options([
                        'active' => 'Active',
                        'sold' => 'Sold',
                        'rented' => 'Rented',
                        'inactive' => 'Inactive',
                    ]),
                SelectFilter::make('country')
                    ->searchable()
                    ->multiple(),
                TrashedFilter::make(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
