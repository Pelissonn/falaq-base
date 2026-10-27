<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreModeracaoRequest;
use App\Models\Evento;
use App\Models\Moderacao;
use App\Models\Pergunta;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ModeracaoController extends Controller
{
    /** Ticket #016: qualquer participante autenticado denuncia uma pergunta. */
    public function store(StoreModeracaoRequest $request, Pergunta $pergunta): RedirectResponse
    {
        $pergunta->moderacoes()->create([
            'motivo' => $request->validated('motivo'),
        ]);

        return back()->with('sucesso', 'Denúncia enviada. Obrigado por ajudar a moderar!');
    }

    /** Fila de moderação: só o dono do evento enxerga. */
    public function index(Evento $evento): View
    {
        $this->authorize('moderate', $evento);

        $moderacoes = Moderacao::with('pergunta')
            ->whereHas('pergunta', fn ($q) => $q->where('evento_id', $evento->id))
            ->latest()
            ->paginate(15);

        return view('eventos.moderacoes', compact('evento', 'moderacoes'));
    }

    /** Aprova (mantém) ou rejeita (retira do telão) a pergunta denunciada. */
    public function updateStatus(Request $request, Moderacao $moderacao): RedirectResponse
    {
        $this->authorize('moderate', $moderacao->pergunta->evento);

        $dados = $request->validate([
            'status' => ['required', 'in:'.Moderacao::APROVADA.','.Moderacao::REJEITADA],
        ], [
            'status.required' => 'Informe a decisão.',
            'status.in' => 'Decisão inválida.',
        ]);

        $moderacao->update(['status' => $dados['status']]);

        // Rejeitada: a pergunta sai do telão. Aprovada: continua pública.
        $moderacao->pergunta->update([
            'is_public' => $dados['status'] === Moderacao::APROVADA,
            'status' => $dados['status'] === Moderacao::APROVADA ? 'aprovado' : 'rejeitado',
        ]);

        return back()->with('sucesso', 'Moderação atualizada.');
    }
}
