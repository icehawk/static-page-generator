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

namespace IceHawk\StaticPageGenerator\ConsoleCommands\Configs;

/**
 * Class PageConfig
 * @package IceHawk\StaticPageGenerator\ConsoleCommands\Configs
 */
final class PageConfig
{
	/** @var string */
	private $uri;

	/** @var array */
	private $configData;

	public function __construct( string $uri, array $configData )
	{
		$this->uri        = $uri;
		$this->configData = $configData;
	}

	public function getUri(): string
	{
		return $this->uri;
	}

	public function getPageLevel() : int
	{
		return (int)$this->getValue( 'pageLevel' );
	}

	private function getValue( $key ) : string
	{
		return (string)($this->configData[ $key ] ?? '');
	}

	public function getPageTitle() : string
	{
		return $this->getValue( 'pageTitle' );
	}

	public function getDescription() : string
	{
		return $this->getValue( 'description' );
	}

	public function getNavName() : string
	{
		return $this->getValue( 'navName' );
	}

	public function getImageUrl() : string
	{
		return $this->getValue( 'imageUrl' );
	}

	public function getTags() : array
	{
		return $this->configData['tags'] ?? [];
	}

	public function getContentType() : string
	{
		return $this->getValue( 'contentType' );
	}

	public function getRenderer() : string
	{
		return $this->getValue( 'renderer' );
	}

	public function getTemplate() : string
	{
		return $this->getValue( 'template' );
	}

	public function getContentFile() : string
	{
		return $this->getValue( 'contentFile' );
	}

	public function getChildren() : array
	{
		return $this->configData['children'] ?? [];
	}

	public function hasChildren() : bool
	{
		return !empty($this->getChildren());
	}
}
