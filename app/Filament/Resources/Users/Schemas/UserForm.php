<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Profile')
                ->columns(2)
                ->schema([
                    TextInput::make('name')->required(),
                    TextInput::make('email')->email()->required()->unique(ignoreRecord: true),
                    TextInput::make('phone'),
                    TextInput::make('password')->password()->dehydrated(fn ($state) => filled($state))
                        ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                        ->required(fn (string $context) => $context === 'create')
                        ->helperText('Leave blank to keep current password.'),
                ]),
            Section::make('Business account')
                ->columns(2)
                ->schema([
                    Select::make('type')->options(['b2c' => 'Personal', 'b2b' => 'Business'])->default('b2c')->required(),
                    Select::make('b2b_status')->label('B2B status')->options([
                        'none' => 'Not applicable', 'pending' => 'Pending review',
                        'approved' => 'Approved', 'rejected' => 'Rejected',
                    ])->default('none')->required()->live()
                        ->afterStateUpdated(fn ($state, $set) => $state === 'approved' ? $set('b2b_approved_at', now()) : null),
                    TextInput::make('company_name'),
                    TextInput::make('kra_pin')->label('KRA PIN'),
                    DateTimePicker::make('b2b_approved_at'),
                    Toggle::make('tax_exempt'),
                    Toggle::make('is_admin')->label('Admin (can access this panel)'),
                ]),
        ]);
    }
}
