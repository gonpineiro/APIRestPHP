<?php

use App\Controllers\LicenciaComercial\Lc_SolicitudController;

$lcSolicitudController = new Lc_SolicitudController();

/* Metodo GET */
if ($url['method'] == 'GET') {
	if (isset($_GET) && count($_GET) > 0 && isset($_GET['action'])) {
		$action = $_GET['action'];
		unset($_GET['action']);

		switch ($action) {
			case '0':
				/* Obtenemos todas las solicitudes, o funcion del estado */
				$_GET['TOP'] = 1000;
				$lc = $lcSolicitudController->index($_GET, ['order' => ' ORDER BY id DESC ']);
				break;

			case '1':
				/* Obtenemos una solicitud puntual */
				$lc = $lcSolicitudController->get($_GET);
				break;

			case '2':
				/* Obtenemos la ultima solicitud */
				$_GET['TOP'] = 1;
				$lc = $lcSolicitudController->get($_GET, ['order' => ' ORDER BY id DESC ']);
				break;

			default:
				$lc = new ErrorException('El action no es valido');
				break;
		}

		/* Envio del mensaje */
		if (!$lc instanceof ErrorException) {
			if ($lc !== false) {
				sendRes($lc);
			} else {
				sendRes(null, 'No se encontro la solicitud', $_GET);
			}
		} else {
			sendRes(null, $lc->getMessage(), $_GET);
		};
	} else {
	}
	eClean();
}

/* Metodo POST */
if ($url['method'] == 'POST') {
	/* Guardamos la solicitud */
	$id = $lcSolicitudController->store($_POST);
	if (!$id instanceof ErrorException) {
		sendRes(['id' => $id]);
	} else {
		sendRes(null, $id->getMessage(), $_GET);
	};
	exit();
}

/* Metodo PUT */
if ($url['method'] == 'PUT') {
	parse_str(file_get_contents('php://input'), $_PUT);
	$id = $url['id'];

	/* Extraemos el contacto y el email  */
	$contacto = $_PUT['contacto'];
	$email = $_PUT['email'];
	unset($_PUT['contacto']);
	unset($_PUT['email']);

	$arbolado = $arbSolicitudController->update($_PUT, $id);

	if (!$arbolado instanceof ErrorException) {
		/* Enviamos el correo electronico */
		$data = [
			'id' => $id,
			'email' => $email,
			'contacto' => $contacto,
			'observacion' => $_PUT['observacion']
		];
		$arbSolicitudController->sendEmail($id, $_PUT['estado'], $data);
		$_PUT['id'] = $id;
		sendRes($_PUT);
	} else {
		sendRes(null, $arbolado->getMessage(), ['ReferenciaID' => $id]);
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
