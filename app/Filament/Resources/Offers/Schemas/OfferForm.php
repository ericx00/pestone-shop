<?php

namespace App\Filament\Resources\Offers\Schemas;

use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class OfferForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Offer')
                ->columns(2)
                ->schema([
                    TextInput::make('name')->required()->columnSpanFull(),
                    Select::make('type')->options(['percent' => 'Percentage off', 'fixed' => 'Fixed KES off'])
                        ->default('percent')->required()->live(),
                    TextInput::make('value')->numeric()->required()
                        ->suffix(fn (Get $get) => $get('type') === 'percent' ? '%' : 'KES'),
                    TextInput::make('badge_text')->placeholder('HOT DEAL / -15% (auto if blank)'),
                    ColorPicker::make('badge_color')->default('#E8801A'),
                    TextInput::make('priority')->numeric()->default(0)
                        ->helperText('Higher wins when several offers match a product.'),
                    Toggle::make('is_active')->default(true),
                ]),

            Section::make('Applies to')
                ->columns(2)
                ->schema([
                    Select::make('scope')->options([
                        'all' => 'All products',
                        'category' => 'Selected categories',
                        'brand' => 'Selected brands',
                        'products' => 'Selected products',
                    ])->default('products')->required()->live(),
                    DateTimePicker::make('starts_at'),
                    DateTimePicker::make('ends_at'),
                    Select::make('products')->relationship('products', 'name')->multiple()->searchable()->preload()
                        ->visible(fn (Get $get) => $get('scope') === 'products')->columnSpanFull(),
                    Select::make('categories')->relationship('categories', 'name')->multiple()->searchable()->preload()
                        ->visible(fn (Get $get) => $get('scope') === 'category')->columnSpanFull()
                        ->helperText('Child categories are included automatically.'),
                    Select::make('brands')->relationship('brands', 'name')->multiple()->searchable()->preload()
                        ->visible(fn (Get $get) => $get('scope') === 'brand')->columnSpanFull(),
                ]),
        ]);
    }
}
