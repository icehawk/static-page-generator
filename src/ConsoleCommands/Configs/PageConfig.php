<?php
/**
 * @author hollodotme
 */

namespace IceHawk\StaticPageGenerator\ConsoleCommands\Configs;

/**
 * Class PageConfig
 * @package IceHawk\StaticPageGenerator\ConsoleCommands\Configs
 */
final class PageConfig
{
	/** @var string */
	private $path;

	/** @var array */
	private $configData;

	public function __construct( string $path, array $configData )
	{
		$this->path       = $path;
		$this->configData = $configData;
	}

	public function getPath(): string
	{
		return $this->path;
	}

	public function getPageTitle() : string
	{
		return $this->getValue( 'pageTitle' );
	}

	private function getValue( $key ) : string
	{
		return $this->configData[ $key ] ?? '';
	}

	public function getMetaDescription() : string
	{
		return $this->getValue( 'metaDescription' );
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
		return $this->configData['tags'] ?? [ ];
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
}
