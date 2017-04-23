<?php declare(strict_types=1);
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

use IceHawk\StaticPageGenerator\ConsoleCommands\Configs\ProjectConfig;
use IceHawk\StaticPageGenerator\Exceptions\ConfigNotFound;
use IceHawk\StaticPageGenerator\StaticPageGenerator;
use Symfony\Component\Console\Command\Command;

/**
 * Class AbstractConsoleCommand
 *
 * @package IceHawk\StaticPageGenerator\ConsoleCommands
 */
abstract class AbstractConsoleCommand extends Command
{
	public function getEnv(): StaticPageGenerator
	{
		/** @var StaticPageGenerator $spg */
		return $this->getApplication();
	}

	final protected function getFullPath( string $dir, string $file ): string
	{
		return rtrim( $dir, DIRECTORY_SEPARATOR ) . DIRECTORY_SEPARATOR . ltrim( $file, DIRECTORY_SEPARATOR );
	}

	final protected function loadConfig( string $configPath, array $overwrites ): ProjectConfig
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
}
