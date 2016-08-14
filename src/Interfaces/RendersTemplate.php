<?php declare(strict_types = 1);
/**
 * @author hollodotme
 */

namespace IceHawk\StaticPageGenerator\Interfaces;

/**
 * Interface RendersTemplate
 * @package IceHawk\StaticPageGenerator\Interfaces
 */
interface RendersTemplate
{
	public function render( string $template, array $data ) : string;
}
