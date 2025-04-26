<?php

namespace Borah\LlmMonitoring\Facades;

use Borah\LlmMonitoring\LlmMonitoring as LlmMonitoringClass;
use Borah\LlmMonitoring\ValueObjects\EvaluationData;
use Illuminate\Support\Facades\Facade;

/**
 * @method static void dispatch(EvaluationData $data)
 * @method static array evaluate(EvaluationData $data)
 *
 * @see \Borah\LlmMonitoring\LlmMonitoring
 */
class LlmMonitoring extends Facade
{
    protected static function getFacadeAccessor()
    {
        return LlmMonitoringClass::class;
    }
}