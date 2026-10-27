@extends('layouts.app')

@section('title', 'Moderação — '.$evento->titulo)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="fw-bold m-0">🛡️ Fila de Moderação — {{ $evento->titulo }}</h4>
    <a href="{{ route('eventos.show', $evento->id) }}" class="btn btn-outline-secondary btn-sm">&larr; Voltar ao evento</a>
</div>

@forelse($moderacoes as $moderacao)
    <div class="card mb-3 shadow-sm">
        <div class="card-body">
            <p class="fs-5 mb-1 text-white">{{ $moderacao->pergunta->texto }}</p>
            <p class="text-secondary small mb-2">Motivo da denúncia: {{ $moderacao->motivo }}</p>
            <div class="d-flex justify-content-between align-items-center">
                <span class="badge {{ $moderacao->status === 'analisando' ? 'bg-warning text-dark' : ($moderacao->status === 'aprovada' ? 'bg-success' : 'bg-danger') }}">
                    {{ ucfirst($moderacao->status) }}
                </span>
                <div class="d-flex gap-2">
                    @foreach(['aprovada' => ['Manter pergunta', 'btn-outline-success'], 'rejeitada' => ['Remover pergunta', 'btn-outline-danger']] as $valor => [$rotulo, $classe])
                        <form action="{{ route('moderacoes.status', $moderacao) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="status" value="{{ $valor }}">
                            <button type="submit" class="btn btn-sm {{ $classe }}">{{ $rotulo }}</button>
                        </form>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
@empty
    <div class="alert alert-dark text-center p-4">Nenhuma denúncia por aqui.</div>
@endforelse

<div class="d-flex justify-content-center mt-4">{{ $moderacoes->links() }}</div>
@endsection
