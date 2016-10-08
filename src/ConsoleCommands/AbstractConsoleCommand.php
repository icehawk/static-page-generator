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
		return $this->getApplication();
	}
}
