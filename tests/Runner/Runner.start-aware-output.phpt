<?php

declare(strict_types=1);

use Tester\Assert;
use Tester\Runner\Test;

require __DIR__ . '/../bootstrap.php';
require __DIR__ . '/../../src/Runner/Test.php';
require __DIR__ . '/../../src/Runner/TestHandler.php';
require __DIR__ . '/../../src/Runner/Runner.php';
require __DIR__ . '/../../src/Runner/OutputHandler.php';
require __DIR__ . '/../../src/Runner/StartAwareOutputHandler.php';

class TimeAwareLogger implements \Tester\Runner\StartAwareOutputHandler
{
	/**
	 * @var float
	 */
	public $lastTimeStarted = NULL;

	function start(Test $test): void
	{
		$this->lastTimeStarted = microtime(TRUE);
	}

	function begin(): void
	{
	}

	function prepare(Test $test): void
	{
	}

	function finish(Test $test): void
	{
	}

	function end(): void
	{
	}
}

$start = microtime(TRUE);
$runner = new Tester\Runner\Runner(createInterpreter());
$runner->paths = [__DIR__ . "/start-aware/*.phptx"];
$runner->outputHandlers[] = $logger = new TimeAwareLogger;

$toleranceMicroSeconds = 1000;
$runner->run();

Assert::notSame(NULL, $logger->lastTimeStarted, "Test start time should be logged");
$difference = abs($start - $logger->lastTimeStarted);
if ($difference > $toleranceMicroSeconds) {
   Assert::fail("Time {$logger->lastTimeStarted} should be within {$toleranceMicroSeconds} microseconds of $start, was {$difference}");
}
