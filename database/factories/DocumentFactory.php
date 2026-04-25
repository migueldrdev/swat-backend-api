<?php

namespace Database\Factories;

use App\Models\Document;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class DocumentFactory extends Factory
{
    protected $model = Document::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'title' => $this->faker->sentence(3),
            'file_path' => 'trabajadores/test/' . $this->faker->uuid . '.pdf',
            'type' => $this->faker->randomElement(['boleta', 'contrato', 'alta']),
        ];
    }
}
