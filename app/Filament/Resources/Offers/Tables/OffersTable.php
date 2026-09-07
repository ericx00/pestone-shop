<?php

namespace App\Filament\Resources\Offers\Tables;

use App\Models\Offer;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class OffersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable(),
                TextColumn::make('badge_text')->badge()->color('warning')
                    ->placeholder('auto'),
                TextColumn::make('value')->formatStateUsing(fn ($state, Offer $r) => $r->type === 'percent' ? $state.'%' : 'KES '.number_format($state)),
                TextColumn::make('scope')->badge(),
                TextColumn::make('starts_at')->dateTime('d M Y')->placeholder('—'),
                TextColumn::make('ends_at')->dateTime('d M Y')->placeholder('—'),
                TextColumn::make('priority')->sortable(),
                IconColumn::make('is_active')->boolean(),
            ])
            ->recordActions([EditAction::make()])
            ->toolbarActions([
                BulkActionGroup::make([DeleteBulkAction::make()]),
            ])
            ->defaultSort('priority', 'desc');
    }
}
