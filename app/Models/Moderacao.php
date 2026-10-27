<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Moderacao extends Model
{
    protected $table = 'moderacoes';

    public const ANALISANDO = 'analisando';
    public const APROVADA = 'aprovada';   // a pergunta é mantida no telão
    public const REJEITADA = 'rejeitada'; // a pergunta é retirada do telão

    protected $fillable = ['pergunta_id', 'motivo', 'status'];

    public function pergunta(): BelongsTo
    {
        return $this->belongsTo(Pergunta::class);
    }
}
