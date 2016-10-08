<?php declare(strict_types = 1);
/**
 * @author hollodotme
 */

namespace IceHawk\StaticPageGenerator;

use IceHawk\StaticPageGenerator\Constants\Renderer;
use IceHawk\StaticPageGenerator\Exceptions\InvalidRenderer;
use IceHawk\StaticPageGenerator\Interfaces\RendersTemplate;
use IceHawk\StaticPageGenerator\Renderers\Html;
use IceHawk\StaticPageGenerator\Renderers\Markdown;
use IceHawk\StaticPageGenerator\Renderers\Twig;
use Symfony\Component\Console\Application;

/**
 * Class StaticPageGenerator
 * @package IceHawk\StaticPageGenerator
 */
final class StaticPageGenerator extends Application
{
	/** @var array */
	private $instances;

	public function __construct( $name, $version )
	{
		parent::__construct( $name, $version );
		$this->instances = [];
	}

	public function getTemplateRenderer( string $renderer, array $searchPaths ) : RendersTemplate
	{
		switch ( $renderer )
		{
			case Renderer::TWIG:
			{
				return $this->getSharedInstance(
					'renderer::twig',
					function () use ( $searchPaths )
					{
						return new Twig( $searchPaths );
					}
				);
				break;
			}

			case Renderer::HTML:
			{
				return $this->getSharedInstance(
					'renderer::html',
					function () use ( $searchPaths )
					{
						return new Html( $searchPaths );
					}
				);
				break;
			}

			case Renderer::MARKDOWN:
			{
				return $this->getSharedInstance(
					'renderer::markdown',
					function () use ( $searchPaths )
					{
						return new Markdown( $searchPaths );
					}
				);
				break;
			}

			default:
				throw (new InvalidRenderer())->withRenderer( $renderer );
		}
	}

	private function getSharedInstance( string $key, \Closure $createFunction )
	{
		if ( !isset($this->instances[ $key ]) )
		{
			$this->instances[ $key ] = $createFunction->call( $this );
		}

		return $this->instances[ $key ];
	}
}
