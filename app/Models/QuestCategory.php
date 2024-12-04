<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QuestCategory extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function quests(): HasMany
    {
        return $this->hasMany(QuestCategory::class);
    }
}
