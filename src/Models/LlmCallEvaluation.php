<?php

namespace Borah\LlmMonitoring\Models;

use Illuminate\Database\Eloquent\Model;

class LlmCallEvaluation extends Model
{
    protected $table = 'llm_call_evaluations';

    protected $guarded = [];

    protected $casts = [
        'metadata' => 'array',
    ];
}
