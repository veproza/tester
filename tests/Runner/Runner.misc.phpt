<?php

declare(strict_types=1);

use Tester\Assert;
use Tester\Runner\Test;

require __DIR__ . '/../bootstrap.php';
require __DIR__ . '/../../src/Runner/OutputHandler.php';
require __DIR__ . '/../../src/Runner/Test.php';
require __DIR__ . '/../../src/Runner/TestHandler.php';
require __DIR__ . '/../../src/Runner/Runner.php';


class Logger implements Tester\Runner\OutputHandler
{
	public $results = [];


	public function prepare(Test $test): void
	{
	}


	public function finish(Test $test): void
	{
		$this->results[basename($test->getFile())] = $test->getResult();
	}


	public function begin(): void
	{
	}


	public function end(): void
	{
	}
}

Assert::false(getenv('TesterEnvVar'));

$runner = new Tester\Runner\Runner(createInterpreter());
$runner->paths[] = __DIR__ . '/misc/*.phptx';
$runner->outputHandlers[] = $logger = new Logger;
$runner->setEnvironmentVariable('TesterEnvVar', 'Is here!');
$runner->run();

Assert::false(getenv('TesterEnvVar'));

Assert::same(Test::PASSED, $logger->results['env-vars.phptx']);
