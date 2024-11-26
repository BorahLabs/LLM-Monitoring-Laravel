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

abstract class BaseEvaluation
{
    /**
     * @param  ChatResponse|array<ChatResponse>  $response
     */
    abstract protected function evaluate(EvaluationData $data, mixed $response): EvaluationResult;

    /**
     * Useful for evaluations that need to be run on each chunk independently.
     */
    protected function shouldEvaluateEachChunkIndependently(): bool
    {
        return false;
    }

    /**
     * Custom LLM driver to be used for this evaluation
     */
    protected function driver(): ?string
    {
        return null;
    }

    /**
     * Custom model to be used for this evaluation
     */
    protected function model(): ?LlmModel
    {
        return null;
    }

    abstract public function identifier(): string;

    abstract public function description(): string;

    abstract public function systemPrompt(EvaluationData $data): string;

    abstract public function userPrompt(EvaluationData $data): string;

    public function requiresContextChunks(): bool
    {
        return false;
    }

    public function run(EvaluationData $data): EvaluationResult
    {
        if ($this->requiresContextChunks() && empty($data->contextChunks)) {
            if ($this->shouldEvaluateEachChunkIndependently()) {
                return $this->evaluate($data, []);
            }

            return $this->evaluate($data, new ChatResponse(id: '', content: '', finishReason: 'unknown', usage: null));
        }

        $driver = LLMPort::driver($this->driver() ?? config('llm-monitoring.llmport.driver', config('llmport.default')));
        if ($this->model()) {
            $driver->using($this->model());
        } elseif ($model = config('llm-monitoring.llmport.model')) {
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
