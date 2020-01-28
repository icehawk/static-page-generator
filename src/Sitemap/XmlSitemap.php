<?php declare(strict_types = 1);
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

namespace IceHawk\StaticPageGenerator\Sitemap;

use DOMDocument;
use DOMElement;
use IceHawk\StaticPageGenerator\ConsoleCommands\Configs\ProjectConfig;

/**
 * Class XmlSitemap
 * @package IceHawk\StaticPageGenerator\Sitemap
 */
final class XmlSitemap
{
	/** @var DOMDocument */
	private $dom;

	/** @var ProjectConfig */
	private $projectConfig;

	public function __construct( ProjectConfig $projectConfig )
	{
		$this->dom               = new DOMDocument( '1.0', 'UTF-8' );
		$this->dom->formatOutput = true;

		$this->projectConfig = $projectConfig;
	}

	public function generate() : string
	{
		$urlSet = $this->getUrlSet();

		/** @noinspection UnusedFunctionResultInspection */
		$this->dom->appendChild( $urlSet );

		return $this->dom->saveXML();
	}

	private function getUrlSet() : DOMElement
	{
		$urlSet = $this->dom->createElementNS( 'http://www.sitemaps.org/schemas/sitemap/0.9', 'urlset' );
		$urlSet->setAttributeNS(
			'http://www.w3.org/2000/xmlns/',
			'xmlns:image',
			'http://www.google.com/schemas/sitemap-image/1.1'
		);
		$urlSet->setAttributeNS(
			'http://www.w3.org/2000/xmlns/',
			'xmlns:video',
			'http://www.google.com/schemas/sitemap-video/1.1'
		);

		foreach ( $this->projectConfig->getAllPages() as $page )
		{
			$urlElement = $this->getUrlElement(
				$this->projectConfig->getUrl( $page->getUri() ),
				$this->projectConfig->getUrl( $page->getImageUrl() ),
				$page->getPageTitle()
			);

			/** @noinspection UnusedFunctionResultInspection */
			$urlSet->appendChild( $urlElement );
		}

		return $urlSet;
	}

	private function getUrlElement( string $url, string $imageUrl, string $imageCaption ) : DOMElement
	{
		$urlElement = $this->dom->createElement( 'url' );
		$homeLoc    = $this->dom->createElement( 'loc', $url );

		$image    = $this->dom->createElement( 'image:image' );
		$imageLoc = $this->dom->createElement( 'image:loc', $imageUrl );
		$imageCap = $this->dom->createElement( 'image:caption', $imageCaption );

		/** @noinspection UnusedFunctionResultInspection */
		$image->appendChild( $imageLoc );
		/** @noinspection UnusedFunctionResultInspection */
		$image->appendChild( $imageCap );

		/** @noinspection UnusedFunctionResultInspection */
		$urlElement->appendChild( $homeLoc );
		/** @noinspection UnusedFunctionResultInspection */
		$urlElement->appendChild( $image );

		return $urlElement;
	}
}
