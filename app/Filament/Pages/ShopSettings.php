<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use BackedEnum;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class ShopSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected string $view = 'filament.pages.shop-settings';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static string|\UnitEnum|null $navigationGroup = 'Customers';

    protected static ?string $navigationLabel = 'Shop settings';

    protected static ?int $navigationSort = 99;

    public ?array $data = [];

    public function mount(): void
    {
        $d = Setting::defaults();
        $this->form->fill([
            'markup_percent' => Setting::get('markup_percent', $d['markup_percent']),
            'vat_rate' => Setting::get('vat_rate', $d['vat_rate']),
            'prices_include_vat' => Setting::get('prices_include_vat', $d['prices_include_vat']),
            'b2b_discount_percent' => Setting::get('b2b_discount_percent', $d['b2b_discount_percent']),
            'low_stock_threshold' => Setting::get('low_stock_threshold', $d['low_stock_threshold']),
            'free_delivery_threshold' => Setting::get('free_delivery_threshold', $d['free_delivery_threshold']),
            'fee_cbd' => (Setting::get('delivery_fees', $d['delivery_fees']))['nairobi_cbd'] ?? 300,
            'fee_metro' => (Setting::get('delivery_fees', $d['delivery_fees']))['nairobi_metro'] ?? 500,
            'fee_country' => (Setting::get('delivery_fees', $d['delivery_fees']))['countrywide'] ?? 1000,
            'company_name' => company('name'),
            'company_email' => company('email'),
            'company_phone' => company('phone'),
            'company_po_box' => company('po_box'),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                Section::make('Pricing & tax')
                    ->columns(2)
                    ->schema([
                        TextInput::make('markup_percent')->numeric()->required()->suffix('%')
                            ->helperText('Default markup applied to supplier cost on catalogue import.'),
                        TextInput::make('vat_rate')->numeric()->required()->suffix('%'),
                        Toggle::make('prices_include_vat')->label('Show B2C prices VAT-inclusive'),
                        TextInput::make('b2b_discount_percent')->numeric()->suffix('%')
                            ->helperText('Discount off net retail for approved B2B accounts without a set B2B price.'),
                    ]),
                Section::make('Delivery & stock')
                    ->columns(2)
                    ->schema([
                        TextInput::make('fee_cbd')->label('Nairobi CBD fee')->numeric()->prefix('KES'),
                        TextInput::make('fee_metro')->label('Greater Nairobi fee')->numeric()->prefix('KES'),
                        TextInput::make('fee_country')->label('Countrywide fee')->numeric()->prefix('KES'),
                        TextInput::make('free_delivery_threshold')->numeric()->prefix('KES')
                            ->helperText('Order value (incl. VAT) above which delivery is free. 0 to disable.'),
                        TextInput::make('low_stock_threshold')->numeric()
                            ->helperText('Stock at or below this shows as "low".'),
                    ]),
                Section::make('Company details')
                    ->columns(2)
                    ->schema([
                        TextInput::make('company_name')->required(),
                        TextInput::make('company_email')->email()->required(),
                        TextInput::make('company_phone')->required(),
                        TextInput::make('company_po_box'),
                    ]),
            ]);
    }

    public function save(): void
    {
        $d = $this->form->getState();

        Setting::put('markup_percent', (float) $d['markup_percent']);
        Setting::put('vat_rate', (float) $d['vat_rate']);
        Setting::put('prices_include_vat', (bool) $d['prices_include_vat']);
        Setting::put('b2b_discount_percent', (float) $d['b2b_discount_percent']);
        Setting::put('low_stock_threshold', (int) $d['low_stock_threshold']);
        Setting::put('free_delivery_threshold', (int) $d['free_delivery_threshold']);
        Setting::put('delivery_fees', [
            'nairobi_cbd' => (int) $d['fee_cbd'],
            'nairobi_metro' => (int) $d['fee_metro'],
            'countrywide' => (int) $d['fee_country'],
            'pickup' => 0,
        ]);
        Setting::put('company', [
            'name' => $d['company_name'],
            'tagline' => company('tagline', 'Proven Technology Solutions'),
            'email' => $d['company_email'],
            'phone' => $d['company_phone'],
            'po_box' => $d['company_po_box'],
            'website' => company('website', 'www.pestone.co.ke'),
        ]);

        Notification::make()->title('Settings saved')->success()->send();
    }
}
