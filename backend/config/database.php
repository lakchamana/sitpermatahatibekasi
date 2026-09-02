<?php

/**
 * Konfigurasi Database
 * 
 * Support:
 * 1. Local Development (XAMPP) menggunakan .env
 * 2. Railway Deployment menggunakan Environment Variables
 */

date_default_timezone_set('Asia/Jakarta');


/**
 * Load .env jika tersedia (LOCAL DEVELOPMENT)
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

        if (!strpos($line, '=')) {
            continue;
        }

        list($name, $value) = explode('=', $line, 2);

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
 * Database Configuration
 *
 * Prioritas:
 * 1. Railway Environment Variable
 * 2. .env Local
 * 3. Default XAMPP
 */


if (!defined('DB_HOST')) {

    define(
        'DB_HOST',
        getenv('MYSQL_HOST')
            ?: (defined('DB_HOST') ? DB_HOST : 'localhost')
    );
}


if (!defined('DB_PORT')) {

    define(
        'DB_PORT',
        getenv('MYSQL_PORT')
            ?: (defined('DB_PORT') ? DB_PORT : '3306')
    );
}


if (!defined('DB_USER')) {

    define(
        'DB_USER',
        getenv('MYSQL_USER')
            ?: (defined('DB_USER') ? DB_USER : 'root')
    );
}


if (!defined('DB_PASS')) {

    define(
        'DB_PASS',
        getenv('MYSQL_PASSWORD')
            ?: (defined('DB_PASS') ? DB_PASS : '')
    );
}


if (!defined('DB_NAME')) {

    define(
        'DB_NAME',
        getenv('MYSQL_DATABASE')
            ?: (defined('DB_NAME') ? DB_NAME : 'school_website')
    );
}



/**
 * Application Base Path
 */
if (!defined('APP_BASE_PATH')) {

    $projectRoot = realpath(dirname(__DIR__, 2))
        ?: dirname(__DIR__, 2);


    $documentRoot = isset($_SERVER['DOCUMENT_ROOT'])
        ? (realpath((string) $_SERVER['DOCUMENT_ROOT']) ?: '')
        : '';


    $basePath = '/' . basename($projectRoot);


    if ($documentRoot !== '') {

        $normalizedProject =
            str_replace('\\', '/', $projectRoot);


        $normalizedDocument =
            rtrim(
                str_replace('\\', '/', $documentRoot),
                '/'
            );


        if (stripos($normalizedProject, $normalizedDocument) === 0) {

            $relativeProject =
                trim(
                    substr(
                        $normalizedProject,
                        strlen($normalizedDocument)
                    ),
                    '/'
                );


            $basePath =
                $relativeProject === ''
                ? ''
                : '/' . $relativeProject;
        }
    }


    define(
        'APP_BASE_PATH',
        rtrim($basePath, '/')
    );
}



/**
 * Site URL
 */
if (!defined('SITE_URL')) {


    $isSecure =
        (!empty($_SERVER['HTTPS'])
            && $_SERVER['HTTPS'] !== 'off')

        ||

        strtolower(
            (string)
            ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '')
        ) === 'https';



    $scheme =
        $isSecure
        ? 'https'
        : 'http';



    $host =
        $_SERVER['HTTP_HOST']
        ?? 'localhost';



    define(
        'SITE_URL',
        $scheme .
            '://' .
            $host .
            APP_BASE_PATH
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
 * Database Connection PDO
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
            false,

        ]

    );
} catch (PDOException $e) {


    die('Koneksi database gagal. Silakan periksa konfigurasi database.');
}



/**
 * Pastikan schema publik tersedia
 */
require_once __DIR__ . '/../migrations/public_schema.php';

ensure_public_schema($pdo);
