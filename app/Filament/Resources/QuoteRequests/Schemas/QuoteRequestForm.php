<?php

namespace App\Filament\Resources\QuoteRequests\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class QuoteRequestForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Request')
                ->columns(2)
                ->schema([
                    TextInput::make('name')->disabled(),
                    TextInput::make('company')->disabled(),
                    TextInput::make('email')->disabled(),
                    TextInput::make('phone')->disabled(),
                    TextInput::make('subject')->disabled()->columnSpanFull(),
                    Textarea::make('message')->disabled()->rows(5)->columnSpanFull(),
                ]),
            Section::make('Handling')
                ->schema([
                    Select::make('status')->options([
                        'new' => 'New', 'quoted' => 'Quoted', 'won' => 'Won', 'lost' => 'Lost',
                    ])->required()->default('new'),
                    Textarea::make('admin_notes')->rows(4)->columnSpanFull(),
                ]),
        ]);
    }
}
