<?php declare(strict_types = 1);
/**
 * @author hollodotme
 */

namespace IceHawk\StaticPageGenerator\ConsoleCommands;

use IceHawk\StaticPageGenerator\StaticPageGenerator;
use Symfony\Component\Console\Command\Command;

/**
 * Class AbstractConsoleCommand
 * @package IceHawk\StaticPageGenerator\ConsoleCommands
 */
abstract class AbstractConsoleCommand extends Command
{
	public function getEnv() : StaticPageGenerator
	{
		/** @var StaticPageGenerator $spg */
		$spg = $this->getApplication();

		return $spg;
	}

	final protected function getFullPath( string $dir, string $file ) : string
	{
		return rtrim( $dir, DIRECTORY_SEPARATOR ) . DIRECTORY_SEPARATOR . ltrim( $file, DIRECTORY_SEPARATOR );
	}
}
