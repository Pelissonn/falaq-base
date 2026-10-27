<?php

namespace App\Policies;

use App\Models\Evento;
use App\Models\User;

class EventoPolicy
{
    /** Só o dono (organizador) do evento modera as denúncias das perguntas dele. */
    public function moderate(User $user, Evento $evento): bool
    {
        return $evento->user_id !== null && $user->id === $evento->user_id;
    }
}
