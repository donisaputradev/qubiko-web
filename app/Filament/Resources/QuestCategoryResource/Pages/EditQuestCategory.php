<?php

namespace App\Filament\Resources\QuestCategoryResource\Pages;

use App\Filament\Resources\QuestCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditQuestCategory extends EditRecord
{
    protected static string $resource = QuestCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
