<?php

use App\Controllers\Arbolado\Arb_PodadorController;

$arbPodadorController = new Arb_PodadorController();

/* Metodo GET */
if ($url['method'] == 'GET') {
	if (isset($_GET) && count($_GET) > 0 && isset($_GET['action'])) {
		$action = $_GET['action'];
		unset($_GET['action']);

		switch ($action) {
			case '0':
				/* Obtenemos todas las solicitudes, o funcion del estado */
				$_GET['TOP'] = 1000;
				if ($_GET['estado'] == 'todas') {
					unset($_GET['estado']);
					$podador = $arbPodadorController->index($_GET, ['order' => ' ORDER BY id DESC ']);
				} else {
					$podador = $arbPodadorController->getNoDeshabilitados($_GET, ['order' => ' ORDER BY id DESC ']);
				}
				break;

			case '1':
				/* Obtenemos una solicitud puntual */
				$podador = $arbPodadorController->get($_GET);
				break;

			case '2':
				/* Obtenemos el estado de la ultima solicitud enviada por el usuario */
				$podador = $arbPodadorController->getEstadoSolicitudDetalle($_GET['id_wappersonas']);
				break;

			case '3':
				/* Obtenemos todas las solicitudes deshabilitadas */
				$_GET['TOP'] = 1000;
				$podador = $arbPodadorController->getDeshabilitados($_GET, ['order' => ' ORDER BY id DESC ']);
				break;

			default:
				$podador = new ErrorException('El action no es valido');
				break;
		}

		if (!$podador instanceof ErrorException) {
			if ($podador !== false) {
				sendRes($podador);
			} else {
				sendRes(null, 'No se encontro la solicitud', $_GET);
			}
		} else {
			sendRes(null, $arbolado->getMessage(), $_GET);
		};
	}
	eClean();
}

/* Metodo POST */
if ($url['method'] == 'POST') {

	$file = $_FILES['certificado'];
	$nameFile = uniqid() . getExtFile($file);

	$_POST['certificado'] = $nameFile;

	/* Guardamos la solicitud */
	$solicitud = $arbPodadorController->existeSol($_POST['id_usuario']);

	$id = $arbPodadorController->store($_POST);

	/* copiamos el archivo en la carpeta correspondiente */
	$path = getPathFile($file, "arbolado/podador/$id/", $nameFile);
	$copiado = copy($file['tmp_name'], $path);

	if ($id instanceof ErrorException || !$copiado) {
		$arbPodadorController->delete($id);
		sendRes(null, $id->getMessage(), $_GET);
		exit;
	}

	/* Envio de correo electronico */
	$data = ['email' => $_POST['email']];
	$arbPodadorController->sendEmail($id, 'envio', $data);

	sendRes(['id' => $id]);
	exit;
}

/* Metodo PUT */
if ($url['method'] == 'PUT') {
	parse_str(file_get_contents('php://input'), $_PUT);

	if ($_PUT["motivo_deshabilitado"] != "null") {
		$typeSendEmail = 'deshabilitado';
		$obsSendEmail = $_PUT['motivo_deshabilitado'];
		$_PUT['fecha_deshabilitado'] = date("Y-m-d", strtotime(date('Y-m-d') . "+ 1 year"));
	} else {
		$typeSendEmail = $_PUT['estado'];
		$_PUT["motivo_deshabilitado"] = null;
		$_PUT["fecha_deshabilitado"] = null;
		$obsSendEmail = $_PUT['observacion'];
	}

	$id = $url['id'];
	$email = $_PUT['email'];
	unset($_PUT['email']);
	$arbolado = $arbPodadorController->update($_PUT, $id);

	/* Envio de correo electronico */
	$data = [
		'email' => $email,
		'observacion' =>  $obsSendEmail,
	];

	$arbPodadorController->sendEmail($id, $typeSendEmail, $data);

	if (!$arbolado instanceof ErrorException) {
		$_PUT['id'] = $id;
		if ($_PUT['fecha_deshabilitado'] > date('Y-m-d')) {
			$_PUT['estado'] = 'deshabilitado';
		}
		sendRes($_PUT);
	} else {
		sendRes(null, $arbolado->getMessage(), ['id' => $id]);
	};
	eClean();
}

/* Metodo DELETE */
if ($url['method'] == 'DELETE') {
	$id = $url['id'];
	$arbolado = $arbPodadorController->delete($url['id']);
	if (!$arbolado instanceof ErrorException) {
		sendRes(['ReferenciaID' => $id]);
	} else {
		sendRes(null, $arbolado->getMessage(), ['ReferenciaID' => $id]);
	};
	eClean();
}

header("HTTP/1.1 200 Bad Request");

eClean();
