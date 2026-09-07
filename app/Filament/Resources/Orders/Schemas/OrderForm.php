<?php

namespace App\Filament\Resources\Orders\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Fulfilment')
                ->columns(2)
                ->schema([
                    Select::make('status')->options(collect(\App\Models\Order::STATUSES)
                        ->mapWithKeys(fn ($s) => [$s => ucfirst(str_replace('_', ' ', $s))]))->required(),
                    Select::make('payment_status')->options([
                        'unpaid' => 'Unpaid', 'pending' => 'Pending', 'paid' => 'Paid',
                        'failed' => 'Failed', 'refunded' => 'Refunded',
                    ])->required(),
                    TextInput::make('payment_method')->disabled(),
                    TextInput::make('delivery_zone')->disabled(),
                    Textarea::make('admin_notes')->rows(3)->columnSpanFull()
                        ->helperText('Internal only — not shown to the customer.'),
                ]),

            Section::make('Customer')
                ->columns(2)
                ->schema([
                    TextInput::make('customer_name')->disabled(),
                    TextInput::make('customer_phone')->disabled(),
                    TextInput::make('customer_email')->disabled(),
                    TextInput::make('customer_company')->disabled(),
                    Textarea::make('notes')->label('Customer notes')->disabled()->columnSpanFull(),
                ]),

            Section::make('Totals')
                ->columns(3)
                ->schema([
                    TextInput::make('subtotal')->prefix('KES')->disabled(),
                    TextInput::make('vat_total')->prefix('KES')->disabled(),
                    TextInput::make('shipping_total')->prefix('KES')->disabled(),
                    TextInput::make('discount_total')->prefix('KES')->disabled(),
                    TextInput::make('grand_total')->prefix('KES')->disabled(),
                ]),
        ]);
    }
}
