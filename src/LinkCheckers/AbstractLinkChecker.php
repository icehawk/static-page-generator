<?php declare(strict_types=1);
/**
 * Copyright (c) 2016-2018 Holger Woltersdorf & Contributors
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
 *
 * @package IceHawk\StaticPageGenerator\LinkCheckers
 */
abstract class AbstractLinkChecker
{
	/** @var string */
	private $outputDir;

	/** @var string */
	private $baseUrl;

	/** @var int */
	private $readTimeout;

	/** @var string */
	private $linkPattern;

	/** @var string */
	private $filePattern;

	/** @var array */
	private $checkedLinks;

	public function __construct( string $outputDir, string $baseUrl, int $readTimeout = Defaults::READ_TIMEOUT )
	{
		$this->outputDir    = $outputDir;
		$this->baseUrl      = $baseUrl;
		$this->readTimeout  = $readTimeout;
		$this->linkPattern  = $this->getLinkPattern();
		$this->filePattern  = $this->getFilePattern();
		$this->checkedLinks = [];
	}

	abstract protected function getLinkPattern(): string;

	abstract protected function getFilePattern(): string;

	public function check( SymfonyStyle $style, array &$failedLinks, array &$skippedLinks ): int
	{
		$baseUrlQuoted   = preg_quote( $this->baseUrl, '#' );
		$outputDirQuoted = preg_quote( $this->outputDir, '#' );
		$links           = $this->collectLinks();
		$totalLinks      = \count( $links );
		$progressBar     = $style->createProgressBar( $totalLinks );

		$progressBar->setFormat( ' %current%/%max% [%bar%] %percent:3s%% | %message%' );
		$progressBar->start();

		foreach ( $links as $filePath => $fileLinks )
		{
			$filename = basename( $filePath );
			$progressBar->setMessage( $filename );

			foreach ( (array)$fileLinks as $link )
			{
				try
				{
					# Convert relative anchor links
					if ( $link{0} === '#' )
					{
						$link = sprintf(
							'%s%s%s',
							$this->baseUrl,
							preg_replace( "#^{$outputDirQuoted}#", '', $filePath ),
							$link
						);
					}

					if ( !preg_match( "#^{$baseUrlQuoted}#", $link ) )
					{
						if ( preg_match( '#^javascript\:#i', $link ) )
						{
							$skippedLinks[] = [$filePath, $link, 'JavaScript'];
							continue;
						}

						if ( preg_match( '#^mailto\:#i', $link ) )
						{
							$skippedLinks[] = [$filePath, $link, 'Mailto'];
							continue;
						}

						# Convert relative URLs
						if ( $link{0} !== '/' && !preg_match( '#^https?\://#i', $link ) )
						{
							$link = sprintf(
								'%s%s/%s',
								$this->baseUrl,
								\dirname( preg_replace( "#^{$outputDirQuoted}#", '', $filePath ) ),
								$link
							);
						}
						# Mark absolute URLs without base URL as failure
						elseif ( $link{0} === '/' )
						{
							$failedLinks[] = [$filePath, $link, 'No base URL'];
							continue;
						}
						# Skip all external links
						else
						{
							$skippedLinks[] = [$filePath, $link, 'External link'];
							continue;
						}
					}

					$response                    = $this->checkedLinks[ $link ] ?? $this->getResponseCode( $link );
					$this->checkedLinks[ $link ] = $response;

					if ( $response !== 200 )
					{
						$failedLinks[] = [$filePath, $link, $response];
					}
				}
				catch ( \Throwable $e )
				{
					$response                    = $e->getMessage();
					$failedLinks[]               = [$filePath, $link, $response];
					$this->checkedLinks[ $link ] = $response;
				}
			}

			/** @noinspection DisconnectedForeachInstructionInspection */
			$progressBar->advance();
		}

		$progressBar->setMessage( 'All links checked.' );
		$progressBar->finish();
		$style->text( '' );

		return empty( $failedLinks ) ? 0 : 1;
	}

	private function collectLinks(): array
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

	private function getResponseCode( string $url ): int
	{
		$ch = curl_init();

		curl_setopt( $ch, CURLOPT_URL, trim( $url ) );
		curl_setopt( $ch, CURLOPT_CUSTOMREQUEST, 'HEAD' );
		curl_setopt( $ch, CURLOPT_HEADER, true );
		curl_setopt( $ch, CURLOPT_NOBODY, true );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT, $this->readTimeout );
		curl_setopt( $ch, CURLOPT_TIMEOUT, $this->readTimeout );
		curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, true );

		curl_exec( $ch );
		$response = curl_getinfo( $ch );

		return $response['http_code'];
	}
}
