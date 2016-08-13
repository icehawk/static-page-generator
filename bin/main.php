<?php declare(string_types = 1);
/**
 * @author hollodotme
 */

namespace IceHawk\StaticPageGenerator;

use IceHawk\StaticPageGenerator\ConsoleCommands\RollBack;
use IceHawk\StaticPageGenerator\ConsoleCommands\SelfUpdate;
use Symfony\Component\Console\Application;

error_reporting( -1 );
ini_set( 'display_errors', 'On' );

require(__DIR__ . '/../vendor/autoload.php');

define( 'PHAR_DIR', dirname( __DIR__ ) );
define( 'WORKING_DIR', getcwd() );

try
{
	$app = new Application( 'Static page generator', '@package_version@' );
	$app->addCommands(
		[
			new GenerateComponent( 'generate:component' ),
			new SelfUpdate( 'self-update' ),
			new RollBack( 'rollback' ),
		]
	);
	$code = $app->run();
	exit($code);
}
catch ( \Throwable $e )
{
	echo "Uncaught " . get_class( $e ) . " with message: " . $e->getMessage() . "\n";
	echo $e->getTraceAsString();
	exit(1);
}
