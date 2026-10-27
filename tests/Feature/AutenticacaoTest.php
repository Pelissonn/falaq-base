<?php

namespace Tests\Feature;

use App\Models\Evento;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Sprint 3 · Ticket #005 (middleware auth) e cadastro/login básicos.
 */
class AutenticacaoTest extends TestCase
{
    use RefreshDatabase;

    public function test_visitante_nao_logado_nao_envia_pergunta(): void
    {
        $evento = Evento::factory()->create();

        $this->post(route('eventos.perguntas.store', $evento->id), ['texto' => 'Pergunta de um visitante anônimo'])
            ->assertRedirect(route('login'));

        $this->assertDatabaseCount('perguntas', 0);
    }

    public function test_requisicao_json_anonima_retorna_401(): void
    {
        $evento = Evento::factory()->create();

        $this->postJson(route('eventos.perguntas.store', $evento->id), ['texto' => 'Pergunta de um visitante anônimo'])
            ->assertUnauthorized();
    }

    public function test_rota_de_envio_de_pergunta_usa_o_middleware_auth(): void
    {
        $rota = app('router')->getRoutes()->getByName('eventos.perguntas.store');

        $this->assertContains('auth', $rota->gatherMiddleware());
    }

    public function test_cadastro_cria_usuario_com_senha_hasheada_e_loga(): void
    {
        $this->post('/register', [
            'name' => 'Novo Usuário',
            'email' => 'novo@falaq.test',
            'password' => 'senha-segura-123',
            'password_confirmation' => 'senha-segura-123',
        ])->assertRedirect(route('eventos.index'));

        $user = User::where('email', 'novo@falaq.test')->firstOrFail();
        $this->assertNotSame('senha-segura-123', $user->password);
        $this->assertAuthenticatedAs($user);
    }

    public function test_cadastro_invalido_mostra_erros(): void
    {
        $this->post('/register', [])
            ->assertSessionHasErrors(['name', 'email', 'password']);
    }

    public function test_login_com_credenciais_corretas_e_incorretas(): void
    {
        $user = User::factory()->create(['password' => 'senha-segura-123']);

        $this->post('/login', ['email' => $user->email, 'password' => 'errada'])
            ->assertSessionHasErrors('email');
        $this->assertGuest();

        $this->post('/login', ['email' => $user->email, 'password' => 'senha-segura-123'])
            ->assertRedirect(route('eventos.index'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_logout_encerra_a_sessao(): void
    {
        $this->actingAs(User::factory()->create())
            ->post('/logout')
            ->assertRedirect(route('eventos.index'));

        $this->assertGuest();
    }
}
