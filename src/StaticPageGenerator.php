<?php declare(strict_types = 1);
/**
 * Copyright (c) 2016-2018 Holger Woltersdorf & Contributors
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
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

	/**
	 * @param string $renderer
	 * @param array  $searchPaths
	 *
	 * @return \IceHawk\StaticPageGenerator\Interfaces\RendersTemplate
	 * @throws \IceHawk\StaticPageGenerator\Exceptions\InvalidRenderer
	 */
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
