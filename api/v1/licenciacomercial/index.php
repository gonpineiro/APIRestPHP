<?php

use App\Controllers\LicenciaComercial\Lc_SolicitudController;
use App\Controllers\LicenciaComercial\Lc_DocumentoController;

/* Metodo GET */

$GLOBALS['exect'][] = 'lc_solicitud';
if ($url['method'] == 'GET') {
	$action = $_GET['action'];
	unset($_GET['action']);

	switch ($action) {
		case '0':
			/* Obtenemos todas las solicitudes de catastro nuevas */
			Lc_SolicitudController::indexCatastroNuevas();
			break;

		case '1':
			/* Obtenemos una solicitud puntual */
			Lc_SolicitudController::getById();
			break;

		case '2':
			/* Obtenemos la ultima solicitud */
			$_GET['TOP'] = 1;
			Lc_SolicitudController::get($_GET);
			break;
			
		case '3':
			/* Obtenemos todas las solicitudes de catastro rechazadas */
			Lc_SolicitudController::indexCatastroRechazadas();
			break;


		default:
			$error = new ErrorException('El action no es valido');
			sendRes(null, $error->getMessage(), $_GET);
			exit;
			break;
	}
}

/* Metodo POST */
if ($url['method'] == 'POST') {
	if (isset($_POST['step']) && $_POST['step'] == '3') {
		Lc_DocumentoController::update();
	} else {
		Lc_SolicitudController::store($_POST);
	}
}

/* Metodo PUT */
if ($url['method'] == 'PUT') {
	parse_str(file_get_contents('php://input'), $_PUT);
	$id = $url['id'];

	$step = $_PUT['step'];
	unset($_PUT['step']);

	switch ($step) {
		case '1':
			/* Datos personales */
			Lc_SolicitudController::updateFirts($_PUT, $id);
			break;

		case '2':
			/* Nomenclatura y rubros */
			Lc_SolicitudController::updateSec($_PUT, $id);
			break;

		case '3':
			/* Documentacion */
			Lc_SolicitudController::updateThir($_PUT, $id);
			break;

		case '4':
			/* Rechazo o aprobacion de catastro */
			Lc_SolicitudController::catastroUpdate($_PUT, $id);
			break;

		default:
			# code...
			break;
	}


	if (!$lc instanceof ErrorException) {
		$_PUT['id'] = $id;
		sendRes($_PUT);
	} else {
		sendRes(null, $arbolado->getMessage(), ['id' => $id]);
	};
	eClean();
}

/* Metodo DELETE */
if ($url['method'] == 'DELETE') {
	$id = $url['id'];
	$arbolado = $arbSolicitudController->delete($url['id']);
	if (!$arbolado instanceof ErrorException) {
		sendRes(['ReferenciaID' => $id]);
	} else {
		sendRes(null, $arbolado->getMessage(), ['ReferenciaID' => $id]);
	};
	eClean();
}

header("HTTP/1.1 200 Bad Request");

eClean();
