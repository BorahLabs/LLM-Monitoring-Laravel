<?php

namespace Borah\LlmMonitoring\Evaluations;

use Borah\LlmMonitoring\ValueObjects\EvaluationData;
use Borah\LlmMonitoring\ValueObjects\EvaluationResult;
use InvalidArgumentException;

class ContextRelevance extends BaseEvaluation
{
  public const MAX_SCORE = 3;

  protected function shouldEvaluateEachChunkIndependently(): bool
  {
    return true;
  }

  /**
   * @param EvaluationData $data
   * @param array<ChatResponse> $responses
   * @return EvaluationResult
   */
  protected function evaluate(EvaluationData $data, mixed $responses): EvaluationResult
  {
    $total = 0;
    foreach ($responses as $response) {
      if (!is_numeric($response->content[0])) {
        throw new InvalidArgumentException('Context relevance evaluation response is not numeric.');
      }

      $total += intval($response->content[0]);
    }

    return new EvaluationResult(
      value: $total / (count($responses) * self::MAX_SCORE),
      formattedValue: number_format($total / (count($responses) * self::MAX_SCORE) * 100, 2).'%',
    );
  }

  public function systemPrompt(EvaluationData $data): string
  {
    return view('llm-monitoring::context-relevance.system', $data->toArray())->render();
  }

  public function userPrompt(EvaluationData $data): string
  {
    return view('llm-monitoring::context-relevance.user', $data->toArray())->render();
  }

  public function requiresContextChunks(): bool
  {
    return true;
  }

  public function identifier(): string
  {
    return 'context_relevance';
  }

  public function description(): string
  {
    return 'Evaluates how relevant each context chunk is to the question. The average score is used. For example, a score of 75% means that 75% of the context chunks were relevant to the question.';
  }
}
