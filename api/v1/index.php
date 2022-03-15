<?php

require_once '../../app/config/global.php';

$token = getBearerToken();
if ($token == TOKEN_KEY) {
	switch ($url['path']) {
		case 'wapusuario':
			include './wapusuario/index.php';
			break;
		case 'wappersona':
			include './wappersona/index.php';
			break;
		case 'deportesusuario':
			include './deportesusuario/index.php';
			break;
		case 'acarreo':
			include './acarreo/index.php';
			break;
		case 'libretasanitaria':
			include './libretasanitaria/index.php';
			break;
		case 'empleado':
			include './empleado/index.php';
			break;
		case 'login':
			include './login/index.php';
			break;
		case 'licenciaconducir':
			include './licenciaconducir/index.php';
			break;
		case 'totemsdata':
			include './totemsdata/index.php';
			break;
		case 'renaper':
			include './renaper/index.php';
			break;
		case 'wlaplicacion':
			include './wlaplicacion/index.php';
			break;
		case 'wapusuariosperfiles':
			include './wapusuariosperfiles/index.php';
			break;
		default:
			sendRes(null, 'no existe el endpoint', null);
			break;
	}
} else {
	header("HTTP/1.1 401 Unauthorized");
}
session_unset();
