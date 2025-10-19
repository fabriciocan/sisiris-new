<?php

namespace App\Filament\Resources\EventoCalendarios;

use App\Filament\Resources\EventoCalendarios\Pages\CalendarioView;
use App\Filament\Resources\EventoCalendarios\Pages\CreateEventoCalendario;
use App\Filament\Resources\EventoCalendarios\Pages\EditEventoCalendario;
use App\Filament\Resources\EventoCalendarios\Pages\ListEventoCalendarios;
use App\Filament\Resources\EventoCalendarios\Schemas\EventoCalendarioForm;
use App\Filament\Resources\EventoCalendarios\Tables\EventoCalendariosTable;
use App\Models\EventoCalendario;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class EventoCalendarioResource extends Resource
{
    protected static ?string $model = EventoCalendario::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static string|UnitEnum|null $navigationGroup = 'Gestão de Eventos';

    protected static ?string $navigationLabel = 'Calendário de Eventos';

    protected static ?string $modelLabel = 'Evento do Calendário';

    protected static ?string $pluralModelLabel = 'Eventos do Calendário';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return EventoCalendarioForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EventoCalendariosTable::configure($table);
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
            'index' => ListEventoCalendarios::route('/'),
            'create' => CreateEventoCalendario::route('/create'),
            'edit' => EditEventoCalendario::route('/{record}/edit'),
            'calendario' => CalendarioView::route('/calendario'),
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
