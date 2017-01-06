<?php declare(strict_types = 1);
/**
 * Copyright (c) 2016-2017 Holger Woltersdorf & Contributors
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
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

	public function __construct( array $searchPaths )
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

	public function render( string $template, array $data = [] ) : string
	{
		return $this->twigEnvironment->render( $template, $data );
	}
}
