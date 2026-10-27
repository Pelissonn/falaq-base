<?php

namespace Tests\Feature;

use App\Models\Evento;
use App\Models\Pergunta;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Sprint 5: autorização (AuthZ) da exclusão de perguntas via PerguntaPolicy.
 */
class AuthZTest extends TestCase
{
    use RefreshDatabase;

    private User $organizador;
    private User $autor;
    private User $intruso;
    private Evento $evento;
    private Pergunta $pergunta;

    protected function setUp(): void
    {
        parent::setUp();

        $this->organizador = User::factory()->create();
        $this->autor = User::factory()->create();
        $this->intruso = User::factory()->create();

        $this->evento = Evento::factory()->create(['user_id' => $this->organizador->id]);
        $this->pergunta = Pergunta::factory()->create([
            'evento_id' => $this->evento->id,
            'user_id' => $this->autor->id,
        ]);
    }

    public function test_autor_da_pergunta_pode_excluir(): void
    {
        $this->actingAs($this->autor)
            ->delete(route('perguntas.destroy', $this->pergunta))
            ->assertRedirect(route('eventos.show', $this->evento->id));

        $this->assertModelMissing($this->pergunta);
    }

    public function test_dono_do_evento_pode_excluir_pergunta_de_outro_usuario(): void
    {
        $this->actingAs($this->organizador)
            ->delete(route('perguntas.destroy', $this->pergunta))
            ->assertRedirect();

        $this->assertModelMissing($this->pergunta);
    }

    public function test_usuario_sem_relacao_recebe_403_e_a_pergunta_continua_existindo(): void
    {
        $this->actingAs($this->intruso)
            ->delete(route('perguntas.destroy', $this->pergunta))
            ->assertForbidden();

        $this->assertModelExists($this->pergunta);
    }

    public function test_visitante_anonimo_e_redirecionado_ao_login(): void
    {
        $this->delete(route('perguntas.destroy', $this->pergunta))
            ->assertRedirect(route('login'));

        $this->assertModelExists($this->pergunta);
    }

    public function test_pergunta_sem_autor_so_pode_ser_excluida_pelo_dono_do_evento(): void
    {
        $orfa = Pergunta::factory()->create(['evento_id' => $this->evento->id, 'user_id' => null]);

        $this->actingAs($this->intruso)->delete(route('perguntas.destroy', $orfa))->assertForbidden();
        $this->actingAs($this->organizador)->delete(route('perguntas.destroy', $orfa))->assertRedirect();

        $this->assertModelMissing($orfa);
    }

    public function test_botao_excluir_aparece_so_para_quem_pode_excluir(): void
    {
        $url = route('eventos.show', $this->evento->id);
        $acao = route('perguntas.destroy', $this->pergunta);

        $this->actingAs($this->autor)->get($url)->assertSee('Excluir Pergunta')->assertSee($acao, false);
        $this->actingAs($this->organizador)->get($url)->assertSee('Excluir Pergunta');
        $this->actingAs($this->intruso)->get($url)->assertDontSee('Excluir Pergunta')->assertDontSee($acao, false);
        $this->get($url)->assertDontSee('Excluir Pergunta');
    }

    public function test_policy_delete_aplica_a_regra_de_negocio(): void
    {
        $this->assertTrue($this->autor->can('delete', $this->pergunta));
        $this->assertTrue($this->organizador->can('delete', $this->pergunta));
        $this->assertFalse($this->intruso->can('delete', $this->pergunta));
    }
}
