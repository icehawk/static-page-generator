<?php declare(strict_types = 1);
/**
 * @author hollodotme
 */

namespace IceHawk\StaticPageGenerator\Renderers;

use IceHawk\StaticPageGenerator\Interfaces\RendersTemplate;

/**
 * Class Twig
 * @package IceHawk\StaticPageGenerator\Renderers
 */
final class Twig implements RendersTemplate
{
	/** @var \Twig_Environment */
	private $twigEnvironment;

	public function __construct( array $searchPaths = [ WORKING_DIR ] )
	{
		$loader                = new \Twig_Loader_Filesystem( $searchPaths );
		$this->twigEnvironment = new \Twig_Environment(
			$loader,
			[
				'debug'      => true,
				'cache'      => sys_get_temp_dir(),
				'autoescape' => 'html',
			]
		);

		$this->twigEnvironment->addExtension( new \Twig_Extension_Debug() );
	}

	public function render( string $template, array $data ) : string
	{
		return $this->twigEnvironment->render( $template, $data );
	}
}
