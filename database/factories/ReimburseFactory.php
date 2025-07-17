<?php

namespace Database\Factories;

use App\Enums\StatusReimburse;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Reimburse>
 */
class ReimburseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'requested_by' => \App\Models\User::factory(),
            'requested_at' => $this->faker->date('Y-m-d'),
            'note' => $this->faker->text(200),
            'status' => $this->faker->randomElement(StatusReimburse::class),
        ];
    }
}
