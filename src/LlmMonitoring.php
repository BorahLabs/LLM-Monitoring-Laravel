<?php

namespace Borah\LlmMonitoring;

use Borah\LlmMonitoring\Models\LlmCallEvaluation;
use Borah\LlmMonitoring\ValueObjects\EvaluationData;
use Borah\LlmMonitoring\ValueObjects\EvaluationResult;
use Illuminate\Support\Lottery;

class LlmMonitoring {
  protected array $evaluations = [];

  public function __construct()
  {
    $this->evaluations = config('llm-monitoring.evaluations');

    abort_if(config('llm-monitoring.probability') > 100 || config('llm-monitoring.probability') < 0, new \Exception('Probability must be between 0 and 100'));
  }

  public function dispatch(EvaluationData $data): void
  {
    // Lottery::odds(config('llm-monitoring.probability'), 100)
    //   ->winner(function () use ($data) {
        dispatch(function () use ($data) {
          $evaluations = $this->evaluate($data);

          foreach ($evaluations as $identifier => $evaluation) {
            LlmCallEvaluation::query()->create([
              'metric' => $identifier,
              'value' => $evaluation->value,
              'formatted_value' => $evaluation->formattedValue,
              'metadata' => $evaluation->metadata,
              'llm_call_id' => $data->externalId,
            ]);
          }
        });
      // });
  }

  /**
   * @param EvaluationData $data
   * @return array<string, EvaluationResult>
   */
  public function evaluate(EvaluationData $data): array
  {
    $results = [];
    foreach ($this->evaluations as $evaluation) {
      /**
       * @var \Borah\LlmMonitoring\Evaluations\BaseEvaluation
       */
      $eval = new $evaluation();
      $results[$eval->identifier()] = $eval->run($data);
    }

    return $results;
  }
}
