<?php

namespace Borah\LlmMonitoring\Evaluations;

use Borah\LlmMonitoring\ValueObjects\EvaluationData;
use Borah\LlmMonitoring\ValueObjects\EvaluationResult;
use InvalidArgumentException;

class AnswerRelevance extends BaseEvaluation
{
    public const MAX_SCORE = 3;

    /**
     * @param  ChatResponse  $response
     */
    protected function evaluate(EvaluationData $data, mixed $response): EvaluationResult
    {
        if (! is_numeric($response->content[0])) {
            throw new InvalidArgumentException('Answer relevance evaluation response is not numeric.');
        }

        $total = intval($response->content[0]);

        return new EvaluationResult(
            value: $total / self::MAX_SCORE,
            formattedValue: number_format($total / self::MAX_SCORE * 100, 2).'%',
        );
    }

    public function systemPrompt(EvaluationData $data): string
    {
        return view('llm-monitoring::answer-relevance.system', $data->toArray())->render();
    }

    public function userPrompt(EvaluationData $data): string
    {
        return view('llm-monitoring::answer-relevance.user', $data->toArray())->render();
    }

    public function identifier(): string
    {
        return 'answer_relevance';
    }

    public function description(): string
    {
        return 'Evaluates how relevant the answer is to the question.';
    }
}
