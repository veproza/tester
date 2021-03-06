<?php

/**
 * This file is part of the Nette Tester.
 * Copyright (c) 2009 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Tester\CodeCoverage\Generators;


/**
 * Code coverage report generator.
 */
abstract class AbstractGenerator
{
	protected const
		CODE_DEAD = -2,
		CODE_UNTESTED = -1,
		CODE_TESTED = 1;

	/** @var array */
	public $acceptFiles = ['php', 'phpt', 'phtml'];

	/** @var array */
	protected $data;

	/** @var string */
	protected $source;

	/** @var int */
	protected $totalSum = 0;

	/** @var int */
	protected $coveredSum = 0;


	/**
	 * @param  string  $file  path to coverage.dat file
	 * @param  string  $source  path to covered source file or directory
	 */
	public function __construct(string $file, string $source = null)
	{
		if (!is_file($file)) {
			throw new \Exception("File '$file' is missing.");
		}

		$this->data = @unserialize(file_get_contents($file)); // @ is escalated to exception
		if (!is_array($this->data)) {
			throw new \Exception("Content of file '$file' is invalid.");
		}

		if (!$source) {
			$source = key($this->data);
			for ($i = 0; $i < strlen($source); $i++) {
				foreach ($this->data as $s => $foo) {
					if (!isset($s[$i]) || $source[$i] !== $s[$i]) {
						$source = substr($source, 0, $i);
						break 2;
					}
				}
			}
			$source = dirname($source . 'x');

		} elseif (!file_exists($source)) {
			throw new \Exception("File or directory '$source' is missing.");
		}

		$this->source = realpath($source);
	}


	public function render(string $file = null): void
	{
		$handle = $file ? @fopen($file, 'w') : STDOUT; // @ is escalated to exception
		if (!$handle) {
			throw new \Exception("Unable to write to file '$file'.");
		}

		ob_start(function (string $buffer) use ($handle) { fwrite($handle, $buffer); }, 4096);
		try {
			$this->renderSelf();
		} catch (\Exception $e) {
		}
		ob_end_flush();
		fclose($handle);

		if (isset($e)) {
			if ($file) {
				unlink($file);
			}
			throw $e;
		}
	}


	public function getCoveredPercent(): float
	{
		return $this->totalSum ? $this->coveredSum * 100 / $this->totalSum : 0;
	}


	protected function getSourceIterator(): \Iterator
	{
		$iterator = is_dir($this->source)
			? new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($this->source))
			: new \ArrayIterator([new \SplFileInfo($this->source)]);

		return new \CallbackFilterIterator($iterator, function (\SplFileInfo $file): bool {
			return $file->getBasename()[0] !== '.'  // . or .. or .gitignore
				&& in_array($file->getExtension(), $this->acceptFiles, true);
		});
	}


	abstract protected function renderSelf();
}
