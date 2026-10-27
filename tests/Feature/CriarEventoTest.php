<?php

namespace Tests\Feature;

use App\Models\Evento;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Sprint 4 · Tickets #007 e #008 (formulário de criação de eventos).
 */
class CriarEventoTest extends TestCase
{
    use RefreshDatabase;

    public function test_visitante_e_redirecionado_ao_login(): void
    {
        $this->get(route('eventos.create'))->assertRedirect(route('login'));
        $this->post(route('eventos.store'), ['titulo' => 'x', 'descricao' => 'y'])
            ->assertRedirect(route('login'));
    }

    public function test_formulario_tem_campos_estilizados_e_botao_com_hover(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('eventos.create'))
            ->assertOk()
            ->assertSee('name="titulo"', false)
            ->assertSee('name="descricao"', false)
            ->assertSee('max-w-2xl mx-auto bg-white p-6 rounded-lg shadow-md', false)
            ->assertSee('hover:bg-blue-800', false);
    }

    public function test_envio_em_branco_mostra_erros_vermelhos_e_bordas_vermelhas(): void
    {
        $this->actingAs(User::factory()->create())
            ->from(route('eventos.create'))
            ->followingRedirects()
            ->post(route('eventos.store'), [])
            ->assertOk()
            ->assertSee('O título do evento é obrigatório.')
            ->assertSee('A descrição do evento é obrigatória.')
            ->assertSee('text-red-500', false)
            ->assertSee('border-red-500', false);
    }

    public function test_texto_digitado_e_preservado_apos_falha_de_validacao(): void
    {
        $this->actingAs(User::factory()->create())
            ->from(route('eventos.create'))
            ->followingRedirects()
            ->post(route('eventos.store'), ['titulo' => 'Meu título digitado', 'descricao' => ''])
            ->assertSee('value="Meu título digitado"', false)
            ->assertSee('A descrição do evento é obrigatória.')
            // Só o campo com erro recebe a borda vermelha.
            ->assertSee('border-gray-300', false);
    }

    public function test_cria_evento_para_o_usuario_logado(): void
    {
        $user = User::factory()->create();

        $resposta = $this->actingAs($user)->post(route('eventos.store'), [
            'titulo' => 'Meu Evento',
            'descricao' => 'Descrição do meu evento',
        ]);

        $evento = Evento::firstOrFail();
        $resposta->assertRedirect(route('eventos.show', $evento->id));
        $this->assertSame($user->id, $evento->user_id);
        $this->assertTrue($evento->is_published);
    }
}
