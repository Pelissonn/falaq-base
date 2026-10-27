<?php

namespace Database\Factories;

use App\Models\Evento;
use App\Models\Pergunta;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Pergunta>
 */
class PerguntaFactory extends Factory
{
    protected $model = Pergunta::class;

    public function definition(): array
    {
        return [
            'evento_id' => Evento::factory(),
            'user_id' => User::factory(),
            'texto' => 'Pergunta de teste: '.fake()->sentence(8),
            'status' => 'aprovado',
            'is_public' => true,
        ];
    }

    /** Pergunta aguardando moderação (invisível para o público). */
    public function pendente(): static
    {
        return $this->state(fn () => ['status' => 'pendente', 'is_public' => false]);
    }
}
