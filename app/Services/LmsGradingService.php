<?php

namespace App\Services;

class LmsGradingService
{
    /**
     * Avalua la resposta d'un alumne per a una pregunta donada.
     *
     * @param  array  $question  La definició de la pregunta (de questions[])
     * @param  mixed  $answer    La resposta de l'alumne (string|array|bool)
     * @return array{score: float|null, auto_graded: bool, is_correct: bool|null}
     */
    public function grade(array $question, mixed $answer): array
    {
        $type   = $question['type'] ?? 'open_text';
        $points = (float) ($question['points'] ?? 0);

        return match ($type) {
            'yes_no'      => $this->gradeYesNo($question, $answer, $points),
            'choice_one'  => $this->gradeChoiceOne($question, $answer, $points),
            'choice_many' => $this->gradeChoiceMany($question, $answer, $points),
            default       => ['score' => null, 'auto_graded' => false, 'is_correct' => null],
        };
    }

    // ─── Tipus avaluables ──────────────────────────────────────────────────────

    private function gradeYesNo(array $question, mixed $answer, float $points): array
    {
        // correct_answer pot ser true/false/bool string
        if (! array_key_exists('correct_answer', $question)) {
            return ['score' => null, 'auto_graded' => false, 'is_correct' => null];
        }

        $correct   = (bool) $question['correct_answer'];
        $given     = (bool) $answer;
        $isCorrect = ($given === $correct);

        return [
            'score'       => $isCorrect ? $points : 0.0,
            'auto_graded' => true,
            'is_correct'  => $isCorrect,
        ];
    }

    private function gradeChoiceOne(array $question, mixed $answer, float $points): array
    {
        if (! isset($question['correct_answer'])) {
            return ['score' => null, 'auto_graded' => false, 'is_correct' => null];
        }

        $isCorrect = (string) $answer === (string) $question['correct_answer'];

        return [
            'score'       => $isCorrect ? $points : 0.0,
            'auto_graded' => true,
            'is_correct'  => $isCorrect,
        ];
    }

    private function gradeChoiceMany(array $question, mixed $answer, float $points): array
    {
        if (! isset($question['correct_answers']) || ! is_array($question['correct_answers'])) {
            return ['score' => null, 'auto_graded' => false, 'is_correct' => null];
        }

        $correctSet = array_map('strval', $question['correct_answers']);
        $givenSet   = is_array($answer) ? array_map('strval', $answer) : [];

        $correctCount = count($correctSet);
        if ($correctCount === 0) {
            return ['score' => 0.0, 'auto_graded' => true, 'is_correct' => false];
        }

        // Intersecció correcta menys les incorrectes seleccionades
        $hits   = count(array_intersect($givenSet, $correctSet));
        $misses = count(array_diff($givenSet, $correctSet));

        // Puntuació proporcional (penalització per errades)
        $ratio  = max(0, ($hits - $misses) / $correctCount);
        $score  = round($ratio * $points, 2);

        return [
            'score'       => $score,
            'auto_graded' => true,
            'is_correct'  => $hits === $correctCount && $misses === 0,
        ];
    }
}
