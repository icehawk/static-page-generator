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

namespace IceHawk\StaticPageGenerator\ConsoleCommands;

use IceHawk\StaticPageGenerator\Exceptions\ConfigNotFound;
use IceHawk\StaticPageGenerator\Formatters\ByteFormatter;
use IceHawk\StaticPageGenerator\Sitemap\XmlSitemap;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Class GenerateSitemap
 * @package IceHawk\StaticPageGenerator\ConsoleCommands
 */
final class GenerateSitemap extends AbstractConsoleCommand
{
	/** @var SymfonyStyle */
	private $style;

	protected function configure()
	{
		$this->setDescription( 'Generates static sitemap for the given config.' );
		$this->addOption( 'baseUrl', 'b', InputOption::VALUE_OPTIONAL, 'Overwrites baseUrl setting in Project.json' );
		$this->addArgument(
			'config',
			InputArgument::OPTIONAL,
			'Specifies the project config to use',
			$this->getFullPath( WORKING_DIR, 'Project.json' )
		);
	}

	protected function execute( InputInterface $input, OutputInterface $output )
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

			$xmlSitemap = new XmlSitemap( $projectConfig );
			$xmlString  = $xmlSitemap->generate();

			$this->saveSitemap( $projectConfig->getOutputDir(), 'sitemap.xml', $xmlString );

			$this->style->text( 'Finished.' );
			$this->style->text( '' );
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
						round( (microtime( true ) - $startTime), 6 )
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

	private function saveSitemap( string $outputDir, string $fileName, string $content ) : bool
	{
		$outputFile    = $this->getFullPath( $outputDir, $fileName );
		$outputFileDir = dirname( $outputFile );
		$result        = true;

		if ( !file_exists( $outputFileDir ) )
		{
			$result = mkdir( $outputFileDir, 0777, true );
		}

		if ( $result )
		{
			$result = (bool)file_put_contents( $outputFile, $content );
		}

		return $result;
	}
}
