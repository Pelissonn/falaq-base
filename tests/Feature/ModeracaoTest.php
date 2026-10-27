<?php

namespace Tests\Feature;

use App\Models\Evento;
use App\Models\Moderacao;
use App\Models\Pergunta;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Sprint 7: denúncia e moderação de perguntas.
 */
class ModeracaoTest extends TestCase
{
    use RefreshDatabase;

    private User $organizador;
    private Evento $evento;
    private Pergunta $pergunta;

    protected function setUp(): void
    {
        parent::setUp();

        $this->organizador = User::factory()->create();
        $this->evento = Evento::factory()->create(['user_id' => $this->organizador->id]);
        $this->pergunta = Pergunta::factory()->create(['evento_id' => $this->evento->id]);
    }

    public function test_tabela_moderacoes_tem_colunas_e_status_padrao_analisando(): void
    {
        $this->assertTrue(Schema::hasColumns('moderacoes', ['pergunta_id', 'motivo', 'status', 'created_at', 'updated_at']));

        $m = Moderacao::create(['pergunta_id' => $this->pergunta->id, 'motivo' => 'Conteúdo ofensivo aqui']);
        $this->assertSame('analisando', $m->fresh()->status);
    }

    public function test_relacionamentos_um_para_muitos(): void
    {
        $this->assertInstanceOf(HasMany::class, (new Pergunta)->moderacoes());
        $this->assertInstanceOf(BelongsTo::class, (new Moderacao)->pergunta());

        $m = Moderacao::create(['pergunta_id' => $this->pergunta->id, 'motivo' => 'Fora do escopo do evento']);
        $this->assertTrue($this->pergunta->moderacoes->contains($m));
        $this->assertTrue($m->pergunta->is($this->pergunta));
    }

    public function test_excluir_pergunta_remove_denuncias_em_cascata(): void
    {
        Moderacao::create(['pergunta_id' => $this->pergunta->id, 'motivo' => 'Conteúdo ofensivo aqui']);
        $this->pergunta->delete();

        $this->assertDatabaseCount('moderacoes', 0);
    }

    public function test_anonimo_nao_denuncia(): void
    {
        $this->post(route('perguntas.moderar', $this->pergunta), ['motivo' => 'Conteúdo ofensivo aqui'])
            ->assertRedirect(route('login'));

        $this->assertDatabaseCount('moderacoes', 0);
    }

    public function test_participante_denuncia_e_fica_analisando(): void
    {
        $this->actingAs(User::factory()->create())
            ->post(route('perguntas.moderar', $this->pergunta), ['motivo' => 'Conteúdo ofensivo aqui'])
            ->assertSessionHas('sucesso');

        $this->assertDatabaseHas('moderacoes', [
            'pergunta_id' => $this->pergunta->id,
            'motivo' => 'Conteúdo ofensivo aqui',
            'status' => 'analisando',
        ]);
    }

    public function test_motivo_e_validado(): void
    {
        $user = User::factory()->create();
        $url = route('perguntas.moderar', $this->pergunta);

        $this->actingAs($user)->postJson($url, [])->assertStatus(422)->assertJsonValidationErrors('motivo');
        $this->actingAs($user)->postJson($url, ['motivo' => 'curto'])->assertStatus(422);
        $this->actingAs($user)->postJson($url, ['motivo' => str_repeat('a', 256)])->assertStatus(422);
        $this->actingAs($user)->postJson($url, ['motivo' => str_repeat('a', 10)])->assertRedirect();
        $this->actingAs($user)->postJson($url, ['motivo' => str_repeat('b', 255)])->assertRedirect();

        $this->assertDatabaseCount('moderacoes', 2);
    }

    public function test_denunciar_pergunta_inexistente_retorna_404(): void
    {
        $this->actingAs(User::factory()->create())
            ->post('/perguntas/99999/moderar', ['motivo' => 'Conteúdo ofensivo aqui'])
            ->assertNotFound();
    }

