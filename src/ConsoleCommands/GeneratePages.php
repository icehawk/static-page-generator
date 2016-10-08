<?php declare(strict_types = 1);
/**
 * @author hollodotme
 */

namespace IceHawk\StaticPageGenerator\ConsoleCommands;

use IceHawk\StaticPageGenerator\ConsoleCommands\Configs\PageConfig;
use IceHawk\StaticPageGenerator\ConsoleCommands\Configs\PagesConfig;
use IceHawk\StaticPageGenerator\Exceptions\ConfigNotFound;
use IceHawk\StaticPageGenerator\Exceptions\InvalidRenderer;
use IceHawk\StaticPageGenerator\Formatters\ByteFormatter;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
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
		$this->addArgument(
			'config',
			InputArgument::OPTIONAL,
			'Specifies the config to use',
			$this->getFullPath( WORKING_DIR, 'Pages.json' )
		);
	}

	protected function execute( InputInterface $input, OutputInterface $output )
	{
		$startTime     = microtime( true );
		$this->style   = new SymfonyStyle( $input, $output );
		$byteFormatter = new ByteFormatter();
		$configPath    = $input->getArgument( 'config' );

		try
		{
			$pagesConfig = $this->loadConfig( $configPath );

			$this->style->title( sprintf( 'Genrating pages for project: %s', $pagesConfig->getProjectName() ) );

			$pages = iterator_to_array( $pagesConfig->getAllPages() );

			$progressBar = $this->style->createProgressBar( count( $pages ) );
			$progressBar->setFormat( ' %current%/%max% [%bar%] %percent:3s%% | %message%' );
			$progressBar->start();

			/** @var PageConfig $pageConfig */
			foreach ( $pages as $pageConfig )
			{
				$progressBar->setMessage( $pageConfig->getUri() );

				$pageContent = $this->generatePage( $pageConfig, $pagesConfig );

				$this->savePage( $pagesConfig->getOutputDir(), $pageConfig->getUri(), $pageContent );

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

	private function loadConfig( string $configPath ) : PagesConfig
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

		return new PagesConfig( dirname( $configPath ), $configData );
	}

	private function generatePage( PageConfig $pageConfig, PagesConfig $pagesConfig ) : string
	{
		try
		{
			$pageRenderer = $this->getEnv()->getTemplateRenderer(
				$pageConfig->getRenderer(),
				[$pagesConfig->getContentsDir()]
			);

			$contentRenderer = $this->getEnv()->getTemplateRenderer(
				$pageConfig->getContentType(),
				[$pagesConfig->getContentsDir()]
			);

			$breadCrumb = $pagesConfig->getBreadCrumbFor( $pageConfig );
			$content    = $contentRenderer->render( $pageConfig->getContentFile() );

			$data = [
				'pages'      => $pagesConfig,
				'page'       => $pageConfig,
				'breadCrumb' => $breadCrumb,
				'content'    => $content,
			];

			return $pageRenderer->render( $pageConfig->getTemplate(), $data );
		}
		catch ( InvalidRenderer $e )
		{
			$this->style->error( 'Invalid renderer set: ' . $e->getRenderer() . ' - skipping' );

			return '';
		}
	}

	private function getFullPath( string $dir, string $file ) : string
	{
		return rtrim( $dir, DIRECTORY_SEPARATOR ) . DIRECTORY_SEPARATOR . ltrim( $file, DIRECTORY_SEPARATOR );
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
