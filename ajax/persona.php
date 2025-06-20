<?php
require_once "../modelos/Persona.php";

$persona = new Persona();

function listar()
{
	global $persona;
	$data = [];
	$idtipopersona = $_GET["idtipopersona"];
	$rspta = $persona->listar(idtipopersona: $idtipopersona);

	while ($reg = $rspta->fetch_object()) {
		$data[] = array(
			"idpersona" => $reg->idpersona,
			"tipopersona" => $reg->tipo_persona,
			"nombre" => $reg->nombre,
			"tipodocumento" => $reg->tipo_documento,
			"numdocumento" => $reg->num_documento,
			"direccion" => $reg->direccion,
			"telefono" => $reg->telefono,
			"email" => $reg->email
		);
	}

	return json_encode($data);
}

$idpersona = isset($_POST["idpersona"]) ? limpiarCadena($_POST["idpersona"]) : "";
$tipo_persona = isset($_POST["tipo_persona"]) ? limpiarCadena($_POST["tipo_persona"]) : "";
$nombre = isset($_POST["nombre"]) ? limpiarCadena($_POST["nombre"]) : "";
$tipo_documento = isset($_POST["tipo_documento"]) ? limpiarCadena($_POST["tipo_documento"]) : "";
$num_documento = isset($_POST["num_documento"]) ? limpiarCadena($_POST["num_documento"]) : "";
$direccion = isset($_POST["direccion"]) ? limpiarCadena($_POST["direccion"]) : "";
$telefono = isset($_POST["telefono"]) ? limpiarCadena($_POST["telefono"]) : "";
$email = isset($_POST["email"]) ? limpiarCadena($_POST["email"]) : "";

switch ($_GET["op"]) {
	case 'guardaryeditar':
		if (empty($idpersona)) {
			$rspta = $persona->insertar($tipo_persona, $nombre, $tipo_documento, $num_documento, $direccion, $telefono, $email);
			echo $rspta ? "Datos registrados correctamente" : "No se pudo registrar los datos";
		} else {
			$rspta = $persona->editar($idpersona, $tipo_persona, $nombre, $tipo_documento, $num_documento, $direccion, $telefono, $email);
			echo $rspta ? "Datos actualizados correctamente" : "No se pudo actualizar los datos";
		}
		break;

	case 'eliminar':
		$rspta = $persona->eliminar($idpersona);
		echo $rspta ? "Datos eliminados correctamente" : "No se pudo eliminar los datos. Valide que no tenga registros asociadas.";
		break;

	case 'mostrar':
		$rspta = $persona->mostrar($idpersona);
		echo json_encode($rspta);
		break;

	case 'listar':
		echo listar();
		break;
}
