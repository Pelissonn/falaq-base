{{-- Sprint 5: botão vermelho para ações destrutivas (o layout do telão usa Bootstrap). --}}
<button {{ $attributes->merge(['type' => 'submit', 'class' => 'btn btn-danger btn-sm fw-bold']) }}>
    {{ $slot }}
</button>
