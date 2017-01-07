<?php declare(strict_types = 1);
/**
 * Copyright (c) 2016-2017 Holger Woltersdorf & Contributors
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 */

namespace IceHawk\StaticPageGenerator\LinkCheckers;

use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Class AbstractLinkChecker
 * @package IceHawk\StaticPageGenerator\LinkCheckers
 */
abstract class AbstractLinkChecker
{
	/** @var string */
	private $outputDir;

	/** @var int */
	private $readTimeout;

	/** @var string */
	private $linkPattern;

	/** @var string */
	private $filePattern;

	/** @var array */
	private $checkedLinks;

	public function __construct( string $outputDir, int $readTimeout = Defaults::READ_TIMEOUT )
	{
		$this->outputDir    = $outputDir;
		$this->readTimeout  = $readTimeout;
		$this->linkPattern  = $this->getLinkPattern();
		$this->filePattern  = $this->getFilePattern();
		$this->checkedLinks = [];
	}

	abstract protected function getLinkPattern() : string;

	abstract protected function getFilePattern() : string;

	public function check( SymfonyStyle $style, array &$failedLinks = [] ) : int
	{
		$links       = $this->collectLinks();
		$totalLinks  = count( $links );
		$progressBar = $style->createProgressBar( $totalLinks );
		$progressBar->setFormat( ' %current%/%max% [%bar%] %percent:3s%% | %message%' );

		# Set check method to HEAD
		# Set check timeout
		stream_context_set_default(
			[
				'http' => [
					'method'  => 'HEAD',
					'timeout' => $this->readTimeout,
				],
			]
		);

		$progressBar->start();

		foreach ( $links as $filePath => $fileLinks )
		{
			$filename = basename( $filePath );
			$progressBar->setMessage( $filename );

			foreach ( $fileLinks as $link )
			{
				try
				{
					$response = $this->checkedLinks[ $link ] ?? get_headers( $link, 1 )[0];

					if ( substr( $response, -6 ) != '200 OK' )
					{
						$failedLinks[] = [ $filePath, $link, $response ];
					}
				}
				catch ( \Throwable $e )
				{
					$response      = $e->getMessage();
					$failedLinks[] = [ $filePath, $link, $response ];
				}

				$this->checkedLinks[ $link ] = $response;
			}

			$progressBar->advance();
		}

		$progressBar->setMessage( 'All links checked.' );
		$progressBar->finish();
		$style->text( '' );

		return empty( $failedLinks ) ? 0 : 1;
	}

	private function collectLinks() : array
	{
		$links = [];
		$dir   = new \RecursiveDirectoryIterator(
			$this->outputDir,
			\FilesystemIterator::CURRENT_AS_PATHNAME | \FilesystemIterator::SKIP_DOTS
		);

		$iterator = new \RecursiveIteratorIterator( $dir );

		foreach ( $iterator as $filePath )
		{
			if ( !preg_match( $this->filePattern, $filePath ) )
			{
				continue;
			}

			preg_match_all( $this->linkPattern, file_get_contents( $filePath ), $matches );

			$links[ $filePath ] = $matches[1] ?? [];
		}

		return $links;
	}
}
