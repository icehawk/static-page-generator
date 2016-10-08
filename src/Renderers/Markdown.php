<?php declare(strict_types = 1);
/**
 * @author hollodotme
 */

namespace IceHawk\StaticPageGenerator\Renderers;

use IceHawk\StaticPageGenerator\Interfaces\RendersTemplate;

/**
 * Class Markdown
 * @package IceHawk\StaticPageGenerator\Renderers
 */
final class Markdown implements RendersTemplate
{
	/** @var \ParsedownExtra */
	private $parsedown;

	/** @var array */
	private $searchPaths;

	public function __construct( array $searchPaths )
	{
		$this->searchPaths = $searchPaths;
		$this->parsedown   = new \ParsedownExtra();
	}

	public function render( string $template, array $data = [] ) : string
	{
		if ( empty($template) )
		{
			return '';
		}

		foreach ( $this->searchPaths as $searchPath )
		{
			$filePath = rtrim( $searchPath, DIRECTORY_SEPARATOR )
			            . DIRECTORY_SEPARATOR
			            . ltrim( $template, DIRECTORY_SEPARATOR );

			if ( file_exists( $filePath ) )
			{
				return $this->parsedown->text( file_get_contents( $filePath ) );
			}
		}

		return '';
	}
}
