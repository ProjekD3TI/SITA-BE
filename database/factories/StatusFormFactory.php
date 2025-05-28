<?php

namespace Database\Factories;

use App\Models\StatusForm;
use Illuminate\Database\Eloquent\Factories\Factory;

class StatusFormFactory extends Factory
{
    protected $model = StatusForm::class;

    public function definition()
    {
        return [
            'nama_form' => $this->faker->randomElement(['Form Pendaftaran TA', 'Form Seminar Proposal', 'Form Ujian Sidang']),
            'is_open' => $this->faker->boolean(),
        ];
    }
}
