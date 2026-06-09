<?php

namespace App\Filament\Resources\WebsitePages\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class WebsitePageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('website_id')
                    ->label('Website')
                    ->relationship('website', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),

                TextInput::make('name')
                    ->label('Naam')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('Contact'),

                TextInput::make('url')
                    ->label('URL')
                    ->url()
                    ->required()
                    ->maxLength(255)
                    ->placeholder('https://quickstream.be/contact'),

                Toggle::make('is_active')
                    ->label('Actief')
                    ->default(true),
            ]);
    }
}
