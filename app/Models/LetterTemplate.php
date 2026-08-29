<?php

namespace App\Models;

use Database\Factories\LetterTemplateFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'type', 'content', 'is_active'])]
class LetterTemplate extends Model
{
    /** @use HasFactory<LetterTemplateFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return HasMany<Letter, $this>
     */
    public function letters(): HasMany
    {
        return $this->hasMany(Letter::class);
    }
}
