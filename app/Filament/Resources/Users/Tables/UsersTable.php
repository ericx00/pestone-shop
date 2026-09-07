<?php

namespace App\Filament\Resources\Users\Tables;

use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable()
                    ->description(fn (User $r) => $r->email),
                TextColumn::make('company_name')->label('Company')->searchable()->placeholder('—'),
                TextColumn::make('phone')->toggleable(),
                TextColumn::make('type')->badge()->colors(['info' => 'b2b', 'gray' => 'b2c']),
                TextColumn::make('b2b_status')->label('B2B')->badge()->colors([
                    'gray' => 'none', 'warning' => 'pending', 'success' => 'approved', 'danger' => 'rejected',
                ]),
                TextColumn::make('orders_count')->counts('orders')->label('Orders'),
                TextColumn::make('ltv')->label('Lifetime value')
                    ->state(fn (User $r) => 'KES '.number_format($r->lifetimeValue())),
                IconColumn::make('is_admin')->label('Admin')->boolean()->toggleable(),
                TextColumn::make('created_at')->dateTime('d M Y')->label('Joined')->sortable()->toggleable(),
            ])
            ->filters([
                SelectFilter::make('type')->options(['b2c' => 'Personal', 'b2b' => 'Business']),
                SelectFilter::make('b2b_status')->options([
                    'pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected',
                ]),
                TernaryFilter::make('is_admin')->label('Admins'),
            ])
            ->recordActions([
                Action::make('approveB2B')
                    ->label('Approve B2B')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->visible(fn (User $r) => $r->b2b_status === 'pending')
                    ->requiresConfirmation()
                    ->action(fn (User $r) => $r->update([
                        'type' => 'b2b', 'b2b_status' => 'approved', 'b2b_approved_at' => now(),
                    ])),
                Action::make('rejectB2B')
                    ->label('Reject B2B')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (User $r) => $r->b2b_status === 'pending')
                    ->requiresConfirmation()
                    ->action(fn (User $r) => $r->update(['b2b_status' => 'rejected'])),
                EditAction::make(),
            ])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])])
            ->defaultSort('id', 'desc');
    }
}
