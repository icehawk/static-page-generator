<?php declare(strict_types=1);
/**
 * Copyright (c) 2016-2020 Holger Woltersdorf & Contributors
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 */

namespace IceHawk\StaticPageGenerator\Tests\Unit\Configs;

use IceHawk\StaticPageGenerator\ConsoleCommands\Configs\PageConfig;
use IceHawk\StaticPageGenerator\ConsoleCommands\Configs\ProjectConfig;
use IceHawk\StaticPageGenerator\Exceptions\DirectoryNotFound;
use IceHawk\StaticPageGenerator\Exceptions\PageConfigNotFound;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\TestCase;
use function dirname;

/**
 * Class ProjectConfigTest
 * @package IceHawk\StaticPageGenerator\Tests\Unit\Configs
 */
class ProjectConfigTest extends TestCase
{
	/** @var ProjectConfig */
	private $projectConfig;

	public function setUp() : void
	{
		$configDir  = __DIR__ . '/../Fixtures';
		$configFile = $configDir . DIRECTORY_SEPARATOR . 'Project.json';
		$configData = json_decode( file_get_contents( $configFile ), true );

		$this->projectConfig = new ProjectConfig( $configDir, $configData );
	}

	/**
	 * @throws DirectoryNotFound
	 */
	public function testCanGetProjectData() : void
	{
		$outputDirReal   = dirname( __DIR__, 3 ) . '/build/output';
		$contentsDirReal = dirname( __DIR__ ) . '/Fixtures/Contents';

		$this->assertEquals( 'IceHawk', $this->projectConfig->getName() );
		$this->assertEquals( 'https://icehawk.github.io', $this->projectConfig->getBaseUrl() );
		$this->assertEquals( $outputDirReal, $this->projectConfig->getOutputDir() );
		$this->assertEquals( $contentsDirReal, $this->projectConfig->getContentsDir() );
	}

	public function testReplacementsIncludeProjectData() : void
	{
		$expectedReplacements = [
			'@unit@'    => 'test',
			'@baseUrl@' => 'https://icehawk.github.io',
			'@name@'    => 'IceHawk',
		];

		$this->assertEquals( $expectedReplacements, $this->projectConfig->getReplacements() );
	}

	/**
	 * @param int $level
	 * @param int $expectedCount
	 *
	 * @dataProvider pageLevelProvider
	 * @throws Exception
	 */
	public function testCanGetPageConfigsAtLevel( int $level, int $expectedCount ) : void
	{
		$pageConfigs = $this->projectConfig->getPageConfigsAtLevel( $level );

		$count = 0;
		foreach ( $pageConfigs as $pageConfig )
		{
			$this->assertInstanceOf( PageConfig::class, $pageConfig );
			$count++;
		}

		$this->assertEquals( $expectedCount, $count );
	}

	public function pageLevelProvider() : array
	{
		return [
			[1, 1],
			[2, 2],
			[3, 1],
			[4, 3],
		];
	}

	/**
	 * @throws Exception
	 */
	public function testCanGetAllPageConfigs() : void
	{
		$pageConfigs = $this->projectConfig->getPageConfigsByFilter();

		$count = 0;
		foreach ( $pageConfigs as $pageConfig )
		{
			$this->assertInstanceOf( PageConfig::class, $pageConfig );
			$count++;
		}

		$this->assertEquals( 7, $count );
	}

	/**
	 * @throws Exception
	 */
	public function testCanGetChildrenOfAPage() : void
	{
		$pageConfig = new PageConfig(
			'/unit/test',
			[
				'children' => [
					'/docs/icehawk/installation.html',
					'/docs/icehawk/configuration.html',
					'/docs/icehawk/delegation.html',
				],
			]
		);

		$children = $this->projectConfig->getChildrenOf( $pageConfig );

		$count = 0;

		foreach ( $children as $child )
		{
			$this->assertInstanceOf( PageConfig::class, $child );
			$count++;
		}

		$this->assertEquals( 3, $count );
	}

