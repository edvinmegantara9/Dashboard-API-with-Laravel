<?php

namespace Database\Factories;

use App\Models\Roles;
use Illuminate\Database\Eloquent\Factories\Factory;

class RolesFactory extends Factory
{
    protected $model = Roles::class;

    public function definition(): array
    {
    	return [
    	    'name' => $this->faker->name(),
            'is_opd' => RolesFactory::oneOrZero()

    	];
    }

    static function oneOrZero() {
        return rand(0, 100) > 50 ? 1 : 0;
    }
}
