<?php declare(strict_types = 1);
/**
 * @author hollodotme
 */

namespace IceHawk\StaticPageGenerator\Formatters;

/**
 * Class ByteFormatter
 * @package IceHawk\StaticPageGenerator\Formatters
 */
final class ByteFormatter
{
	public function format( int $bytes )
	{
		$unit = [ 'b', 'kb', 'mb', 'gb', 'tb', 'pb' ];
		$i    = (int)floor( log( $bytes, 1024 ) );

		return round( $bytes / pow( 1024, $i ), 2 ) . ' ' . $unit[ $i ];
	}
}
