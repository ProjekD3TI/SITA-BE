<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    public function __construct(...$args)
    {
        parent::__construct(...$args);
        $this->faker->addProvider(new \Faker\Provider\id_ID\Person($this->faker));
    }
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nim' => $this->faker->unique()->numerify('V34#####'),
            'name' => $this->faker->name('id_ID'), // nama Indonesia
            'email' => $this->faker->userName() . '@student.uns.ac.id',
            'no_telp' => $this->faker->phoneNumber(),
            'password' => static::$password ??= Hash::make('123123123'),
            'status' => 1,
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn(array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}