	/**
	 * @throws Exception
	 */
	public function testCanGetPagesGroupedByTags() : void
	{
		$groupedPages = $this->projectConfig->getPageConfigsGroupedByTag();

		$expectedTags = [
			'PHP',
			'CQRS',
			'API',
			'IceHawk',
			'components',
			'applications',
			'pubsub',
			'session',
			'forms',
			'routing',
			'publish',
			'subscribe',
			'documentation',
			'installation',
			'configuration',
			'delegation',
		];

		$this->assertEquals( $expectedTags, array_keys( $groupedPages ) );

		$this->assertCount( 1, $groupedPages['PHP'] );
		$this->assertCount( 2, $groupedPages['CQRS'] );
		$this->assertCount( 1, $groupedPages['API'] );
		$this->assertCount( 7, $groupedPages['IceHawk'] );

		foreach ( $groupedPages as $pageConfigs )
		{
			foreach ( (array)$pageConfigs as $pageConfig )
			{
				$this->assertInstanceOf( PageConfig::class, $pageConfig );
			}
		}
	}

	/**
	 * @param string $uri
	 * @param string $expectedPageTitle
	 *
	 * @dataProvider uriProvider
	 * @throws PageConfigNotFound
	 */
	public function testCanGetPageByUri( string $uri, string $expectedPageTitle ) : void
	{
		$pageConfig = $this->projectConfig->getPageConfigForUri( $uri );

		$this->assertEquals( $expectedPageTitle, $pageConfig->getPageTitle() );
	}

	public function uriProvider() : array
	{
		return [
			['/index.html', 'Home'],
			['/components.html', 'Components'],
			['/docs.html', 'Documentation'],
			['/docs/icehawk.html', 'IceHawk component'],
			['/docs/icehawk/installation.html', 'IceHawk installation'],
			['/docs/icehawk/configuration.html', 'IceHawk configuration'],
			['/docs/icehawk/delegation.html', 'IceHawk delegation'],
		];
	}

	/**
	 * @throws PageConfigNotFound
	 */
	public function testGetNotExistingPageThrowsException() : void
	{
		$this->expectException( PageConfigNotFound::class );

		/** @noinspection UnusedFunctionResultInspection */
		$this->projectConfig->getPageConfigForUri( '/not/existing' );
	}

	/**
	 * @param string $uri
	 * @param array  $expectedBreadCrumb
	 *
	 * @dataProvider breadCrumbProvider
	 * @throws PageConfigNotFound
	 */
	public function testCanGetBreadCrumbForPageConfig( string $uri, array $expectedBreadCrumb ) : void
	{
		$pageConfig = $this->projectConfig->getPageConfigForUri( $uri );

		$breadCrumb = $this->projectConfig->getBreadCrumbFor( $pageConfig );

		$this->assertSame( $expectedBreadCrumb, $breadCrumb );
	}

	public function breadCrumbProvider() : array
	{
		return [
			[
				'/index.html',
				['/index.html' => 'Home'],
			],
			[
				'/components.html',
				[
					'/index.html'      => 'Home',
					'/components.html' => 'Components',
				],
			],
			[
				'/docs.html',
				[
					'/index.html' => 'Home',
					'/docs.html'  => 'Documentation',
				],
			],
			[
				'/docs/icehawk.html',
				[
					'/index.html'        => 'Home',
					'/docs.html'         => 'Documentation',
					'/docs/icehawk.html' => 'IceHawk',
				],
			],
			[
				'/docs/icehawk/installation.html',
				[
					'/index.html'                     => 'Home',
					'/docs.html'                      => 'Documentation',
					'/docs/icehawk.html'              => 'IceHawk',
					'/docs/icehawk/installation.html' => 'Installation',
				],
			],
			[
				'/docs/icehawk/configuration.html',
				[
					'/index.html'                      => 'Home',
					'/docs.html'                       => 'Documentation',
					'/docs/icehawk.html'               => 'IceHawk',
					'/docs/icehawk/configuration.html' => 'Configuration',
				],
			],
			[
				'/docs/icehawk/delegation.html',
				[
					'/index.html'                   => 'Home',
					'/docs.html'                    => 'Documentation',
					'/docs/icehawk.html'            => 'IceHawk',
					'/docs/icehawk/delegation.html' => 'Delegation',
				],
			],
		];
	}
}
