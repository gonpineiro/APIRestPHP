<?php

/* Root Path */
include_once 'paths.php';

/* AutoLoad composer & local */
require '../../vendor/autoload.php';
require ROOT_PATH . 'app/utils/funciones.php';

/* Carga del DOTENV */
$dotenv = \Dotenv\Dotenv::createImmutable(ROOT_PATH);
$dotenv->load();

/* Modo produccion: true */
define('PROD', $_ENV['PROD'] == 'true' ? true : false);

/* ######################### */

/* Headers */
include_once 'headers.php';

/* Tokens */
include 'tokens.php';

/* Database */
include 'db.php';
