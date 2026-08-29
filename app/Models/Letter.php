<?php

namespace App\Models;

use Database\Factories\LetterFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'letter_number',
    'letter_template_id',
    'resident_id',
    'issued_by',
    'purpose',
    'issued_date',
    'file_path',
])]
class Letter extends Model
{
    /** @use HasFactory<LetterFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'issued_date' => 'date',
        ];
    }

    /**
     * @return BelongsTo<LetterTemplate, $this>
     */
    public function template(): BelongsTo
    {
        return $this->belongsTo(LetterTemplate::class, 'letter_template_id');
    }

    /**
     * @return BelongsTo<Resident, $this>
     */
    public function resident(): BelongsTo
    {
        return $this->belongsTo(Resident::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function issuer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'issued_by');
    }
}
