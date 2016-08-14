<?php declare(strict_types = 1);
/**
 * @author hollodotme
 */

namespace IceHawk\StaticPageGenerator\ConsoleCommands;

use IceHawk\StaticPageGenerator\ConsoleCommands\Configs\PagesConfig;
use IceHawk\StaticPageGenerator\Exceptions\ConfigNotFound;
use IceHawk\StaticPageGenerator\Formatters\ByteFormatter;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class GeneratePages
 * @package IceHawk\StaticPageGenerator\ConsoleCommands
 */
final class GeneratePages extends Command
{
	protected function configure()
	{
		$this->setDescription( 'Generates static pages for the given config.' );
		$this->addOption(
			'config', 'c',
			InputOption::VALUE_OPTIONAL,
			'Specifies the config to use',
			WORKING_DIR . DIRECTORY_SEPARATOR . 'Pages.json'
		);
	}

	protected function execute( InputInterface $input, OutputInterface $output )
	{
		$startTime     = microtime( true );
		$logger        = new ConsoleLogger( $output );
		$byteFormatter = new ByteFormatter();
		$configPath    = $input->getOption( 'config' );

		try
		{
			$pagesConfig = $this->loadConfig( $configPath );

			$output->writeln( sprintf( 'Genrating pages for project: %s', $pagesConfig->getProjectName() ) );

			$logger->debug( 'Output dir: {outputDir}', [ 'outputDir' => $pagesConfig->getOutputDir() ] );
			$logger->debug( 'Base URL: {baseUrl}', [ 'baseUrl' => $pagesConfig->getBaseUrl() ] );
		}
		catch ( ConfigNotFound $e )
		{
			$logger->alert( 'Config not found {configPath}', [ 'configPath' => $configPath ] );

			return;
		}
		finally
		{
			$logger->debug(
				'Memory consumption: {memoryPeak} MiB',
				[ 'memoryPeak' => $byteFormatter->format( memory_get_peak_usage( true ) ) ]
			);

			$logger->debug(
				'Time elapsed: {duration} Seconds',
				[ 'duration' => round( (microtime( true ) - $startTime), 6 ) ]
			);

			$output->writeln( 'Done.' );
		}
	}

	private function loadConfig( string $configPath ) : PagesConfig
	{
		if ( !realpath( $configPath ) )
		{
			$configPath = WORKING_DIR . DIRECTORY_SEPARATOR . ltrim( $configPath, DIRECTORY_SEPARATOR );
		}

		if ( file_exists( $configPath ) )
		{
			$configData = json_decode( file_get_contents( $configPath ), true );

			return new PagesConfig( dirname( $configPath ), $configData );
		}

		throw (new ConfigNotFound())->withConfigPath( $configPath );
	}
}
