<?php

namespace App\Filament\Resources\Categories\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class CategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')->required()->live(onBlur: true)
                ->afterStateUpdated(fn ($state, $set, $context) => $context === 'create' ? $set('slug', \Illuminate\Support\Str::slug($state)) : null),
            TextInput::make('slug')->required()->unique(ignoreRecord: true),
            Select::make('parent_id')->label('Parent category')
                ->relationship('parent', 'name')->searchable()->preload()->placeholder('Top level'),
            TextInput::make('position')->numeric()->default(0),
            Textarea::make('description')->rows(3)->columnSpanFull(),
            FileUpload::make('image_path')->image()->directory('categories'),
            Toggle::make('is_active')->default(true),
        ]);
    }
}
