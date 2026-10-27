<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEventoRequest;
use App\Http\Requests\StorePerguntaRequest;
use App\Models\Evento;
use App\Models\Pergunta;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class EventoController extends Controller
{
    public function index(): View
    {
        // Rascunhos ficam fora da página principal; a contagem só considera perguntas públicas.
        $eventos = Evento::where('is_published', true)
            ->withCount(['perguntas' => fn ($q) => $q->where('is_public', true)])
            ->get();

        return view('eventos.index', compact('eventos'));
    }

    public function create(): View
    {
        return view('eventos.create');
    }

    public function store(StoreEventoRequest $request): RedirectResponse
    {
        $evento = Evento::create([
            'user_id' => Auth::id(),
            'titulo' => $request->validated('titulo'),
            'descricao' => $request->validated('descricao'),
            'is_published' => true,
        ]);

        return redirect()->route('eventos.show', $evento->id)
            ->with('sucesso', 'Evento criado com sucesso!');
    }

    public function show($id): View
    {
        $evento = Evento::findOrFail($id);

        // Rascunho só pode ser visto pelo organizador dono do evento.
        abort_unless($evento->is_published || (Auth::check() && Auth::id() === $evento->user_id), 404);

        // Tickets #002 (where + paginate), #004 (with), #006 (is_public) e #012 (ranking por votos).
        $perguntas = Pergunta::where('evento_id', $evento->id)
            ->where('is_public', true)
            ->with('user')
            ->withCount('votos')
            ->orderByDesc('votos_count')
            ->latest()
            ->paginate(10);

        // Ids das perguntas da página em que o usuário logado já votou (uma única consulta).
        $votadas = Auth::check()
            ? Auth::user()->perguntasVotadas()
                ->whereIn('perguntas.id', $perguntas->pluck('id'))
                ->pluck('perguntas.id')
                ->all()
            : [];

        return view('eventos.show', compact('evento', 'perguntas', 'votadas'));
    }

    public function storePergunta(StorePerguntaRequest $request, $id): RedirectResponse
    {
        $evento = Evento::findOrFail($id);

        Pergunta::create([
            'evento_id' => $evento->id,
            'user_id' => Auth::id(),
            'texto' => $request->validated('texto'),
            'status' => 'pendente',
            'is_public' => false,
        ]);

        return redirect()->route('eventos.show', $evento->id)
            ->with('sucesso', 'Sua pergunta foi enviada e aguarda aprovação do organizador.');
    }
}
