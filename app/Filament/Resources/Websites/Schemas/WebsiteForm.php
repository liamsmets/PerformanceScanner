<?php

namespace App\Filament\Resources\Websites\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DateTimePicker;

class WebsiteForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('url')
                    ->label('Website URL')
                    ->url()
                    ->required()
                    ->placeholder('https://jouwklant.be'),

                TextInput::make('performance_score')
                    ->label('Score (%)')
                    ->numeric()
                    ->disabled() // Wordt ingevuld door de scanner
                    ->placeholder('Nog niet gescand'),

                DateTimePicker::make('last_scanned_at')
                    ->label('Laatste scan')
                    ->disabled(),
            ]);
    }
}
