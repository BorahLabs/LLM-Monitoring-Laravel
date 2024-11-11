<?php

use Borah\LlmMonitoring\Evaluations\ContextRelevanceChainOfThought;
use Borah\LlmMonitoring\ValueObjects\EvaluationData;

test('cannot be called without chunks', function () {
    $evaluation = new ContextRelevanceChainOfThought();
    expect($evaluation->requiresContextChunks())->toBeTrue();

    $data = new EvaluationData(
        query: 'What is the capital of France?',
        response: 'The capital of France is Paris.',
    );

    $evaluation->run($data);
})->expectException(InvalidArgumentException::class);

test('evaluate context relevance for relevant query', function () {
    $data = new EvaluationData(
        query: 'What is the capital of France?',
        response: 'The capital of France is Paris.',
        contextChunks: [
            'Francia, en Europa Occidental, abarca ciudades medievales, villas alpinas y playas mediterráneas. París, su capital, es famosa por sus firmas de alta costura, los museos de arte clásico, como el Louvre, y monumentos como la Torre Eiffel',
        ],
    );

    $evaluation = new ContextRelevanceChainOfThought();
    $result = $evaluation->run($data);

    expect($result->value)->toBe(1);
    expect($result->formattedValue)->toBe('100.00%');
});

test('evaluate context relevance for slightly irrelevant query', function () {
    $data = new EvaluationData(
        query: 'What is the capital of France?',
        response: 'The capital of France is Paris.',
        contextChunks: [
            '1. Zinedine Zidane. Zinedine Zidane, often referred to as "Zizou," is widely considered one of the greatest footballers of all time. Known for his meticulous elegance, vision, and technique, Zidane\'s ability to control the game from midfield was unparalleled.',
            'The eyes of the sporting world are currently on France with the 2024 Olympics currently taking place in the French capital. It\'s been a festival of football this summer with Spain winning the European Championships and Argentina victorious again in the Copa América.',
        ],
    );

    $evaluation = new ContextRelevanceChainOfThought();
    $result = $evaluation->run($data);

    expect($result->value)->toBeLessThan(1);
});

test('evaluate context relevance for irrelevant query', function () {
    $data = new EvaluationData(
        query: 'What is the capital of France?',
        response: 'The capital of France is Paris.',
        contextChunks: [
            'I like french pizzas',
        ],
    );

    $evaluation = new ContextRelevanceChainOfThought();
    $result = $evaluation->run($data);

    expect($result->value)->toBe(0);
});

test('uses the right prompts', function () {
    $data = new EvaluationData(
        query: 'What is the capital of France?',
        response: 'The capital of France is Paris.',
        contextChunks: [
            '1. Zinedine Zidane. Zinedine Zidane, often referred to as "Zizou," is widely considered one of the greatest footballers of all time. Known for his meticulous elegance, vision, and technique, Zidane\'s ability to control the game from midfield was unparalleled.',
            'The eyes of the sporting world are currently on France with the 2024 Olympics currently taking place in the French capital. It\'s been a festival of football this summer with Spain winning the European Championships and Argentina victorious again in the Copa América.',
        ],
    );

    $evaluation = new ContextRelevanceChainOfThought();

    $systemPrompt = $evaluation->systemPrompt($data);
    $userPrompt = $evaluation->userPrompt($data);

    expect($systemPrompt)
        ->toBeString()
        ->toBe(view('llm-monitoring::context-relevance-cot.system', $data->toArray())->render());
    expect($userPrompt)
        ->toBeString()
        ->toBe(view('llm-monitoring::context-relevance-cot.user', $data->toArray())->render())
        ->toContain($data->query)
        ->toContain(htmlspecialchars($data->contextChunks[0]));
});
