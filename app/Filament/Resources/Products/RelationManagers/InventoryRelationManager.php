<?php

namespace App\Filament\Resources\Products\RelationManagers;

use App\Models\InventoryMovement;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class InventoryRelationManager extends RelationManager
{
    protected static string $relationship = 'inventoryMovements';

    protected static ?string $title = 'Inventory';

    public function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')->dateTime('d M Y H:i')->label('When'),
                TextColumn::make('change')->badge()
                    ->color(fn ($state) => $state >= 0 ? 'success' : 'danger')
                    ->formatStateUsing(fn ($state) => ($state >= 0 ? '+' : '').$state),
                TextColumn::make('reason')->badge(),
                TextColumn::make('reference')->placeholder('—'),
                TextColumn::make('user.name')->label('By')->placeholder('system'),
                TextColumn::make('note')->limit(40)->placeholder('—'),
            ])
            ->headerActions([
                Action::make('adjust')
                    ->label('Adjust stock')
                    ->icon('heroicon-o-adjustments-horizontal')
                    ->schema([
                        Select::make('direction')->options(['in' => 'Add (restock)', 'out' => 'Remove'])
                            ->default('in')->required(),
                        TextInput::make('qty')->numeric()->minValue(1)->required(),
                        TextInput::make('note')->maxLength(120),
                    ])
                    ->action(function (array $data) {
                        $change = $data['direction'] === 'in' ? (int) $data['qty'] : -(int) $data['qty'];
                        InventoryMovement::record(
                            $this->getOwnerRecord(),
                            $change,
                            'restock',
                            null,
                            $data['note'] ?? null,
                            Auth::id(),
                        );
                    }),
            ])
            ->defaultSort('id', 'desc');
    }
}