    public function test_so_o_dono_do_evento_ve_a_fila(): void
    {
        $url = route('eventos.moderacoes', $this->evento);
        Moderacao::create(['pergunta_id' => $this->pergunta->id, 'motivo' => 'Denúncia visível ao dono']);

        $this->get($url)->assertRedirect(route('login'));
        $this->actingAs(User::factory()->create())->get($url)->assertForbidden();
        $this->actingAs($this->organizador)->get($url)->assertOk()->assertSee('Denúncia visível ao dono');
    }

    public function test_fila_mostra_so_denuncias_do_evento_e_pagina_de_15_em_15(): void
    {
        $outra = Pergunta::factory()->create();
        Moderacao::create(['pergunta_id' => $outra->id, 'motivo' => 'Denúncia de outro evento']);
        foreach (range(1, 20) as $i) {
            Moderacao::create(['pergunta_id' => $this->pergunta->id, 'motivo' => "Denúncia número {$i} deste evento"]);
        }

        $resposta = $this->actingAs($this->organizador)->get(route('eventos.moderacoes', $this->evento))->assertOk();

        $this->assertCount(15, $resposta->viewData('moderacoes'));
        $this->assertSame(20, $resposta->viewData('moderacoes')->total());
        $resposta->assertDontSee('Denúncia de outro evento');
    }

    public function test_fila_nao_dispara_n_mais_1(): void
    {
        $contar = function (int $n): int {
            $evento = Evento::factory()->create(['user_id' => $this->organizador->id]);
            $perguntas = Pergunta::factory()->count($n)->create(['evento_id' => $evento->id]);
            foreach ($perguntas as $p) {
                Moderacao::create(['pergunta_id' => $p->id, 'motivo' => 'Denúncia de teste aqui']);
            }
            DB::enableQueryLog();
            $this->actingAs($this->organizador)->get(route('eventos.moderacoes', $evento))->assertOk();
            $total = count(DB::getQueryLog());
            DB::disableQueryLog();
            DB::flushQueryLog();

            return $total;
        };

        $this->assertSame($contar(2), $contar(10));
    }

    public function test_dono_rejeita_e_a_pergunta_some_do_telao(): void
    {
        $m = Moderacao::create(['pergunta_id' => $this->pergunta->id, 'motivo' => 'Conteúdo ofensivo aqui']);

        $this->actingAs($this->organizador)
            ->patch(route('moderacoes.status', $m), ['status' => 'rejeitada'])
            ->assertSessionHas('sucesso');

        $this->assertSame('rejeitada', $m->fresh()->status);
        $this->assertFalse($this->pergunta->fresh()->is_public);
        $this->get(route('eventos.show', $this->evento->id))->assertDontSee($this->pergunta->texto);
    }

    public function test_dono_aprova_e_a_pergunta_continua_publica(): void
    {
        $m = Moderacao::create(['pergunta_id' => $this->pergunta->id, 'motivo' => 'Conteúdo ofensivo aqui']);

        $this->actingAs($this->organizador)->patch(route('moderacoes.status', $m), ['status' => 'aprovada']);

        $this->assertSame('aprovada', $m->fresh()->status);
        $this->assertTrue($this->pergunta->fresh()->is_public);
    }

    public function test_so_o_dono_altera_o_status(): void
    {
        $m = Moderacao::create(['pergunta_id' => $this->pergunta->id, 'motivo' => 'Conteúdo ofensivo aqui']);
        $url = route('moderacoes.status', $m);

        $this->patch($url, ['status' => 'rejeitada'])->assertRedirect(route('login'));
        $this->actingAs(User::factory()->create())->patch($url, ['status' => 'rejeitada'])->assertForbidden();
        // Nem o autor da pergunta, nem quem denunciou, tem esse poder.
        $this->actingAs($this->pergunta->user)->patch($url, ['status' => 'rejeitada'])->assertForbidden();

        $this->assertSame('analisando', $m->fresh()->status);
        $this->assertTrue($this->pergunta->fresh()->is_public);
    }

    public function test_status_invalido_e_recusado(): void
    {
        $m = Moderacao::create(['pergunta_id' => $this->pergunta->id, 'motivo' => 'Conteúdo ofensivo aqui']);

        $this->actingAs($this->organizador)
            ->patchJson(route('moderacoes.status', $m), ['status' => 'qualquer'])
            ->assertStatus(422);

        $this->assertSame('analisando', $m->fresh()->status);
    }
}
