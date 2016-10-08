<?php declare(strict_types = 1);
/**
 * @author hollodotme
 */

namespace IceHawk\StaticPageGenerator\Tests\Unit\Configs;

use IceHawk\StaticPageGenerator\ConsoleCommands\Configs\PageConfig;
use IceHawk\StaticPageGenerator\ConsoleCommands\Configs\PagesConfig;

/**
 * Class PagesConfigTest
 * @package IceHawk\StaticPageGenerator\Tests\Unit\Configs
 */
class PagesConfigTest extends \PHPUnit_Framework_TestCase
{
	/** @var PagesConfig */
	private $pagesConfig;

	public function setUp()
	{
		$configDir  = __DIR__ . '/../Fixtures';
		$configFile = $configDir . DIRECTORY_SEPARATOR . 'Pages.json';
		$configData = json_decode( file_get_contents( $configFile ), true );

		$this->pagesConfig = new PagesConfig( $configDir, $configData );
	}

	public function testCanGetProjectData()
	{
		$outputDirReal   = realpath( __DIR__ . '/../../../build/output' );
		$contentsDirReal = realpath( __DIR__ . '/../Fixtures/Contents' );

		$this->assertEquals( 'IceHawk - PHP micro-framework respecting CQRS', $this->pagesConfig->getProjectName() );
		$this->assertEquals( 'https://icehawk.github.io', $this->pagesConfig->getBaseUrl() );
		$this->assertEquals( $outputDirReal, $this->pagesConfig->getOutputDir() );
		$this->assertEquals( $contentsDirReal, $this->pagesConfig->getContentsDir() );
	}

	/**
	 * @param int $level
	 * @param int $expectedCount
	 *
	 * @dataProvider pageLevelProvider
	 */
	public function testCanGetPageConfigsAtLevel( int $level, int $expectedCount )
	{
		$pageConfigs = $this->pagesConfig->getPageConfigsAtLevel( $level );

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
			[1, 1], [2, 2], [3, 1], [4, 3],
		];
	}

	public function testCanGetAllPageConfigs()
	{
		$pageConfigs = $this->pagesConfig->getPageConfigsByFilter();

		$count = 0;
		foreach ( $pageConfigs as $pageConfig )
		{
			$this->assertInstanceOf( PageConfig::class, $pageConfig );
			$count++;
		}

		$this->assertEquals( 7, $count );
	}

	public function testCanGetChildrenOfAPage()
	{
		$pageConfig = new PageConfig(
			'/unit/test',
			[
				'children' => [
					"/docs/icehawk/installation.html",
					"/docs/icehawk/configuration.html",
					"/docs/icehawk/delegation.html",
				],
			]
		);

		$children = $this->pagesConfig->getChildrenOf( $pageConfig );

		$count = 0;

		foreach ( $children as $child )
		{
			$this->assertInstanceOf( PageConfig::class, $child );
			$count++;
		}

		$this->assertEquals( 3, $count );
	}

	public function testCanGetPagesGroupedByTags()
	{
		$groupedPages = $this->pagesConfig->getPageConfigsGroupedByTag();

		$expectedTags = [
			'PHP', 'CQRS', 'API', 'IceHawk', 'components', 'applications', 'pubsub', 'session', 'forms', 'routing',
			'publish', 'subscribe', 'documentation', 'installation', 'configuration', 'delegation',
		];

		$this->assertEquals( $expectedTags, array_keys( $groupedPages ) );

		$this->assertEquals( 1, count( $groupedPages['PHP'] ) );
		$this->assertEquals( 2, count( $groupedPages['CQRS'] ) );
		$this->assertEquals( 1, count( $groupedPages['API'] ) );
		$this->assertEquals( 7, count( $groupedPages['IceHawk'] ) );

		foreach ( $groupedPages as $pageConfigs )
		{
			foreach ( $pageConfigs as $pageConfig )
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
	 */
	public function testCanGetPageByUri( string $uri, string $expectedPageTitle )
	{
		$pageConfig = $this->pagesConfig->getPageConfigForUri( $uri );

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
	 * @expectedException \IceHawk\StaticPageGenerator\Exceptions\PageConfigNotFound
	 */
	public function testGetNotExistingPageThrowsException()
	{
		$this->pagesConfig->getPageConfigForUri( '/not/existing' );
	}

	/**
	 * @param string $uri
	 * @param array  $expectedBreadCrumb
	 *
	 * @dataProvider breadCrumbProvider
	 */
	public function testCanGetBreadCrumbForPageConfig( string $uri, array $expectedBreadCrumb )
	{
		$pageConfig = $this->pagesConfig->getPageConfigForUri( $uri );

		$breadCrumb = $this->pagesConfig->getBreadCrumbFor( $pageConfig );

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
					'/docs/icehawk.html' => 'IceHawk component',
				],
			],
			[
				'/docs/icehawk/installation.html',
				[
					'/index.html'                     => 'Home',
					'/docs.html'                      => 'Documentation',
					'/docs/icehawk.html'              => 'IceHawk component',
					'/docs/icehawk/installation.html' => 'IceHawk installation',
				],
			],
			[
				'/docs/icehawk/configuration.html',
				[
					'/index.html'                      => 'Home',
					'/docs.html'                       => 'Documentation',
					'/docs/icehawk.html'               => 'IceHawk component',
					'/docs/icehawk/configuration.html' => 'IceHawk configuration',
				],
			],
			[
				'/docs/icehawk/delegation.html',
				[
					'/index.html'                   => 'Home',
					'/docs.html'                    => 'Documentation',
					'/docs/icehawk.html'            => 'IceHawk component',
					'/docs/icehawk/delegation.html' => 'IceHawk delegation',
				],
			],
		];
	}
}
