<?php declare(strict_types = 1);
/**
 * @author hollodotme
 */

namespace IceHawk\StaticPageGenerator\LinkCheckers;

/**
 * Class HtmlLinkChecker
 * @package IceHawk\StaticPageGenerator\LinkCheckers
 */
final class HtmlLinkChecker extends AbstractLinkChecker
{
	protected function getLinkPattern() : string
	{
		return "#\<a.*\bhref=\"([^\"]+)\".*\>#i";
	}

	protected function getFilePattern() : string
	{
		return "#\.html$#i";
	}
}
