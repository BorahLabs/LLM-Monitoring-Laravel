<?php

return [
    'llmport' => [
        'driver' => null, // one of the llmport.php drivers
        'model' => null,
    ],
    'probability' => 100, // 0 to 100. Chance of a response being evaluated. 100 is always.
    'evaluations' => [
        \Borah\LlmMonitoring\Evaluations\AnswerRelevance::class,
        \Borah\LlmMonitoring\Evaluations\ContextRelevanceChainOfThought::class,
    ],
];
