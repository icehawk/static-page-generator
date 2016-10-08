<?php declare(strict_types = 1);
/**
 * @author hollodotme
 */

namespace IceHawk\StaticPageGenerator\Exceptions;

/**
 * Class ParentPageConfigNotFound
 * @package IceHawk\StaticPageGenerator\Exceptions
 */
final class ParentPageConfigNotFound extends StaticPageGeneratorException
{
	/** @var string */
	private $childUri;

	public function getChildUri(): string
	{
		return $this->childUri;
	}

	public function forChildUri( string $childUri ): ParentPageConfigNotFound
	{
		$this->childUri = $childUri;

		return $this;
	}
}
