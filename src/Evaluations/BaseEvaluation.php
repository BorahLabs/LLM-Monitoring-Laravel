<?php

namespace Borah\LlmMonitoring\Evaluations;

use Borah\LlmMonitoring\ValueObjects\EvaluationData;
use Borah\LlmMonitoring\ValueObjects\EvaluationResult;
use Borah\LLMPort\Enums\MessageRole;
use Borah\LLMPort\Facades\LLMPort;
use Borah\LLMPort\ValueObjects\ChatMessage;
use Borah\LLMPort\ValueObjects\ChatRequest;
use Borah\LLMPort\ValueObjects\ChatResponse;
use Borah\LLMPort\ValueObjects\LlmModel;
use InvalidArgumentException;

abstract class BaseEvaluation
{
  /**
   * @param EvaluationData $data
   * @param ChatResponse|array<ChatResponse> $response
   * @return EvaluationResult
   */
  protected abstract function evaluate(EvaluationData $data, mixed $response): EvaluationResult;

  /**
   * Useful for evaluations that need to be run on each chunk independently.
   */
  protected function shouldEvaluateEachChunkIndependently(): bool
  {
    return false;
  }

  /**
   * Custom LLM driver to be used for this evaluation
   *
   * @return string|null
   */
  protected function driver(): ?string
  {
    return null;
  }

  /**
   * Custom model to be used for this evaluation
   *
   * @return LlmModel|null
   */
  protected function model(): ?LlmModel
  {
    return null;
  }

  public abstract function identifier(): string;

  public abstract function description(): string;

  public abstract function systemPrompt(EvaluationData $data): string;

  public abstract function userPrompt(EvaluationData $data): string;

  public function requiresContextChunks(): bool
  {
    return false;
  }

  public function run(EvaluationData $data): EvaluationResult
  {
    if ($this->requiresContextChunks() && empty($data->contextChunks)) {
      throw new InvalidArgumentException('Context chunks are required for this evaluation.');
    }

    $driver = LLMPort::driver($this->driver() ?? config('llm-monitoring.llmport.driver', config('llmport.default')));
    if ($this->model()) {
      $driver->using($this->model());
    } else if ($model = config('llm-monitoring.llmport.model')) {
      $driver->using($model);
    }

    if ($this->shouldEvaluateEachChunkIndependently()) {
      $responses = [];
      foreach ($data->contextChunks as $chunk) {
        $chunkEvaluationData = new EvaluationData(
          query: $data->query,
          response: $data->response,
          contextChunks: [$chunk],
        );

        $responses[] = $driver->chat(new ChatRequest(
          messages: [
            new ChatMessage(role: MessageRole::System, content: $this->systemPrompt($chunkEvaluationData)),
            new ChatMessage(role: MessageRole::User, content: $this->userPrompt($chunkEvaluationData)),
          ],
          temperature: 0.3,
        ));
      }

      return $this->evaluate($data, $responses);
    }

    $response = $driver->chat(new ChatRequest(
      messages: [
        new ChatMessage(role: MessageRole::System, content: $this->systemPrompt($data)),
        new ChatMessage(role: MessageRole::User, content: $this->userPrompt($data)),
      ],
      temperature: 0.3,
    ));

    return $this->evaluate($data, $response);
  }
}