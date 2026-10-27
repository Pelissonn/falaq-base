<?php

namespace Tests\Feature;

use App\Models\Evento;
use App\Models\Pergunta;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Sprint 6: upvotes (N:M com tabela pivot) e ranking no telão.
 */
class UpvotePerguntaTest extends TestCase
{
    use RefreshDatabase;

    public function test_tabela_pivot_existe_com_as_colunas_esperadas(): void
    {
        $this->assertTrue(Schema::hasTable('pergunta_user'));
        $this->assertTrue(Schema::hasColumns('pergunta_user', ['pergunta_id', 'user_id', 'created_at', 'updated_at']));
    }

    public function test_pivot_tem_chave_unica_composta(): void
    {
        $pergunta = Pergunta::factory()->create();
        $user = User::factory()->create();

        $pergunta->votos()->attach($user->id);

        $this->expectException(QueryException::class);
        $pergunta->votos()->attach($user->id);
    }

    public function test_relacionamentos_belongs_to_many(): void
    {
        $this->assertInstanceOf(BelongsToMany::class, (new Pergunta)->votos());
        $this->assertInstanceOf(BelongsToMany::class, (new User)->perguntasVotadas());

        $pergunta = Pergunta::factory()->create();
        $user = User::factory()->create();
        $pergunta->votos()->attach($user->id);

        $this->assertTrue($pergunta->votos->contains($user));
        $this->assertTrue($user->perguntasVotadas->contains($pergunta));
    }

    public function test_usuario_anonimo_nao_pode_votar(): void
    {
        $pergunta = Pergunta::factory()->create();

        $this->post(route('perguntas.votar', $pergunta))->assertRedirect(route('login'));
        $this->postJson(route('perguntas.votar', $pergunta))->assertUnauthorized();

        $this->assertDatabaseCount('pergunta_user', 0);
    }

    public function test_votar_insere_registro_na_pivot(): void
    {
        $pergunta = Pergunta::factory()->create();
        $user = User::factory()->create();

        $this->actingAs($user)->from('/')->post(route('perguntas.votar', $pergunta))
            ->assertRedirect('/')
            ->assertSessionHas('sucesso', 'Voto registrado!');

        $this->assertDatabaseHas('pergunta_user', ['pergunta_id' => $pergunta->id, 'user_id' => $user->id]);
    }

    public function test_votar_de_novo_remove_o_voto_toggle(): void
    {
        $pergunta = Pergunta::factory()->create();
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('perguntas.votar', $pergunta));
        $this->actingAs($user)->post(route('perguntas.votar', $pergunta))
            ->assertSessionHas('sucesso', 'Voto removido.');

        $this->assertDatabaseMissing('pergunta_user', ['pergunta_id' => $pergunta->id, 'user_id' => $user->id]);

        $this->actingAs($user)->post(route('perguntas.votar', $pergunta));
        $this->assertDatabaseCount('pergunta_user', 1);
    }

    public function test_cada_usuario_vota_uma_vez_e_votos_de_usuarios_diferentes_somam(): void
    {
        $pergunta = Pergunta::factory()->create();
        [$a, $b] = User::factory()->count(2)->create();

        $this->actingAs($a)->post(route('perguntas.votar', $pergunta));
        $this->actingAs($b)->post(route('perguntas.votar', $pergunta));

        $this->assertSame(2, $pergunta->votos()->count());
    }

    public function test_votar_em_pergunta_inexistente_retorna_404(): void
    {
        $this->actingAs(User::factory()->create())
            ->post('/perguntas/99999/votar')
            ->assertNotFound();
    }

    public function test_ranking_ordena_por_votos_decrescente(): void
    {
        $evento = Evento::factory()->create();
        $semVoto = Pergunta::factory()->create(['evento_id' => $evento->id, 'created_at' => now()]);
        $umVoto = Pergunta::factory()->create(['evento_id' => $evento->id, 'created_at' => now()->subHour()]);
        $tresVotos = Pergunta::factory()->create(['evento_id' => $evento->id, 'created_at' => now()->subDays(2)]);

        $umVoto->votos()->attach(User::factory()->create());
        $tresVotos->votos()->attach(User::factory()->count(3)->create());

        $perguntas = $this->get(route('eventos.show', $evento->id))->viewData('perguntas');

        $this->assertSame([$tresVotos->id, $umVoto->id, $semVoto->id], $perguntas->pluck('id')->all());
        $this->assertSame([3, 1, 0], $perguntas->pluck('votos_count')->all());
    }

    public function test_tela_mostra_contagem_e_destaca_o_botao_de_quem_ja_votou(): void
    {
        $evento = Evento::factory()->create();
        $pergunta = Pergunta::factory()->create(['evento_id' => $evento->id]);
        $votante = User::factory()->create();
        $outro = User::factory()->create();
        $pergunta->votos()->attach($votante->id);

        $url = route('eventos.show', $evento->id);
        $acao = route('perguntas.votar', $pergunta);

        $this->actingAs($votante)->get($url)
            ->assertSee($acao, false)
            ->assertSee('▲ 1')
            ->assertSee('btn btn-sm btn-primary', false)
            ->assertSee('aria-pressed="true"', false);

        $this->actingAs($outro)->get($url)
            ->assertSee('btn btn-sm btn-outline-primary', false)
            ->assertSee('aria-pressed="false"', false);
    }

    public function test_excluir_pergunta_remove_os_votos_em_cascata(): void
    {
        $pergunta = Pergunta::factory()->create();
        $pergunta->votos()->attach(User::factory()->count(2)->create());

        $this->actingAs($pergunta->user)->delete(route('perguntas.destroy', $pergunta));

        $this->assertDatabaseCount('pergunta_user', 0);
    }
}
