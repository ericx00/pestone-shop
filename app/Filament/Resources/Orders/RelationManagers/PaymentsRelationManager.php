<?php

namespace App\Filament\Resources\Orders\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PaymentsRelationManager extends RelationManager
{
    protected static string $relationship = 'payments';

    public function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('gateway')->badge(),
                TextColumn::make('method'),
                TextColumn::make('amount')->money('KES'),
                TextColumn::make('phone'),
                TextColumn::make('status')->badge()->colors([
                    'success' => 'success', 'warning' => 'pending', 'danger' => 'failed', 'gray' => 'initiated',
                ]),
                TextColumn::make('gateway_ref')->label('Reference')->copyable(),
                TextColumn::make('paid_at')->dateTime('d M H:i'),
            ])
            ->paginated(false);
    }
}
