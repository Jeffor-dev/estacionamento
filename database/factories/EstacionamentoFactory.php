<?php

namespace Database\Factories;

use App\Models\Estacionamento;
use App\Models\Motorista;
use App\Models\Caminhao;
use Illuminate\Database\Eloquent\Factories\Factory;

class EstacionamentoFactory extends Factory
{
    protected $model = Estacionamento::class;

    public function definition()
    {
        $entrada = $this->faker->dateTimeBetween('-30 days', 'now');
        $saida = $this->faker->boolean(70) ? $this->faker->dateTimeBetween($entrada, 'now') : null;
        
        return [
            'motorista_id' => Motorista::factory(),
            'entrada' => $entrada,
            'saida' => $saida,
            'valor_pagamento' => $saida ? $this->faker->randomFloat(2, 5, 50) : null,
            'tipo_pagamento' => $saida ? $this->faker->randomElement(['dinheiro', 'pix', 'cartao']) : null,
        ];
    }

    public function semSaida()
    {
        return $this->state(function (array $attributes) {
            return [
                'saida' => null,
                'valor_pagamento' => null,
                'tipo_pagamento' => null,
            ];
        });
    }

    public function comSaida()
    {
        return $this->state(function (array $attributes) {
            return [
                'saida' => $this->faker->dateTimeBetween($attributes['entrada'] ?? '-1 day', 'now'),
                'valor_pagamento' => $this->faker->randomFloat(2, 5, 50),
                'tipo_pagamento' => $this->faker->randomElement(['dinheiro', 'pix', 'cartao']),
            ];
        });
    }
}
