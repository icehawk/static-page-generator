<?php declare(strict_types = 1);
/**
 * @author hollodotme
 */

namespace IceHawk\StaticPageGenerator\Constants;

/**
 * Class Renderer
 * @package IceHawk\StaticPageGenerator\Constants
 */
abstract class Renderer
{
	const TWIG     = 'twig';

	const HTML     = 'html';

	const MARKDOWN = 'markdown';

	const ALL      = [
		self::TWIG, self::HTML, self::MARKDOWN,
	];
}
