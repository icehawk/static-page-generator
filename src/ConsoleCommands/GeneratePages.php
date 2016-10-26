<?php declare(strict_types = 1);
/**
 * @author hollodotme
 */

namespace IceHawk\StaticPageGenerator\ConsoleCommands;

use IceHawk\StaticPageGenerator\ConsoleCommands\Configs\PageConfig;
use IceHawk\StaticPageGenerator\ConsoleCommands\Configs\ProjectConfig;
use IceHawk\StaticPageGenerator\Exceptions\ConfigNotFound;
use IceHawk\StaticPageGenerator\Exceptions\InvalidRenderer;
use IceHawk\StaticPageGenerator\Formatters\ByteFormatter;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Class GeneratePages
 * @package IceHawk\StaticPageGenerator\ConsoleCommands
 */
final class GeneratePages extends AbstractConsoleCommand
{
	/** @var SymfonyStyle */
	private $style;

	protected function configure()
	{
		$this->setDescription( 'Generates static pages for the given config.' );
		$this->addOption( 'baseUrl', 'b', InputOption::VALUE_OPTIONAL, 'Overwrites baseUrl setting in Pages.json' );
		$this->addArgument(
			'config',
			InputArgument::OPTIONAL,
			'Specifies the project config to use',
			$this->getFullPath( WORKING_DIR, 'Project.json' )
		);
	}

	private function getFullPath( string $dir, string $file ) : string
	{
		return rtrim( $dir, DIRECTORY_SEPARATOR ) . DIRECTORY_SEPARATOR . ltrim( $file, DIRECTORY_SEPARATOR );
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

				$progressBar->advance();
			}

			$progressBar->setMessage( 'All pages generated.' );
			$progressBar->finish();
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

	private function loadConfig( string $configPath, array $overwrites ) : ProjectConfig
	{
		if ( $configPath[0] !== '/' )
		{
			$configPath = $this->getFullPath( WORKING_DIR, $configPath );
		}

		if ( !realpath( $configPath ) )
		{
			throw (new ConfigNotFound())->withConfigPath( $configPath );
		}

		$configData = json_decode( file_get_contents( $configPath ), true );
		$configData = array_merge( $configData, $overwrites );

		return new ProjectConfig( dirname( $configPath ), $configData );
	}

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

		$contentWithReplacements = str_replace( $search, $replace, $pageContent );

		return $contentWithReplacements;
	}

	private function savePage( string $outputDir, string $fileName, string $content ) : bool
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
