<?php

namespace App\Filament\Resources\CasafariProperties;

use App\Filament\Resources\CasafariProperties\Pages\CreateCasafariProperty;
use App\Filament\Resources\CasafariProperties\Pages\EditCasafariProperty;
use App\Filament\Resources\CasafariProperties\Pages\ListCasafariProperties;
use App\Filament\Resources\CasafariProperties\Schemas\CasafariPropertyForm;
use App\Filament\Resources\CasafariProperties\Tables\CasafariPropertiesTable;
use App\Models\CasafariProperty;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CasafariPropertyResource extends Resource
{
    protected static ?string $model = CasafariProperty::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return CasafariPropertyForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CasafariPropertiesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCasafariProperties::route('/'),
            'create' => CreateCasafariProperty::route('/create'),
            'edit' => EditCasafariProperty::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
