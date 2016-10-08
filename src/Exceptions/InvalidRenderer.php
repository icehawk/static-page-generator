<?php declare(strict_types = 1);
/**
 * @author hollodotme
 */

namespace IceHawk\StaticPageGenerator\Exceptions;

/**
 * Class InvalidRenderer
 * @package IceHawk\StaticPageGenerator\Exceptions
 */
final class InvalidRenderer extends StaticPageGeneratorException
{
	/** @var string */
	private $renderer;

	public function getRenderer(): string
	{
		return $this->renderer;
	}

	public function withRenderer( string $renderer ): InvalidRenderer
	{
		$this->renderer = $renderer;

		return $this;
	}
}
