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
 * Class Html
 * @package IceHawk\StaticPageGenerator\Renderers
 */
final class Html implements RendersTemplate
{
	/** @var array */
	private $searchPaths;

	public function __construct( array $searchPaths )
	{
		$this->searchPaths = $searchPaths;
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
				return file_get_contents( $template );
			}
		}

		return '';
	}
}
