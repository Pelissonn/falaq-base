<?php

namespace App\Policies;

use App\Models\Pergunta;
use App\Models\User;

class PerguntaPolicy
{
    /**
     * Só pode excluir a pergunta quem a escreveu ou o organizador
     * (dono) do evento ao qual ela pertence.
     */
    public function delete(User $user, Pergunta $pergunta): bool
    {
        $ehAutor = $pergunta->user_id !== null && $user->id === $pergunta->user_id;
        $ehDonoDoEvento = $pergunta->evento !== null
            && $pergunta->evento->user_id !== null
            && $user->id === $pergunta->evento->user_id;

        return $ehAutor || $ehDonoDoEvento;
    }
}
