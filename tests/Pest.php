<?php

use Borah\LlmMonitoring\Tests\TestCase;

$dotenv = \Dotenv\Dotenv::createImmutable(realpath(__DIR__.'/../'));
$dotenv->load();

uses(TestCase::class)->in(__DIR__);
