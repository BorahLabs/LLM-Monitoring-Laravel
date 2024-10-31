<?php

namespace Borah\LLMMonitoring\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Borah\LLMMonitoring\LLMMonitoring
 */
class LLMMonitoring extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Borah\LLMMonitoring\LLMMonitoring::class;
    }
}
