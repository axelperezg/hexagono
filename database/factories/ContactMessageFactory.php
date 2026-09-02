<?php

namespace Database\Factories;

use App\Enums\StudyType;
use App\Models\ContactMessage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ContactMessage>
 */
class ContactMessageFactory extends Factory
{
    protected $model = ContactMessage::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'institution' => $this->faker->company(),
            'email' => $this->faker->safeEmail(),
            'phone' => $this->faker->numerify('55########'),
            'study_type' => $this->faker->randomElement(StudyType::cases()),
            'message' => $this->faker->paragraph(),
        ];
    }
}
