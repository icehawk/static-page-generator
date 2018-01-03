<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace IceHawk\StaticPageGenerator\SearchIndex;

use IceHawk\StaticPageGenerator\ConsoleCommands\Configs\PageConfig;
use IceHawk\StaticPageGenerator\ConsoleCommands\Configs\ProjectConfig;

/**
 * Class WordIndex
 * @package IceHawk\StaticPageGenerator\SearchIndex
 */
final class WordIndex implements \JsonSerializable
{
	/** @var array */
	private $wordUrlMap;

	/** @var ProjectConfig */
	private $projectConfig;

	public function __construct( ProjectConfig $projectConfig )
	{
		$this->wordUrlMap    = [];
		$this->projectConfig = $projectConfig;
	}

	public function addPhrase( Phrase $phrase, PageConfig $pageConfig ) : void
	{
		$wordsUnique = $phrase->getWordsUnique();
		if ( 0 === \count( $wordsUnique ) )
		{
			return;
		}

		$pageUrl = $this->projectConfig->getUrl( $pageConfig->getUri() );

		$pageData = [
			'title'      => $pageConfig->getPageTitle(),
			'breadcrumb' => $this->projectConfig->getBreadCrumbFor( $pageConfig ),
		];

		foreach ( $wordsUnique as $word )
		{
			if ( !isset( $this->wordUrlMap[ $word ] ) )
			{
				$this->wordUrlMap[ $word ] = [$pageUrl => $pageData];
				continue;
			}

			if ( !isset( $this->wordUrlMap[ $word ][ $pageUrl ] ) )
			{
				$this->wordUrlMap[ $word ][ $pageUrl ] = $pageData;
			}
		}
	}

	public function jsonSerialize()
	{
		return $this->wordUrlMap;
	}
}
