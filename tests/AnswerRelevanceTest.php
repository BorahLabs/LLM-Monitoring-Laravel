<?php

use Borah\LlmMonitoring\Evaluations\AnswerRelevance;
use Borah\LlmMonitoring\ValueObjects\EvaluationData;

test('can be called without chunks', function () {
    $evaluation = new AnswerRelevance();
    expect($evaluation->requiresContextChunks())->toBeFalse();

    $data = new EvaluationData(
        query: 'What is the capital of France?',
        response: 'The capital of France is Paris.',
    );

    $evaluation->run($data);
});

test('evaluate answer relevance for relevant query', function () {
    $data = new EvaluationData(
        query: 'What is the capital of France?',
        response: 'The capital of France is Paris.',
    );

    $evaluation = new AnswerRelevance();
    $result = $evaluation->run($data);

    expect($result->value)->toBe(1);
    expect($result->formattedValue)->toBe('100.00%');
});

test('evaluate answer relevance for slightly irrelevant', function () {
    $data = new EvaluationData(
        query: 'What is the capital of France and Spain?',
        response: 'The capital of France is Paris.',
    );

    $evaluation = new AnswerRelevance();
    $result = $evaluation->run($data);

    expect($result->value)->toBeLessThan(1);
});

test('evaluate context relevance for irrelevant query', function () {
    $data = new EvaluationData(
        query: 'What is the capital of France?',
        response: 'I like croissants',
    );

    $evaluation = new AnswerRelevance();
    $result = $evaluation->run($data);

    expect($result->value)->toBe(0);
});

test('uses the right prompts', function () {
    $data = new EvaluationData(
        query: 'What is the capital of France?',
        response: 'The capital of France is Paris.',
    );

    $evaluation = new AnswerRelevance();

    $systemPrompt = $evaluation->systemPrompt($data);
    $userPrompt = $evaluation->userPrompt($data);

    expect($systemPrompt)
        ->toBeString()
        ->toBe(view('llm-monitoring::answer-relevance.system', $data->toArray())->render());
    expect($userPrompt)
        ->toBeString()
        ->toBe(view('llm-monitoring::answer-relevance.user', $data->toArray())->render())
        ->toContain($data->query)
        ->toContain($data->response);
});
