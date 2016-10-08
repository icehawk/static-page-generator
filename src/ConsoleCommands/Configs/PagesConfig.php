<?php declare(strict_types = 1);
/**
 * @author hollodotme
 */

namespace IceHawk\StaticPageGenerator\ConsoleCommands\Configs;

use IceHawk\StaticPageGenerator\Exceptions\DirectoryNotFound;
use IceHawk\StaticPageGenerator\Exceptions\PageConfigNotFound;
use IceHawk\StaticPageGenerator\Exceptions\ParentPageConfigNotFound;

/**
 * Class PagesConfig
 * @package IceHawk\StaticPageGenerator\ConsoleCommands\Configs
 */
final class PagesConfig
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

	public function getProjectName() : string
	{
		return $this->getValue( 'projectName' );
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
				if ( isset($tagReferences[ $tag ]) )
				{
					$tagReferences[ $tag ][] = $pageConfig;
				}
				else
				{
					$tagReferences[ $tag ] = [$pageConfig];
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
		$breadCrumb = [$pageConfig->getUri() => $pageConfig->getPageTitle()];

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
}
