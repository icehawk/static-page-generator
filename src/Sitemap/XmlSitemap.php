<?php declare(strict_types = 1);
/**
 * @author hollodotme
 */

namespace IceHawk\StaticPageGenerator\Sitemap;

use IceHawk\StaticPageGenerator\ConsoleCommands\Configs\ProjectConfig;

/**
 * Class XmlSitemap
 * @package IceHawk\StaticPageGenerator\Sitemap
 */
final class XmlSitemap
{
	/** @var \DOMDocument */
	private $dom;

	/** @var ProjectConfig */
	private $projectConfig;

	public function __construct( ProjectConfig $projectConfig )
	{
		$this->dom               = new \DOMDocument( '1.0', 'UTF-8' );
		$this->dom->formatOutput = true;

		$this->projectConfig = $projectConfig;
	}

	public function generate() : string
	{
		$urlSet = $this->getUrlSet();

		$this->dom->appendChild( $urlSet );

		return $this->dom->saveXML();
	}

	private function getUrlSet() : \DOMElement
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

		$allPages = $this->projectConfig->getAllPages();

		foreach ( $allPages as $page )
		{
			$urlElement = $this->getUrlElement(
				$this->projectConfig->getUrl( $page->getUri() ),
				$this->projectConfig->getUrl( $page->getImageUrl() ),
				$page->getPageTitle()
			);

			$urlSet->appendChild( $urlElement );
		}

		return $urlSet;
	}

	private function getUrlElement( string $url, string $imageUrl, string $imageCaption ) : \DOMElement
	{
		$urlElement = $this->dom->createElement( 'url' );
		$homeLoc    = $this->dom->createElement( 'loc', $url );

		$image    = $this->dom->createElement( 'image:image' );
		$imageLoc = $this->dom->createElement( 'image:loc', $imageUrl );
		$imageCap = $this->dom->createElement( 'image:caption', $imageCaption );

		$image->appendChild( $imageLoc );
		$image->appendChild( $imageCap );

		$urlElement->appendChild( $homeLoc );
		$urlElement->appendChild( $image );

		return $urlElement;
	}
}
