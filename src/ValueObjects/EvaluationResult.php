<?php

namespace Borah\LlmMonitoring\ValueObjects;

class EvaluationResult
{
  public function __construct(
    public readonly mixed $value,
    public readonly string $formattedValue,
    public readonly array $metadata = [],
  ) {
    //
  }
}
