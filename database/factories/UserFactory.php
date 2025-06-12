<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = User::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->name,
            'line_id' => 'U'.$this->faker->unique()->regexify('[a-f0-9]{32}'),
            'avatar' => $this->faker->imageUrl(300, 300, 'people'),
            'access_token' => $this->faker->regexify('[A-Za-z0-9]{200}'),
            'refresh_token' => $this->faker->regexify('[A-Za-z0-9]{43}'),
            'notify_token' => $this->faker->regexify('[A-Za-z0-9]{43}'),
            'remember_token' => Str::random(10),
        ];
    }
}
