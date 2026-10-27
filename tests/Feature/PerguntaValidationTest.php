<?php

namespace Tests\Feature;

use App\Models\Evento;
use App\Models\Pergunta;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Sprint 1 · Ticket #001 (validação de entrada).
 */
class PerguntaValidationTest extends TestCase
{
    use RefreshDatabase;

    private function enviar(array $dados, ?Evento $evento = null, ?User $user = null)
    {
        $evento ??= Evento::factory()->create();

        return $this->actingAs($user ?? User::factory()->create())
            ->postJson(route('eventos.perguntas.store', $evento->id), $dados);
    }

    public function test_envio_vazio_retorna_422(): void
    {
        $this->enviar([])
            ->assertStatus(422)
            ->assertJsonValidationErrors('texto');

        $this->assertDatabaseCount('perguntas', 0);
    }

    public function test_pergunta_de_uma_letra_retorna_422(): void
    {
        $this->enviar(['texto' => 'a'])
            ->assertStatus(422)
            ->assertJsonValidationErrors('texto');
    }

    public function test_pergunta_com_menos_de_10_caracteres_retorna_422(): void
    {
        $this->enviar(['texto' => 'curta'])->assertStatus(422);
        $this->enviar(['texto' => str_repeat('a', 9)])->assertStatus(422);
    }

    public function test_pergunta_com_mais_de_255_caracteres_retorna_422(): void
    {
        $this->enviar(['texto' => str_repeat('a', 256)])
            ->assertStatus(422)
            ->assertJsonValidationErrors('texto');
    }

    public function test_texto_precisa_ser_string(): void
    {
        $this->enviar(['texto' => ['uma', 'lista']])
            ->assertStatus(422)
            ->assertJsonValidationErrors('texto');
    }

    public function test_limites_exatos_10_e_255_caracteres_sao_aceitos(): void
    {
        $evento = Evento::factory()->create();

        $this->enviar(['texto' => str_repeat('a', 10)], $evento)->assertRedirect();
        $this->enviar(['texto' => str_repeat('b', 255)], $evento)->assertRedirect();

        $this->assertDatabaseCount('perguntas', 2);
    }

    public function test_evento_inexistente_retorna_422(): void
    {
        $this->actingAs(User::factory()->create())
            ->postJson('/eventos/99999/perguntas', ['texto' => 'Pergunta válida e longa'])
            ->assertStatus(422)
            ->assertJsonValidationErrors('evento_id');
    }

    public function test_pergunta_valida_e_salva_como_pendente_com_o_autor(): void
    {
        $user = User::factory()->create();
        $evento = Evento::factory()->create();

        $this->enviar(['texto' => 'Como funciona o Service Container?'], $evento, $user)
            ->assertRedirect(route('eventos.show', $evento->id));

        $pergunta = Pergunta::first();
        $this->assertSame($user->id, $pergunta->user_id);
        $this->assertSame($evento->id, $pergunta->evento_id);
        $this->assertFalse($pergunta->is_public);
    }
}
