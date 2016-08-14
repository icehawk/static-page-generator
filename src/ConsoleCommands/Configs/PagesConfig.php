<?php declare(strict_types = 1);
/**
 * @author hollodotme
 */

namespace IceHawk\StaticPageGenerator\ConsoleCommands\Configs;

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

		if ( !realpath( $outputDir ) )
		{
			$outputDir = $this->configFileDir . DIRECTORY_SEPARATOR . ltrim( $outputDir, DIRECTORY_SEPARATOR );
		}

		return $outputDir;
	}

	private function getValue( $key ) : string
	{
		return $this->configData[ $key ] ?? '';
	}

	/**
	 * @return \Generator|PageConfig[]
	 */
	public function getPageConfigs() : \Generator
	{
		foreach ( $this->configData['pages'] ?? [ ] as $path => $configData )
		{
			yield new PageConfig( $path, $configData );
		}
	}

	/**
	 * @return array|PageConfig[][]
	 */
	public function getPageConfigsGroupedByTag() : array
	{
		$tagReferneces = [ ];

		foreach ( $this->getPageConfigs() as $pageConfig )
		{
			foreach ( $pageConfig->getTags() as $tag )
			{
				if ( isset($tagReferneces[ $tag ]) )
				{
					$tagReferneces[ $tag ][] = $pageConfig;
				}
				else
				{
					$tagReferneces[ $tag ] = [ $pageConfig ];
				}
			}
		}

		return $tagReferneces;
	}
}
