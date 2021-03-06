<?php

use App\Controllers\AcarreoController;

$acarreoController = new AcarreoController();

/* Metodo GET */
if ($url['method'] == 'GET') {
	$id = $url['id'];
	if (isset($_GET) && $id !== null && $id !== '') {
		$acarreo = $acarreoController->getByReferenciaId($id);
		if (!$acarreo instanceof ErrorException) {
			if ($acarreo) {
				sendRes($acarreo);
			} else {
				sendRes(null, 'No se encuenta el recurso', ['ReferenciaID' => $id]);
			}
		} else {
			sendRes(null, $acarreo->getMessage(), ['ReferenciaID' => $id]);
		};
	} else {
		sendRes(null, 'Error en los parametros', ['ReferenciaID' => $id]);
	}
	eClean();
}

header("HTTP/1.1 200 Bad Request");

eClean();
