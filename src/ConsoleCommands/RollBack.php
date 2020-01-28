<?php declare(strict_types = 1);
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

use Humbug\SelfUpdate\Strategy\GithubStrategy;
use Humbug\SelfUpdate\Updater;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class RollBack
 * @package IceHawk\StaticPageGenerator\ConsoleCommands
 */
final class RollBack extends Command
{
	protected function configure() : void
	{
		/** @noinspection UnusedFunctionResultInspection */
		$this->setDescription( 'Rolls back this PHAR to the previous version.' );
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

		if ( $updater->rollback() )
		{
			$logger->info( 'Roll back successful!' );
		}
		else
		{
			$logger->alert( 'Roll back failed.' );
		}

		return 0;
	}
}
