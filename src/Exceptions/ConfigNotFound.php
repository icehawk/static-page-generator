<?php declare(strict_types = 1);
/**
 * @author hollodotme
 */

namespace IceHawk\StaticPageGenerator\Exceptions;

/**
 * Class ConfigNotFound
 * @package IceHawk\StaticPageGenerator\Exceptions
 */
final class ConfigNotFound extends StaticPageGeneratorException
{
	/** @var string */
	private $configPath;

	public function getConfigPath(): string
	{
		return $this->configPath;
	}

	public function withConfigPath( string $configPath ): ConfigNotFound
	{
		$this->configPath = $configPath;

		return $this;
	}
}
