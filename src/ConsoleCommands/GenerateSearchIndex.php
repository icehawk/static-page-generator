<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace IceHawk\StaticPageGenerator\ConsoleCommands;

use IceHawk\StaticPageGenerator\ConsoleCommands\Configs\PageConfig;
use IceHawk\StaticPageGenerator\Exceptions\ConfigNotFound;
use IceHawk\StaticPageGenerator\Formatters\ByteFormatter;
use IceHawk\StaticPageGenerator\SearchIndex\Phrase;
use IceHawk\StaticPageGenerator\SearchIndex\WordIndex;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Class GenerateSearchIndex
 * @package IceHawk\StaticPageGenerator\ConsoleCommands
 */
final class GenerateSearchIndex extends AbstractConsoleCommand
{
	private const SEARCH_INDEX_FILE_NAME = 'search-index.json';

	/** @var SymfonyStyle */
	private $style;

	protected function configure() : void
	{
		$this->setDescription( 'Generates a static search index JSON file for onpage search' );
		$this->addOption( 'baseUrl', 'b', InputOption::VALUE_OPTIONAL, 'Overwrites baseUrl setting in Project.json' );
		$this->addArgument(
			'config',
			InputArgument::OPTIONAL,
			'Specifies the project config to use',
			$this->getFullPath( WORKING_DIR, 'Project.json' )
		);
	}

	/**
	 * @param \Symfony\Component\Console\Input\InputInterface   $input
	 * @param \Symfony\Component\Console\Output\OutputInterface $output
	 *
	 * @return int
	 * @throws \IceHawk\StaticPageGenerator\Exceptions\DirectoryNotFound
	 * @throws \Symfony\Component\Console\Exception\RuntimeException
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

			$this->style->title( sprintf( 'Genrating search index for project: %s', $projectConfig->getName() ) );

			$pages = iterator_to_array( $projectConfig->getAllPages() );

			$wordIndex = new WordIndex( $projectConfig );

			$progressBar = $this->style->createProgressBar( \count( $pages ) );
			$progressBar->setFormat( ' %current%/%max% [%bar%] %percent:3s%% | %message%' );
			$progressBar->start();

			/** @var PageConfig $pageConfig */
			foreach ( $pages as $pageConfig )
			{
				if ( '' === $pageConfig->getContentFile() )
				{
					continue;
				}

				$contentFilePath = $this->getFullPath( $projectConfig->getContentsDir(), $pageConfig->getContentFile() );

				$progressBar->setMessage( $pageConfig->getUri() );

				$phrase = Phrase::fromFileContents( $contentFilePath );
				$wordIndex->addPhrase( $phrase, $pageConfig );

				/** @noinspection DisconnectedForeachInstructionInspection */
				$progressBar->advance();
			}

			$progressBar->setMessage( 'All pages indexed.' );
			$progressBar->finish();

			$this->saveIndexFile( $wordIndex, $projectConfig->getOutputDir() );

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
	 * @param \IceHawk\StaticPageGenerator\SearchIndex\WordIndex $wordIndex
	 * @param string                                             $outputDir
	 *
	 * @return bool
	 * @throws \Symfony\Component\Console\Exception\RuntimeException
	 */
	private function saveIndexFile( WordIndex $wordIndex, string $outputDir ) : bool
	{
		$outputFile    = $this->getFullPath( $outputDir, self::SEARCH_INDEX_FILE_NAME );
		$outputFileDir = \dirname( $outputFile );

		if ( !@mkdir( $outputFileDir, 0777, true ) && !is_dir( $outputFileDir ) )
		{
			throw new RuntimeException( 'Could not create output directory: ' . $outputFileDir );
		}

		return (bool)file_put_contents( $outputFile, json_encode( $wordIndex, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT ) );
	}
}
