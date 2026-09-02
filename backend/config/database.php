<?php

/**
 * Database Configuration
 *
 * Support:
 * - Local XAMPP (.env)
 * - Railway Environment Variables
 */


date_default_timezone_set('Asia/Jakarta');



/**
 * Load .env untuk local development
 */
$envPath = __DIR__ . '/../../.env';


if (file_exists($envPath)) {


    $lines = file(
        $envPath,
        FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES
    );


    foreach ($lines as $line) {


        if (strpos(trim($line), '#') === 0) {
            continue;
        }


        if (!str_contains($line, '=')) {
            continue;
        }


        [$name, $value] = explode('=', $line, 2);


        $name = trim($name);


        $value = trim(
            $value,
            " \t\n\r\0\x0B\"'"
        );


        if (!defined($name)) {

            define($name, $value);

        }

    }

}



/**
 * Database Config
 *
 * Priority:
 * 1. Railway Variables
 * 2. .env
 * 3. Local Default
 */


define(
    'DB_HOST',
    getenv('MYSQLHOST')
        ?: (defined('DB_HOST') ? DB_HOST : 'localhost')
);


define(
    'DB_PORT',
    getenv('MYSQLPORT')
        ?: (defined('DB_PORT') ? DB_PORT : '3306')
);


define(
    'DB_USER',
    getenv('MYSQLUSER')
        ?: (defined('DB_USER') ? DB_USER : 'root')
);


define(
    'DB_PASS',
    getenv('MYSQLPASSWORD')
        ?: (defined('DB_PASS') ? DB_PASS : '')
);


define(
    'DB_NAME',
    getenv('MYSQLDATABASE')
        ?: (defined('DB_NAME') ? DB_NAME : 'school_website')
);




/**
 * Application Base Path
 */

if (!defined('APP_BASE_PATH')) {


    $projectRoot =
        realpath(dirname(__DIR__, 2))
        ?: dirname(__DIR__, 2);


    $documentRoot =
        isset($_SERVER['DOCUMENT_ROOT'])
        ? realpath($_SERVER['DOCUMENT_ROOT'])
        : '';


    $basePath =
        '/' . basename($projectRoot);



    if ($documentRoot) {


        $project =
            str_replace(
                '\\',
                '/',
                $projectRoot
            );


        $document =
            rtrim(
                str_replace(
                    '\\',
                    '/',
                    $documentRoot
                ),
                '/'
            );


        if (stripos($project, $document) === 0) {


            $relative =
                trim(
                    substr(
                        $project,
                        strlen($document)
                    ),
                    '/'
                );


            $basePath =
                $relative
                ? '/' . $relative
                : '';

        }

    }


    define(
        'APP_BASE_PATH',
        rtrim($basePath,'/')
    );

}




/**
 * Site URL
 */

if (!defined('SITE_URL')) {


    $https =
        (!empty($_SERVER['HTTPS'])
        && $_SERVER['HTTPS'] !== 'off')
        ||
        ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https';


    define(
        'SITE_URL',
        ($https ? 'https' : 'http')
        . '://'
        . ($_SERVER['HTTP_HOST'] ?? 'localhost')
        . APP_BASE_PATH
    );

}




/**
 * Cookie Path
 */

if (!defined('APP_COOKIE_PATH')) {


    define(
        'APP_COOKIE_PATH',
        APP_BASE_PATH === ''
        ? '/'
        : APP_BASE_PATH . '/'
    );

}




/**
 * PDO Connection
 */

try {


    $pdo = new PDO(

        "mysql:host=" . DB_HOST .
        ";port=" . DB_PORT .
        ";dbname=" . DB_NAME .
        ";charset=utf8mb4",


        DB_USER,


        DB_PASS,


        [

            PDO::ATTR_ERRMODE =>
                PDO::ERRMODE_EXCEPTION,


            PDO::ATTR_DEFAULT_FETCH_MODE =>
                PDO::FETCH_ASSOC,


            PDO::ATTR_EMULATE_PREPARES =>
                false

        ]

    );


} catch (PDOException $e) {


    die(
        "Database Error: "
        . $e->getMessage()
    );

}




/**
 * Migration
 */

$migration =
    __DIR__ . '/../migrations/public_schema.php';


if (file_exists($migration)) {


    require_once $migration;


    if (function_exists('ensure_public_schema')) {

        ensure_public_schema($pdo);

    }

}