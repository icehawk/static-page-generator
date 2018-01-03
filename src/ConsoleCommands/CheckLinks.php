<?php declare(strict_types = 1);
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

namespace IceHawk\StaticPageGenerator\ConsoleCommands;

use IceHawk\StaticPageGenerator\ConsoleCommands\Configs\ProjectConfig;
use IceHawk\StaticPageGenerator\Exceptions\ConfigNotFound;
use IceHawk\StaticPageGenerator\Formatters\ByteFormatter;
use IceHawk\StaticPageGenerator\LinkCheckers\HtmlLinkChecker;
use IceHawk\StaticPageGenerator\LinkCheckers\XmlSitemapLinkChecker;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Class CheckLinks
 * @package IceHawk\StaticPageGenerator\ConsoleCommands
 */
final class CheckLinks extends AbstractConsoleCommand
{
	/** @var SymfonyStyle */
	private $style;

	protected function configure()
	{
		$this->setDescription( 'Checks links in generated output.' );
		$this->addOption( 'baseUrl', 'b', InputOption::VALUE_OPTIONAL, 'Overwrites baseUrl setting in Project.json' );
		$this->addOption( 'generate', 'g', InputOption::VALUE_NONE, 'Generate output before checking links.' );
		$this->addOption(
			'timeout',
			't',
			InputOption::VALUE_OPTIONAL,
			'Defines the timeout in seconds to wait for each link.',
			5
		);
		$this->addArgument(
			'config',
			InputArgument::OPTIONAL,
			'Specifies the project config to use',
			$this->getFullPath( WORKING_DIR, 'Project.json' )
		);
	}

	protected function execute( InputInterface $input, OutputInterface $output )
	{
		$generate      = $input->getOption( 'generate' );
		$baseUrl       = $input->getOption( 'baseUrl' );
		$readTimeout   = $input->getOption( 'timeout' );
		$configPath    = $input->getArgument( 'config' );
		$this->style   = new SymfonyStyle( $input, $output );
		$byteFormatter = new ByteFormatter();
		$startTime     = microtime( true );

		try
		{
			$projectConfig = $this->loadConfig( $configPath, $this->getOverwrites( $input ) );

			if ( $generate )
			{
				$this->style->text( 'Pre-Generation is enabled...' );

				$generateInput = new ArrayInput(
					[
						'config'    => $configPath,
						'--baseUrl' => $baseUrl,
					]
				);

				$generateCommands = $this->getEnv()->all( 'generate' );

				/** @var AbstractConsoleCommand $command */
				foreach ( $generateCommands as $command )
				{
					$command->run( clone $generateInput, $output );
				}
			}

			$this->style->title( sprintf( 'Checking links for project: %s', $projectConfig->getName() ) );

			$htmlCheckResult    = $this->checkHtmlLinks( $projectConfig, (int)$readTimeout );
			$sitemapCheckResult = $this->checkXmlSitemapLinks( $projectConfig, (int)$readTimeout );

			return ($htmlCheckResult + $sitemapCheckResult);
		}
		catch ( ConfigNotFound $e )
		{
			$this->style->error( sprintf( 'Config not found %s', $configPath ) );

			return 1;
		}
		finally
		{
			$this->style->block(
				[
					sprintf(
						'Memory consumption: %s',
						$byteFormatter->format( memory_get_peak_usage( true ) )
					),
					sprintf(
						'Time elapsed: %f Seconds',
						round( microtime( true ) - $startTime, 6 )
					),
				]
			);

			$this->style->text( '<fg=green>√ Done</>' );
		}
	}

	private function getOverwrites( InputInterface $input ) : array
	{
		$overwrites = [];

		$overwrites['baseUrl'] = $input->getOption( 'baseUrl' );

		return array_filter( $overwrites );
	}

	private function checkHtmlLinks( ProjectConfig $projectConfig, int $readTimeout ) : int
	{
		$linkChecker = new HtmlLinkChecker(
			$projectConfig->getOutputDir(),
			$projectConfig->getBaseUrl(),
			$readTimeout
		);

		$failedLinks  = [];
		$skippedLinks = [];

		$result = $linkChecker->check( $this->style, $failedLinks, $skippedLinks );

		if ( !empty( $failedLinks ) )
		{
			$this->style->text( '' );
			$this->style->text( '<fg=red>!! Some HTML links seem to be broken:</>' );
			$headers = [ 'Filepath', 'Link', 'Response' ];
			$this->style->table( $headers, $failedLinks );
		}
		else
		{
			$this->style->text( '' );
			$this->style->text( '<fg=green>√ All HTML links OK</>' );
			$this->style->text( '' );
		}

		if ( !empty( $skippedLinks ) )
		{
			if ( $this->style->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE )
			{
				$this->style->text( '' );
				$this->style->text( '<fg=yellow>Some HTML links have been skipped:</>' );
				$headers = [ 'Filepath', 'Link', 'Reason' ];
				$this->style->table( $headers, $skippedLinks );
				$this->style->text( '' );
			}
			else
			{
				$skippedLinksCount = count( $skippedLinks );
				$this->style->text( '' );
				$this->style->text( "<fg=yellow>{$skippedLinksCount} HTML links have been skipped.</>" );
				$this->style->text( '' );
			}
		}

		return $result;
	}

	private function checkXmlSitemapLinks( ProjectConfig $projectConfig, int $readTimeout ) : int
	{
		$linkChecker = new XmlSitemapLinkChecker(
			$projectConfig->getOutputDir(),
			$projectConfig->getBaseUrl(),
			$readTimeout
		);

		$failedLinks  = [];
		$skippedLinks = [];

		$result = $linkChecker->check( $this->style, $failedLinks, $skippedLinks );

		if ( !empty( $failedLinks ) )
		{
			$this->style->text( '' );
			$this->style->text( '<fg=red>!! Some Sitemap links seem to be broken:</>' );
			$headers = [ 'Filepath', 'Link', 'Response' ];
			$this->style->table( $headers, $failedLinks );
		}
		else
		{
			$this->style->text( '' );
			$this->style->text( '<fg=green>√ All Sitemap links OK</>' );
			$this->style->text( '' );
		}

		if ( !empty( $skippedLinks ) )
		{
			if ( $this->style->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE )
			{
				$this->style->text( '' );
				$this->style->text( '<fg=yellow>Some Sitemap links have been skipped:</>' );
				$headers = [ 'Filepath', 'Link', 'Reason' ];
				$this->style->table( $headers, $skippedLinks );
				$this->style->text( '' );
			}
			else
			{
				$skippedLinksCount = count( $skippedLinks );
				$this->style->text( '' );
				$this->style->text( "<fg=yellow>{$skippedLinksCount} Sitemap links have been skipped.</>" );
				$this->style->text( '' );
			}
		}

		return $result;
	}
}
