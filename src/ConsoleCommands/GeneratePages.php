<?php declare(strict_types=1);
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

namespace IceHawk\StaticPageGenerator\ConsoleCommands;

use IceHawk\StaticPageGenerator\ConsoleCommands\Configs\PageConfig;
use IceHawk\StaticPageGenerator\ConsoleCommands\Configs\ProjectConfig;
use IceHawk\StaticPageGenerator\Exceptions\ConfigNotFound;
use IceHawk\StaticPageGenerator\Exceptions\DirectoryNotFound;
use IceHawk\StaticPageGenerator\Exceptions\InvalidRenderer;
use IceHawk\StaticPageGenerator\Formatters\ByteFormatter;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use function count;
use function dirname;

/**
 * Class GeneratePages
 *
 * @package IceHawk\StaticPageGenerator\ConsoleCommands
 */
final class GeneratePages extends AbstractConsoleCommand
{
	/** @var SymfonyStyle */
	private $style;

	protected function configure() : void
	{
		/** @noinspection UnusedFunctionResultInspection */
		$this->setDescription( 'Generates static pages for the given config.' );
		/** @noinspection UnusedFunctionResultInspection */
		$this->addOption( 'baseUrl', 'b', InputOption::VALUE_OPTIONAL, 'Overwrites baseUrl setting in Project.json' );
		/** @noinspection UnusedFunctionResultInspection */
		$this->addArgument(
			'config',
			InputArgument::OPTIONAL,
			'Specifies the project config to use',
			$this->getFullPath( WORKING_DIR, 'Project.json' )
		);
	}

	/**
	 * @param InputInterface  $input
	 * @param OutputInterface $output
	 *
	 * @return int
	 * @throws DirectoryNotFound
	 * @throws RuntimeException
	 */
	protected function execute( InputInterface $input, OutputInterface $output ) : int
	{
		$startTime     = microtime( true );
		$this->style   = new SymfonyStyle( $input, $output );
		$byteFormatter = new ByteFormatter();
		$configPath    = $input->getArgument( 'config' );
		$overwrites    = $this->getOverwrites( $input );

		try
		{
			$projectConfig = $this->loadConfig( $configPath, $overwrites );

			$this->style->title( sprintf( 'Genrating pages for project: %s', $projectConfig->getName() ) );

			$pages = iterator_to_array( $projectConfig->getAllPages() );

			$progressBar = $this->style->createProgressBar( count( $pages ) );
			$progressBar->setFormat( ' %current%/%max% [%bar%] %percent:3s%% | %message%' );
			$progressBar->start();

			/** @var PageConfig $pageConfig */
			foreach ( $pages as $pageConfig )
			{
				$progressBar->setMessage( $pageConfig->getUri() );

				$pageContent = $this->generatePage( $pageConfig, $projectConfig );

				$this->savePage( $projectConfig->getOutputDir(), $pageConfig->getUri(), $pageContent );

				/** @noinspection DisconnectedForeachInstructionInspection */
				$progressBar->advance();
			}

			$progressBar->setMessage( 'All pages generated.' );
			$progressBar->finish();
			$this->style->text( '' );

			return 0;
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

	/**
	 * @param PageConfig    $pageConfig
	 * @param ProjectConfig $projectConfig
	 *
	 * @return string
	 * @throws DirectoryNotFound
	 */
	private function generatePage( PageConfig $pageConfig, ProjectConfig $projectConfig ) : string
	{
		try
		{
			$pageRenderer = $this->getEnv()->getTemplateRenderer(
				$pageConfig->getRenderer(),
				[$projectConfig->getContentsDir()]
			);

			$contentRenderer = $this->getEnv()->getTemplateRenderer(
				$pageConfig->getContentType(),
				[$projectConfig->getContentsDir()]
			);

			$breadCrumb = $projectConfig->getBreadCrumbFor( $pageConfig );
			$content    = $contentRenderer->render( $pageConfig->getContentFile() );

			$data = [
				'project'    => $projectConfig,
				'page'       => $pageConfig,
				'breadCrumb' => $breadCrumb,
				'content'    => $content,
			];

			$pageContent = $pageRenderer->render( $pageConfig->getTemplate(), $data );

			return $this->getContentWithReplacements( $pageContent, $projectConfig );
		}
		catch ( InvalidRenderer $e )
		{
			$this->style->error( 'Invalid renderer set: ' . $e->getRenderer() . ' - skipping' );

			return '';
		}
	}

	private function getContentWithReplacements( string $pageContent, ProjectConfig $projectConfig ) : string
	{
		$replacements = $projectConfig->getReplacements();
		$search       = array_keys( $replacements );
		$replace      = array_values( $replacements );

		return str_replace( $search, $replace, $pageContent );
	}

	/**
	 * @param string $outputDir
	 * @param string $fileName
	 * @param string $content
	 *
	 * @return bool
	 * @throws RuntimeException
	 */
	private function savePage( string $outputDir, string $fileName, string $content ) : bool
	{
		$outputFile    = $this->getFullPath( $outputDir, $fileName );
		$outputFileDir = dirname( $outputFile );

		if ( !@mkdir( $outputFileDir, 0777, true ) && !is_dir( $outputFileDir ) )
		{
			throw new RuntimeException( 'Could not create output directory: ' . $outputFileDir );
		}

		return (bool)file_put_contents( $outputFile, $content );
	}
}
