<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace IceHawk\StaticPageGenerator\SearchIndex;

/**
 * Class Phrase
 * @package IceHawk\StaticPageGenerator\SearchIndex
 */
final class Phrase
{
	/** @var string */
	private $phrase;

	public function __construct( string $phrase )
	{
		$this->phrase = strip_tags( $phrase );
	}

	public function getWordsUnique() : array
	{
		$cleanString = preg_replace( ["#[^a-z\d]#i", "#\s+#"], ' ', $this->phrase );
		$words       = array_filter(
			explode( ' ', $cleanString ),
			function ( string $word )
			{
				return \strlen( $word ) > 2;
			}
		);

		$words = array_map( 'strtolower', array_unique( $words ) );

		return $words;
	}

	public static function fromFileContents( string $filePath ) : self
	{
		return new self( file_get_contents( $filePath ) );
	}
}
