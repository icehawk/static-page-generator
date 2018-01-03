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

namespace IceHawk\StaticPageGenerator;

use IceHawk\StaticPageGenerator\ConsoleCommands\CheckLinks;
use IceHawk\StaticPageGenerator\ConsoleCommands\GeneratePages;
use IceHawk\StaticPageGenerator\ConsoleCommands\GenerateSitemap;
use IceHawk\StaticPageGenerator\ConsoleCommands\RollBack;
use IceHawk\StaticPageGenerator\ConsoleCommands\SelfUpdate;

error_reporting( -1 );
ini_set( 'display_errors', 'On' );

require(__DIR__ . '/../vendor/autoload.php');

define( 'PHAR_DIR', dirname( __DIR__ ) );
define( 'WORKING_DIR', getcwd() );

try
{
	$app = new StaticPageGenerator( 'Static page generator', '@package_version@' );
	$app->addCommands(
		[
			new GeneratePages( 'generate:pages' ),
			new GenerateSitemap( 'generate:sitemap' ),
			new CheckLinks( 'check:links' ),
			new SelfUpdate( 'self-update' ),
			new RollBack( 'rollback' ),
		]
	);
	$code = $app->run();
	exit( $code );
}
catch ( \Throwable $e )
{
	echo "Uncaught " . get_class( $e ) . " with message: " . $e->getMessage() . "\n";
	echo $e->getTraceAsString();
	exit( 1 );
}
