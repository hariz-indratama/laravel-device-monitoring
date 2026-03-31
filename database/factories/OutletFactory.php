<?php

namespace Database\Factories;

use App\Models\Outlet;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Outlet>
 */
class OutletFactory extends Factory
{
    protected $model = Outlet::class;

    public function definition(): array
    {
        return [
            'user_id'  => User::factory(),
            'name'     => fake()->company(),
            'address'  => fake()->streetAddress(),
            'phone'    => fake()->phoneNumber(),
            'email'    => fake()->companyEmail(),
            'kota'     => fake()->city(),
            'provinsi' => fake()->state(),
            'kode_pos' => fake()->postcode(),
        ];
    }
}
