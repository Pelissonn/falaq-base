@extends('layouts.app')

@section('title', $evento->titulo . ' — FalaQ')

@section('content')
<div class="row">
    <!-- Formularço de envio de Pergunta -->
    <div class="col-md-5 mb-4">
        <div class="card shadow-sm p-3">
            <h4 class="fw-bold mb-3">💬 Faça sua Pergunta</h4>
            <form action="{{ route('eventos.perguntas.store', $evento->id) }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label for="texto" class="form-label text-secondary">Texto da Pergunta</label>

                    <textarea name="texto" id="texto" rows="4" 
                              class="form-control bg-dark text-white border-secondary @error('texto') is-invalid @enderror"
                              placeholder="Digite sua dúvida ou comentário para o palestrante...">{{ old('texto') }}</textarea>

                    @error('texto')
                        <div class="invalid-feedback fw-bold">
                            {{ $message }}
                        </div>
                    @enderror
                </div>
                @auth
                    <button type="submit" class="btn btn-primary w-100 fw-bold">Enviar Pergunta</button>
                @else
                    <a href="{{ route('login') }}" class="btn btn-primary w-100 fw-bold">Entre para enviar uma pergunta</a>
                @endauth
            </form>
        </div>
    </div>

    <!-- Lista de Perguntas (TICKET #002) -->
    <div class="col-md-7">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="fw-bold m-0">📋 Perguntas do Evento</h4>
            <span class="text-secondary small">
                @can('moderate', $evento)
                    <a href="{{ route('eventos.moderacoes', $evento) }}" class="me-3">Fila de moderação</a>
                @endcan
                Total: {{ $perguntas->total() }}
            </span>
        </div>

        @forelse($perguntas as $pergunta)
            @php($jaVotou = in_array($pergunta->id, $votadas, true))
            <div class="card mb-3 shadow-sm border-start border-4 border-primary">
                <div class="card-body">
                    <p class="fs-5 mb-2 text-white">{{ $pergunta->texto }}</p>
                    <div class="d-flex justify-content-between align-items-center text-secondary small">
                        <span>Por <strong>{{ $pergunta->user->name ?? 'Anônimo' }}</strong></span>
                        <span>{{ $pergunta->created_at->format('d/m/Y H:i') }}</span>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mt-3">
                        {{-- Sprint 6 · Ticket #012: upvote (toggle) com destaque para quem já votou --}}
                        <form action="{{ route('perguntas.votar', $pergunta) }}" method="POST">
                            @csrf
                            <button type="submit"
                                    class="btn btn-sm {{ $jaVotou ? 'btn-primary' : 'btn-outline-primary' }}"
                                    aria-pressed="{{ $jaVotou ? 'true' : 'false' }}"
                                    title="{{ $jaVotou ? 'Clique para remover seu voto' : 'Votar nesta pergunta' }}">▲ {{ $pergunta->votos_count }}</button>
                        </form>

                        {{-- Sprint 7: denunciar pergunta imprópria --}}
                        @auth
                            <details class="small">
                                <summary class="text-secondary" style="cursor:pointer">🚩 Denunciar</summary>
                                <form action="{{ route('perguntas.moderar', $pergunta) }}" method="POST" class="mt-2">
                                    @csrf
                                    <textarea name="motivo" rows="2" minlength="10" maxlength="255" required
                                              class="form-control form-control-sm bg-dark text-white border-secondary"
                                              placeholder="Explique por que esta pergunta é imprópria"></textarea>
                                    <button type="submit" class="btn btn-sm btn-outline-secondary mt-1">Enviar denúncia</button>
                                </form>
                            </details>
                        @endauth

                        {{-- Sprint 5 · Ticket 4: só quem pode excluir enxerga o botão --}}
                        @can('delete', $pergunta)
                            <form action="{{ route('perguntas.destroy', $pergunta) }}" method="POST"
                                  onsubmit="return confirm('Excluir esta pergunta?')">
                                @csrf
                                @method('DELETE')
                                <x-danger-button>Excluir Pergunta</x-danger-button>
                            </form>
                        @endcan
                    </div>
                </div>
            </div>
        @empty
            <div class="alert alert-dark text-center p-4">
                Nenhuma pergunta enviada ainda. Seja o primeiro!
            </div>
        @endforelse

        <!-- TICKET #002: Renderização dos Botões de Paginação -->
        @if(method_exists($perguntas, 'links'))
            <div class="d-flex justify-content-center mt-4">
                {{ $perguntas->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
