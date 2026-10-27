<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Evento extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'titulo', 'descricao', 'data_evento', 'is_published'];

    protected function casts(): array
    {
        return [
            'data_evento' => 'datetime',
            'is_published' => 'boolean',
        ];
    }

    /** Organizador dono do evento. */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function perguntas(): HasMany
    {
        return $this->hasMany(Pergunta::class);
    }
}
