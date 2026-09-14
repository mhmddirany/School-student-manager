<?php

namespace Database\Factories;

use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;

class StudentFactory extends Factory
{
    protected $model = Student::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            // Mostly active, a few inactive — gives the status filter something to filter.
            'status' => $this->faker->randomElement(['active', 'active', 'active', 'inactive']),
        ];
    }
}
