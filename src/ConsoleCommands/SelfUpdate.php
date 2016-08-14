<?php declare(strict_types = 1);
/**
 * @author hollodotme
 */

namespace IceHawk\StaticPageGenerator\ConsoleCommands;

use Humbug\SelfUpdate\Strategy\GithubStrategy;
use Humbug\SelfUpdate\Updater;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class SelfUpdate
 * @package IceHawk\StaticPageGenerator\ConsoleCommands
 */
final class SelfUpdate extends Command
{
	protected function configure()
	{
		$this->setAliases( [ 'selfupdate' ] );
		$this->setDescription( 'Updates this PHAR to latest version.' );

		$this->addOption(
			'stability', 's', InputOption::VALUE_OPTIONAL,
			sprintf(
				'Specify the stability (%s, %s or %s)',
				GithubStrategy::STABLE,
				GithubStrategy::UNSTABLE,
				GithubStrategy::ANY
			),
			GithubStrategy::STABLE
		);
	}

	protected function execute( InputInterface $input, OutputInterface $output )
	{
		$logger  = new ConsoleLogger( $output );
		$updater = new Updater( null, false, Updater::STRATEGY_GITHUB );

		/** @var GithubStrategy $strategy */
		$strategy = $updater->getStrategy();

		$strategy->setPackageName( 'icehawk/static-page-generator' );
		$strategy->setPharName( 'static-page-generator.phar' );
		$strategy->setCurrentLocalVersion( '@package_version@' );

		$stability = $input->getOption( 'stability' );
		$strategy->setStability( $stability );

		if ( $updater->hasUpdate() )
		{
			$newVersion = $updater->getNewVersion();

			$logger->info( sprintf( 'The current stable version available is: %s', $newVersion ) );
			$logger->info( 'Updating...' );

			if ( $updater->update() )
			{
				$logger->info( sprintf( 'Successful! You now have version %s installed', $newVersion ) );
			}
		}
		elseif ( false === $updater->getNewVersion() )
		{
			$logger->alert( 'There is no stable version available.' );
		}
		else
		{
			$logger->info( '@package_version@ is the latest stable version.' );
		}

		return 0;
	}
}
