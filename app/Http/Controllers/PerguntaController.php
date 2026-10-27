<?php

namespace App\Http\Controllers;

use App\Models\Pergunta;
use Illuminate\Http\RedirectResponse;

class PerguntaController extends Controller
{
    /**
     * Sprint 5: exclui a pergunta (só o autor ou o dono do evento — PerguntaPolicy).
     */
    public function destroy(Pergunta $pergunta): RedirectResponse
    {
        $this->authorize('delete', $pergunta);

        $eventoId = $pergunta->evento_id;
        $pergunta->delete();

        return redirect()->route('eventos.show', $eventoId)
            ->with('sucesso', 'Pergunta excluída.');
    }

    /**
     * Sprint 6: alterna (toggle) o upvote do usuário logado na pergunta.
     */
    public function votar(Pergunta $pergunta): RedirectResponse
    {
        $resultado = $pergunta->votos()->toggle(auth()->id());

        $mensagem = count($resultado['attached']) > 0
            ? 'Voto registrado!'
            : 'Voto removido.';

        return back()->with('sucesso', $mensagem);
    }
}
