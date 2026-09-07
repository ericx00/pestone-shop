<?php

namespace App\Filament\Resources\Products\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Product')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')->required()->columnSpanFull(),
                        TextInput::make('sku')->label('SKU'),
                        TextInput::make('slug')->required()
                            ->helperText('Used in the product URL')->unique(ignoreRecord: true),
                        Select::make('brand_id')->label('Brand')
                            ->relationship('brand', 'name')->searchable()->preload()->createOptionForm([
                                TextInput::make('name')->required(),
                            ]),
                        Select::make('category_id')->label('Category')
                            ->relationship('category', 'name')->searchable()->preload(),
                        Textarea::make('short_description')->rows(2)->maxLength(500)->columnSpanFull(),
                        Textarea::make('description')->rows(6)->columnSpanFull(),
                    ]),

                Section::make('Pricing')
                    ->columns(3)
                    ->description('Cost is what Pestone pays the supplier. Retail is VAT-inclusive; B2B price is ex-VAT.')
                    ->schema([
                        TextInput::make('cost_price')->numeric()->prefix('KES')->required()->default(0),
                        TextInput::make('price')->label('Retail price (incl. VAT)')->numeric()->prefix('KES')->required()->default(0)
                            ->helperText('Editing this locks the price against catalogue re-imports.'),
                        TextInput::make('b2b_price')->label('B2B price (ex. VAT)')->numeric()->prefix('KES')
                            ->helperText('Leave blank to auto-discount off retail.'),
                        Toggle::make('price_locked')->label('Price locked')
                            ->helperText('When on, catalogue import will not overwrite the price.'),
                    ]),

                Section::make('Inventory')
                    ->columns(3)
                    ->schema([
                        TextInput::make('stock_qty')->numeric()->required()->default(0),
                        Select::make('stock_status')->options([
                            'in_stock' => 'In stock',
                            'low' => 'Low stock',
                            'out' => 'Out of stock',
                            'on_request' => 'On request',
                        ])->required()->default('on_request'),
                        TextInput::make('availability_label')->placeholder('e.g. Ex-Stock'),
                        TextInput::make('weight_grams')->label('Weight (g)')->numeric(),
                    ]),

                Section::make('Media & visibility')
                    ->columns(2)
                    ->schema([
                        FileUpload::make('images')->image()->multiple()->reorderable()
                            ->directory('products')->imageEditor()->columnSpanFull(),
                        KeyValue::make('specs')->keyLabel('Spec')->valueLabel('Value')->columnSpanFull(),
                        Toggle::make('is_active')->label('Active (visible in shop)')->default(true),
                        Toggle::make('is_featured')->label('Featured on home page'),
                        TextInput::make('meta_title'),
                        TextInput::make('meta_description'),
                    ]),
            ]);
    }
}
