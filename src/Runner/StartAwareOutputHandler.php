<?php
declare(strict_types=1);

namespace Tester\Runner;

/**
 * For output handlers that wish to output the time it took to perform a test
 */
interface StartAwareOutputHandler extends OutputHandler
{
    /**
     * Called when the test starts
     * @param Test $test
     */
    function start(Test $test): void;
}