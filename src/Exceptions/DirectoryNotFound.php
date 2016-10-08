<?php declare(strict_types = 1);
/**
 * @author hollodotme
 */

namespace IceHawk\StaticPageGenerator\Exceptions;

/**
 * Class DirectoryNotFound
 * @package IceHawk\StaticPageGenerator\Exceptions
 */
final class DirectoryNotFound extends StaticPageGeneratorException
{
	/** @var string */
	private $dir;

	public function getDir(): string
	{
		return $this->dir;
	}

	public function withDir( string $dir ): DirectoryNotFound
	{
		$this->dir = $dir;

		return $this;
	}
}
