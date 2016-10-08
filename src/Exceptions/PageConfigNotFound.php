<?php declare(strict_types = 1);
/**
 * @author hollodotme
 */

namespace IceHawk\StaticPageGenerator\Exceptions;

/**
 * Class PageConfigNotFound
 * @package IceHawk\StaticPageGenerator\Exceptions
 */
final class PageConfigNotFound extends StaticPageGeneratorException
{
	/** @var string */
	private $uri;

	public function getUri(): string
	{
		return $this->uri;
	}

	public function withUri( string $uri ): PageConfigNotFound
	{
		$this->uri = $uri;

		return $this;
	}
}
