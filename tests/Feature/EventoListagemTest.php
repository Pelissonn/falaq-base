<?php

namespace Tests\Feature;

use App\Models\Evento;
use App\Models\Pergunta;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Sprints 1 (paginação), 2 (autor + N+1) e 3 (is_public).
 */
class EventoListagemTest extends TestCase
{
    use RefreshDatabase;

    public function test_lista_apenas_perguntas_do_evento_atual(): void
    {
        $evento = Evento::factory()->create();
        $outro = Evento::factory()->create();
        Pergunta::factory()->create(['evento_id' => $evento->id, 'texto' => 'Pergunta do evento atual']);
        Pergunta::factory()->create(['evento_id' => $outro->id, 'texto' => 'Pergunta de outro evento']);

        $this->get(route('eventos.show', $evento->id))
            ->assertOk()
            ->assertSee('Pergunta do evento atual')
            ->assertDontSee('Pergunta de outro evento');
    }

    public function test_pagina_de_10_em_10_com_links_de_navegacao(): void
    {
        $evento = Evento::factory()->create();
        Pergunta::factory()->count(25)->create(['evento_id' => $evento->id]);

        $resposta = $this->get(route('eventos.show', $evento->id))->assertOk();

        $this->assertCount(10, $resposta->viewData('perguntas'));
        $this->assertSame(25, $resposta->viewData('perguntas')->total());
        $resposta->assertSee('?page=2', false);

        $ultima = $this->get(route('eventos.show', [$evento->id, 'page' => 3]));
        $this->assertCount(5, $ultima->viewData('perguntas'));
    }

    public function test_mais_recentes_aparecem_primeiro_quando_nao_ha_votos(): void
    {
        $evento = Evento::factory()->create();
        $antiga = Pergunta::factory()->create(['evento_id' => $evento->id, 'created_at' => now()->subHour()]);
        $nova = Pergunta::factory()->create(['evento_id' => $evento->id, 'created_at' => now()]);

        $ids = $this->get(route('eventos.show', $evento->id))
            ->viewData('perguntas')->pluck('id')->all();

        $this->assertSame([$nova->id, $antiga->id], $ids);
    }

    public function test_exibe_o_nome_do_autor_ou_anonimo(): void
    {
        $evento = Evento::factory()->create();
        $autor = User::factory()->create(['name' => 'Maria Autora']);
        Pergunta::factory()->create(['evento_id' => $evento->id, 'user_id' => $autor->id]);
        Pergunta::factory()->create(['evento_id' => $evento->id, 'user_id' => null]);

        $this->get(route('eventos.show', $evento->id))
            ->assertSee('Maria Autora')
            ->assertSee('Anônimo');
    }

    public function test_nao_dispara_n_mais_1_consultas_para_o_autor(): void
    {
        $contarConsultas = function (int $quantidade): int {
            $evento = Evento::factory()->create();
            Pergunta::factory()->count($quantidade)->create(['evento_id' => $evento->id]);

            DB::enableQueryLog();
            $this->get(route('eventos.show', $evento->id))->assertOk();
            $total = count(DB::getQueryLog());
            DB::disableQueryLog();
            DB::flushQueryLog();

            return $total;
        };

        $com2 = $contarConsultas(2);
        $com10 = $contarConsultas(10);

        $this->assertSame($com2, $com10, 'O número de consultas não pode crescer com a quantidade de perguntas.');
    }

    public function test_perguntas_pendentes_nao_aparecem_para_o_publico(): void
    {
        $evento = Evento::factory()->create();
        Pergunta::factory()->create(['evento_id' => $evento->id, 'texto' => 'Pergunta já aprovada']);
        Pergunta::factory()->pendente()->create(['evento_id' => $evento->id, 'texto' => 'Pergunta aguardando moderação']);

        $this->get(route('eventos.show', $evento->id))
            ->assertSee('Pergunta já aprovada')
            ->assertDontSee('Pergunta aguardando moderação');
    }

    public function test_paginacao_e_contagem_consideram_so_perguntas_publicas(): void
    {
        $evento = Evento::factory()->create();
        Pergunta::factory()->count(12)->create(['evento_id' => $evento->id]);
        Pergunta::factory()->pendente()->count(8)->create(['evento_id' => $evento->id]);

        $perguntas = $this->get(route('eventos.show', $evento->id))->viewData('perguntas');

        $this->assertSame(12, $perguntas->total());
        $this->assertSame(2, $perguntas->lastPage());
    }

    public function test_pagina_principal_esconde_rascunhos_e_conta_so_perguntas_publicas(): void
    {
        $publicado = Evento::factory()->create(['titulo' => 'Evento Publicado']);
        Evento::factory()->rascunho()->create(['titulo' => 'Evento em Rascunho']);
        Pergunta::factory()->count(3)->create(['evento_id' => $publicado->id]);
        Pergunta::factory()->pendente()->count(2)->create(['evento_id' => $publicado->id]);

        $resposta = $this->get('/')->assertOk()
            ->assertSee('Evento Publicado')
            ->assertDontSee('Evento em Rascunho');

        $this->assertSame(3, $resposta->viewData('eventos')->first()->perguntas_count);
    }

    public function test_rascunho_so_pode_ser_visto_pelo_organizador(): void
    {
        $evento = Evento::factory()->rascunho()->create();

        $this->get(route('eventos.show', $evento->id))->assertNotFound();
        $this->actingAs(User::factory()->create())
            ->get(route('eventos.show', $evento->id))->assertNotFound();
        $this->actingAs($evento->user)
            ->get(route('eventos.show', $evento->id))->assertOk();
    }
}
