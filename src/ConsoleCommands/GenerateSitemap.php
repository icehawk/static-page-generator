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

use IceHawk\StaticPageGenerator\Exceptions\ConfigNotFound;
use IceHawk\StaticPageGenerator\Exceptions\DirectoryNotFound;
use IceHawk\StaticPageGenerator\Formatters\ByteFormatter;
use IceHawk\StaticPageGenerator\Sitemap\XmlSitemap;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use function dirname;

/**
 * Class GenerateSitemap
 *
 * @package IceHawk\StaticPageGenerator\ConsoleCommands
 */
final class GenerateSitemap extends AbstractConsoleCommand
{
	/** @var SymfonyStyle */
	private $style;

	protected function configure() : void
	{
		/** @noinspection UnusedFunctionResultInspection */
		$this->setDescription( 'Generates static sitemap for the given config.' );
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

			$this->style->title( sprintf( 'Genrating XML sitemap for project: %s', $projectConfig->getName() ) );

			$xmlString = (new XmlSitemap( $projectConfig ))->generate();

			$this->saveSitemap( $projectConfig->getOutputDir(), 'sitemap.xml', $xmlString );

			$this->style->text( 'Finished.' );
			$this->style->text( '' );
		}
		catch ( ConfigNotFound $e )
		{
			$this->style->error( sprintf( 'Config not found %s', $configPath ) );

			return 1;
		}
		catch ( RuntimeException $e )
		{
			$this->style->error( $e->getMessage() );

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

		return 0;
	}

	private function getOverwrites( InputInterface $input ) : array
	{
		$overwrites = [];

		$overwrites['baseUrl'] = $input->getOption( 'baseUrl' );

		return array_filter( $overwrites );
	}

	/**
	 * @param string $outputDir
	 * @param string $fileName
	 * @param string $content
	 *
	 * @return bool
	 * @throws RuntimeException
	 */
	private function saveSitemap( string $outputDir, string $fileName, string $content ) : bool
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
