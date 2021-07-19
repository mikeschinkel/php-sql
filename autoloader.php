<?php

/** @noinspection PhpIncludeInspection */
require __DIR__ . '/Sql/Grammar/functions.php';

ini_set('memory_limit','256M');

spl_autoload_register( function ( string $class_name ) {
    $filepath = sprintf( '%s%s%s.php', __DIR__, DIRECTORY_SEPARATOR, str_replace( '\\', DIRECTORY_SEPARATOR, $class_name ) );
    if ( is_file( $filepath ) ) {
        require $filepath;
    }
} );

