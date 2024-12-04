<?php

namespace App\Filament\Resources\QuestCategoryResource\Pages;

use App\Filament\Resources\QuestCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListQuestCategories extends ListRecords
{
    protected static string $resource = QuestCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
