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

use IceHawk\StaticPageGenerator\Exceptions\DirectoryNotFound;
use IceHawk\StaticPageGenerator\Exceptions\PageConfigNotFound;
use IceHawk\StaticPageGenerator\Exceptions\ParentPageConfigNotFound;

/**
 * Class ProjectConfig
 * @package IceHawk\StaticPageGenerator\ConsoleCommands\Configs
 */
final class ProjectConfig
{
	/** @var string */
	private $configFileDir;

	/** @var array */
	private $configData;

	public function __construct( string $configFileDir, array $configData )
	{
		$this->configFileDir = $configFileDir;
		$this->configData    = $configData;
	}

	public function getName() : string
	{
		return $this->getValue( 'name' );
	}

	public function getBaseUrl() : string
	{
		return $this->getValue( 'baseUrl' );
	}

	public function getOutputDir() : string
	{
		$outputDir = $this->getValue( 'outputDir' );

		$outputDirReal = realpath( $this->configFileDir . DIRECTORY_SEPARATOR . $outputDir );

		if ( !$outputDirReal || !is_dir( $outputDirReal ) )
		{
			throw (new DirectoryNotFound())->withDir( $outputDir );
		}

		return $outputDirReal;
	}

	public function getContentsDir() : string
	{
		$contentsDir = $this->getValue( 'contentsDir' );

		$contentsDirReal = realpath( $this->configFileDir . DIRECTORY_SEPARATOR . $contentsDir );

		if ( !$contentsDirReal || !is_dir( $contentsDirReal ) )
		{
			throw (new DirectoryNotFound())->withDir( $contentsDir );
		}

		return $contentsDirReal;
	}

	public function getReplacements() : array
	{
		$replacements = $this->configData['replacements'] ?? [];

		$replacements['@baseUrl@'] = $this->getBaseUrl();
		$replacements['@name@']    = $this->getName();

		return $replacements;
	}

	private function getValue( $key ) : string
	{
		return $this->configData[ $key ] ?? '';
	}

	public function getPageConfigsAtLevel( int $pageLevel ) : \Generator
	{
		yield from $this->getPageConfigsByFilter(
			function ( array $pageConfig ) use ( $pageLevel )
			{
				return (($pageConfig['pageLevel'] ?? -1) == $pageLevel);
			}
		);
	}

	/**
	 * @return \Generator|PageConfig[]
	 */
	public function getAllPages() : \Generator
	{
		yield from $this->getPageConfigsByFilter();
	}

	/**
	 * @param callable $filter
	 *
	 * @return \Generator|PageConfig[]
	 */
	public function getPageConfigsByFilter( callable $filter = null ) : \Generator
	{
		$pagesConfig = $this->configData['pages'] ?? [];

		if ( null !== $filter )
		{
			$pagesConfig = array_filter( $pagesConfig, $filter, ARRAY_FILTER_USE_BOTH );
		}

		foreach ( $pagesConfig as $uri => $configData )
		{
			yield new PageConfig( $uri, $configData );
		}
	}

	/**
	 * @return array|PageConfig[][]
	 */
	public function getPageConfigsGroupedByTag() : array
	{
		$tagReferences = [];

		foreach ( $this->getPageConfigsByFilter() as $pageConfig )
		{
			foreach ( $pageConfig->getTags() as $tag )
			{
				if ( isset( $tagReferences[ $tag ] ) )
				{
					$tagReferences[ $tag ][] = $pageConfig;
				}
				else
				{
					$tagReferences[ $tag ] = [ $pageConfig ];
				}
			}
		}

		return $tagReferences;
	}

	public function getChildrenOf( PageConfig $pageConfig ) : \Generator
	{
		yield from $this->getPageConfigsByFilter(
			function ( array $configData, string $uri ) use ( $pageConfig )
			{
				return in_array( $uri, $pageConfig->getChildren() );
			}
		);
	}

	public function getPageConfigForUri( string $uri ) : PageConfig
	{
		$pageConfigs = $this->getPageConfigsByFilter(
			function ( array $pageConfig, string $configUri ) use ( $uri )
			{
				return ($configUri == $uri);
			}
		);

		$pageConfigs = iterator_to_array( $pageConfigs );

		if ( count( $pageConfigs ) === 1 )
		{
			return $pageConfigs[0];
		}

		throw (new PageConfigNotFound())->withUri( $uri );
	}

	private function getParentOf( PageConfig $pageConfig ) : PageConfig
	{
		$pageUri = $pageConfig->getUri();

		if ( $pageConfig->getPageLevel() == 1 )
		{
			throw (new ParentPageConfigNotFound())->forChildUri( $pageUri );
		}

		$parentLevel = $pageConfig->getPageLevel() - 1;
		$pageConfigs = $this->getPageConfigsByFilter(
			function ( array $configData ) use ( $parentLevel, $pageUri )
			{
				return (($configData['pageLevel'] ?? -1) == $parentLevel)
				       && in_array( $pageUri, $configData['children'] ?? [] );
			}
		);

		$pageConfigs = iterator_to_array( $pageConfigs );

		if ( count( $pageConfigs ) === 1 )
		{
			return $pageConfigs[0];
		}

		throw (new ParentPageConfigNotFound())->forChildUri( $pageUri );
	}

	public function getBreadCrumbFor( PageConfig $pageConfig ) : array
	{
		$breadCrumb = [ $pageConfig->getUri() => $pageConfig->getNavName() ];

		try
		{
			$parentPageConfig = $this->getParentOf( $pageConfig );
		}
		catch ( ParentPageConfigNotFound $e )
		{
			return $breadCrumb;
		}

		$breadCrumb = array_merge( $this->getBreadCrumbFor( $parentPageConfig ), $breadCrumb );

		return $breadCrumb;
	}

	public function getUrl( string $path ) : string
	{
		return $this->getBaseUrl() . $path;
	}
}
