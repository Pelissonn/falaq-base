<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pergunta extends Model
{
    use HasFactory;

    protected $fillable = ['evento_id', 'user_id', 'texto', 'status', 'is_public'];

    protected function casts(): array
    {
        return ['is_public' => 'boolean'];
    }

    public function evento(): BelongsTo
    {
        return $this->belongsTo(Evento::class);
    }

    /** Autor da pergunta (Sprint 2). */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** Usuários que deram upvote na pergunta (Sprint 6). */
    public function votos(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'pergunta_user')->withTimestamps();
    }

    /** Denúncias feitas contra esta pergunta (Sprint 7). */
    public function moderacoes(): HasMany
    {
        return $this->hasMany(Moderacao::class);
    }
}
