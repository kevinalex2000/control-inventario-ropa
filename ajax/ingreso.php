<?php
require_once "../modelos/Ingreso.php";
if (strlen(session_id()) < 1)
	session_start();

$ingreso = new Ingreso();

function listarDetalle()
{
	global $ingreso;
	$idingreso = $_GET['id'];

	$rspta = $ingreso->listarDetalle(idingreso: $idingreso);
	$data = array();

	while ($reg = $rspta->fetch_object()) {
		$data[] = array(
			"idingreso" => $reg->idingreso,
			"idarticulo" => $reg->idarticulo,
			"articulo" => $reg->articulo,
			"talla" => $reg->talla,
			"imagen" => $reg->imagen,
			"cantidad" => $reg->cantidad,
			"preciocompra" => $reg->precio_compra,
			"subtotal" => $reg->precio_compra * $reg->cantidad
		);
	}

	return json_encode($data);
}

function guardar()
{
	global $ingreso;

	$idusuario = $_SESSION["idusuario"];
	$input = file_get_contents('php://input');
	$data = json_decode($input, true);

	$idproveedor = $data['idproveedor'];
	$fechahora = $data['fechahora'];
	$totalcompra = 0;
	$detalleingreso = [];

	foreach ($data['detalle'] as $item) {
		$idarticulo = $item['idarticulo'];
		$preciocompra = $item['preciocompra'];
		$cantidad = $item['cantidad'];
		$idtalla = $item['idtalla'];
		$subtotal = floatval($preciocompra) * floatval($cantidad);
		$totalcompra += $subtotal;
		array_push($detalleingreso, [
			'preciocompra' => $preciocompra,
			'cantidad' => $cantidad,
			'idarticulo' => $idarticulo,
			'idtalla' => $idtalla,
		]);
	}

	$rspta = $ingreso->insertar(
		$idusuario,
		$idproveedor,
		$fechahora,
		$totalcompra,
		$detalleingreso
	);

	return $rspta ? "Datos registrados correctamente" : "Error al registrar los datos";
}


$idingreso = isset($_POST["idingreso"]) ? limpiarCadena($_POST["idingreso"]) : "";
$idproveedor = isset($_POST["idproveedor"]) ? limpiarCadena($_POST["idproveedor"]) : "";
$idusuario = $_SESSION["idusuario"];
$tipo_comprobante = isset($_POST["tipo_comprobante"]) ? limpiarCadena($_POST["tipo_comprobante"]) : "";
$serie_comprobante = isset($_POST["serie_comprobante"]) ? limpiarCadena($_POST["serie_comprobante"]) : "";
$num_comprobante = isset($_POST["num_comprobante"]) ? limpiarCadena($_POST["num_comprobante"]) : "";
$fecha_hora = isset($_POST["fecha_hora"]) ? limpiarCadena($_POST["fecha_hora"]) : "";
$impuesto = isset($_POST["impuesto"]) ? limpiarCadena($_POST["impuesto"]) : "";
$total_compra = isset($_POST["total_compra"]) ? limpiarCadena($_POST["total_compra"]) : "";


switch ($_GET["op"]) {
	case 'guardar':
		echo (guardar());
		break;


	case 'anular':
		$rspta = $ingreso->anular($idingreso);
		echo $rspta ? "Ingreso anulado correctamente" : "No se pudo anular el ingreso";
		break;

	case 'mostrar':
		$rspta = $ingreso->mostrar($idingreso);
		echo json_encode($rspta);
		break;

	case 'listarDetalle':
		echo (listarDetalle());
		break;

	case 'listar':
		$rspta = $ingreso->listar();
		$data = array();

		while ($reg = $rspta->fetch_object()) {
			$data[] = array(
				"0" => ($reg->estado == 'Aceptado') ? '<button class="btn btn-warning btn-xs" onclick="mostrar(' . $reg->idingreso . ')"><i class="fa fa-eye"></i></button>' . ' ' . '<button class="btn btn-danger btn-xs" onclick="anular(' . $reg->idingreso . ')"><i class="fa fa-close"></i></button>' : '<button class="btn btn-warning btn-xs" onclick="mostrar(' . $reg->idingreso . ')"><i class="fa fa-eye"></i></button>',
				"1" => $reg->fecha_registro,
				"2" => $reg->proveedor,
				"3" => $reg->total_compra,
				"4" => $reg->usuario,
				"5" => ($reg->estado == 'Aceptado') ? '<span class="label bg-green">Aceptado</span>' : '<span class="label bg-red">Anulado</span>'
			);
		}
		$results = array(
			"sEcho" => 1, //info para datatables
			"iTotalRecords" => count($data), //enviamos el total de registros al datatable
			"iTotalDisplayRecords" => count($data), //enviamos el total de registros a visualizar
			"aaData" => $data
		);
		echo json_encode($results);
		break;

	case 'listarArticulos':
		require_once "../modelos/Articulo.php";
		$articulo = new Articulo();
		$rspta = $articulo->listar(null, null, 1);
		$data = array();

		while ($reg = $rspta->fetch_object()) {
			$data[] = array(
				"0" => '<button class="btn btn-warning" onclick="agregarDetalle(' . $reg->idarticulo . ',\'' . $reg->nombre . ' \' ,\'' . $reg->imagen . ' \' ,\'' . $reg->precio_venta . ' \')"><span class="fa fa-plus"></span></button>',
				"1" => $reg->nombre,
				"2" => $reg->categoria,
				"3" => $reg->codigo,
				"4" => $reg->stock,
				"5" => "<img src='../files/articulos/" . $reg->imagen . "' height='50px' width='50px'>",
				"6" => $reg->idarticulo
			);
		}

		$results = array(
			"sEcho" => 1, //info para datatables
			"iTotalRecords" => count($data), //enviamos el total de registros al datatable
			"iTotalDisplayRecords" => count($data), //enviamos el total de registros a visualizar
			"aaData" => $data
		);
		echo json_encode($results);
		break;
}
