<?php

namespace App\Filament\Resources\QuoteRequests\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class QuoteRequestsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')->dateTime('d M H:i')->label('Received')->sortable(),
                TextColumn::make('name')->searchable()
                    ->description(fn ($record) => $record->company),
                TextColumn::make('phone')->searchable(),
                TextColumn::make('subject')->limit(40)->searchable(),
                TextColumn::make('status')->badge()->colors([
                    'gray' => 'new', 'info' => 'quoted', 'success' => 'won', 'danger' => 'lost',
                ]),
            ])
            ->filters([
                SelectFilter::make('status')->options([
                    'new' => 'New', 'quoted' => 'Quoted', 'won' => 'Won', 'lost' => 'Lost',
                ]),
            ])
            ->recordActions([EditAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])])
            ->defaultSort('id', 'desc');
    }
}
