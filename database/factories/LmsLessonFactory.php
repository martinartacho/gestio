<?php

namespace Database\Factories;

use App\Models\CampusCourse;
use App\Models\LmsLesson;
use Illuminate\Database\Eloquent\Factories\Factory;

class LmsLessonFactory extends Factory
{
    protected $model = LmsLesson::class;

    public function definition(): array
    {
        static $sessionCounter = 1;

        return [
            'course_id'      => CampusCourse::factory(),
            'session_number' => $sessionCounter++,
            'title'          => $this->faker->sentence(4),
            'subtitle'       => $this->faker->sentence(6),
            'duration'       => $this->faker->randomElement(['45 min', '1h', '1h 30min']),
            'quote_text'     => $this->faker->sentence(8),
            'quote_author'   => $this->faker->name(),
            'quote_work'     => $this->faker->words(3, true),
            'intro_text'     => $this->faker->paragraph(),
            'topic_text'     => $this->faker->paragraph(),
            'concepts'       => [
                ['icon' => 'bulb', 'title' => 'Concepte 1', 'description' => $this->faker->sentence()],
            ],
            'text_cards'     => [
                ['type' => 'project',   'title' => 'Text del projecte', 'author' => 'Autor intern', 'year' => null,   'extract' => $this->faker->sentence(), 'analysis' => $this->faker->paragraph()],
                ['type' => 'reference', 'title' => 'Text de referència','author' => $this->faker->name(),  'year' => '2000', 'extract' => $this->faker->sentence(), 'analysis' => $this->faker->paragraph()],
            ],
            'comparison'     => [
                'left_label'  => 'Text 1',
                'right_label' => 'Text 2',
                'left_points' => ['Punt A', 'Punt B'],
                'right_points'=> ['Punt C', 'Punt D'],
            ],
            'reflection_questions' => [
                ['question' => $this->faker->sentence() . '?'],
                ['question' => $this->faker->sentence() . '?'],
            ],
            'exercise' => [
                'title'             => 'Exercici pràctic',
                'duration'          => '20 min',
                'statement'         => $this->faker->paragraph(),
                'examples'          => ['Exemple 1', 'Exemple 2'],
                'tips'              => ['Consell 1'],
                'demo_first_person' => null,
                'demo_third_person' => null,
            ],
            'status'     => 'published',
            'sort_order' => 0,
        ];
    }

    public function draft(): static
    {
        return $this->state(['status' => 'draft']);
    }

    public function published(): static
    {
        return $this->state(['status' => 'published']);
    }

    /**
     * Lliçó amb preguntes de tots els tipus per a tests.
     */
    public function withQuestions(): static
    {
        return $this->state([
            'questions' => [
                ['index' => 0, 'type' => 'open_text',   'block' => 'reflection', 'text' => 'Reflexió oberta', 'required' => false, 'points' => 0],
                ['index' => 1, 'type' => 'yes_no',       'block' => 'quiz', 'text' => 'Veritat o fals?', 'correct_answer' => true,  'required' => true, 'points' => 1],
                ['index' => 2, 'type' => 'choice_one',   'block' => 'quiz', 'text' => 'Tria una opció', 'options' => ['A', 'B', 'C'], 'correct_answer' => 'A', 'required' => true, 'points' => 2],
                ['index' => 3, 'type' => 'choice_many',  'block' => 'quiz', 'text' => 'Tria les correctes', 'options' => ['X', 'Y', 'Z'], 'correct_answers' => ['X', 'Y'], 'required' => false, 'points' => 2],
                ['index' => 4, 'type' => 'select_from_examples', 'block' => 'exercise', 'text' => 'Escull exemple', 'options' => ['Op1', 'Op2'], 'allow_custom' => true, 'required' => false, 'points' => 0],
            ],
        ]);
    }
}
