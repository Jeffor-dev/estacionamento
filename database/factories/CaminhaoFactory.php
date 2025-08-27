<?php

namespace Database\Factories;

use App\Models\Caminhao;
use Illuminate\Database\Eloquent\Factories\Factory;

class CaminhaoFactory extends Factory
{
    protected $model = Caminhao::class;

    public function definition()
    {
        return [
            'placa' => strtoupper($this->faker->bothify('???####')),
            'modelo' => $this->faker->randomElement(['Volvo FH', 'Scania R450', 'Mercedes Actros', 'DAF XF', 'Iveco Stralis']),
            'cor' => $this->faker->colorName(),
        ];
    }
}
