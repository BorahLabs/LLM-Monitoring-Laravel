<?php

namespace Borah\LlmMonitoring\ValueObjects;

use Illuminate\Contracts\Support\Arrayable;

class EvaluationData implements Arrayable
{
    public function __construct(
        public readonly string $query,
        public readonly string $response,
        /**
         * @var array<string>|null
         */
        public readonly ?array $contextChunks = null,
        public readonly mixed $externalId = null,
    ) {
        //
    }

    public function toArray()
    {
        return [
            'query' => $this->query,
            'response' => $this->response,
            'chunks' => $this->contextChunks,
            'external_id' => $this->externalId,
        ];
    }
}
