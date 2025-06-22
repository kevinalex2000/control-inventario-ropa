<?php
require_once "../modelos/Venta.php";
if (strlen(session_id()) < 1)
	session_start();

$venta = new Venta();


function guardar()
{
	global $venta;

	$idusuario = $_SESSION["idusuario"];
	$input = file_get_contents('php://input');
	$data = json_decode($input, true);

	$idcliente = (int) $data['idcliente'];
	$fechahora = $data['fechahora'];
	$idtipocancelacion = (int) $data['idtipocancelacion'];
	$adelanto = (float) $data['adelanto'];
	$totalventa = 0;
	$detalleventa = [];

	foreach ($data['detalle'] as $item) {
		$idarticulo = (int) $item['idarticulo'];
		$precioventa = (float) $item['precioventa'];
		$cantidad = (int) $item['cantidad'];
		$idtalla = (int) $item['idtalla'];
		$descuento = (float) $item['descuento'];
		$subtotal = ($precioventa * $cantidad) - $descuento;
		$totalventa += $subtotal;
		array_push($detalleventa, [
			'precioventa' => $precioventa,
			'cantidad' => $cantidad,
			'idarticulo' => $idarticulo,
			'idtalla' => $idtalla,
			'descuento' => $descuento,
		]);
	}

	$rspta = $venta->insertar(
		$idusuario,
		$idcliente,
		$fechahora,
		$totalventa,
		$idtipocancelacion,
		$adelanto,
		$detalleventa
	);

	return $rspta ? "Datos registrados correctamente" : "Error al registrar los datos";
}

function listarDetalle()
{
	global $venta;
	//recibimos el idventa
	$id = $_GET['idventa'];
	$rspta = $venta->listarDetalle($id);
	$data = array();

	while ($reg = $rspta->fetch_object()) {
		$data[] = array(
			"idarticulo" => $reg->idarticulo,
			"nombre" => $reg->nombre,
			"imagen" => $reg->imagen,
			"talla" => $reg->talla,
			"cantidad" => $reg->cantidad,
			"precioventa" => $reg->precio_venta,
			"descuento" => $reg->descuento,
			"subtotal" => ($reg->precio_venta * $reg->cantidad) - $reg->descuento
		);
	}

	return json_encode($data);
}

function generarCodigoVenta($idventa, $fechaRegistro)
{
	// Convertimos la fecha a formato YYYYMMDD
	$fecha = date('Ym', strtotime($fechaRegistro));

	// Rellenamos el idventa con ceros a la izquierda para que tenga 4 dígitos
	$numero = str_pad($idventa, 4, '0', STR_PAD_LEFT);

	// Concatenamos todo
	return "VE-{$fecha}{$numero}";
}

$idventa = isset($_POST["idventa"]) ? limpiarCadena($_POST["idventa"]) : "";
$idcliente = isset($_POST["idcliente"]) ? limpiarCadena($_POST["idcliente"]) : "";
$idusuario = $_SESSION["idusuario"];
$tipo_comprobante = isset($_POST["tipo_comprobante"]) ? limpiarCadena($_POST["tipo_comprobante"]) : "";
$serie_comprobante = isset($_POST["serie_comprobante"]) ? limpiarCadena($_POST["serie_comprobante"]) : "";
$num_comprobante = isset($_POST["num_comprobante"]) ? limpiarCadena($_POST["num_comprobante"]) : "";
$fecha_hora = isset($_POST["fecha_hora"]) ? limpiarCadena($_POST["fecha_hora"]) : "";
$impuesto = isset($_POST["impuesto"]) ? limpiarCadena($_POST["impuesto"]) : "";
$total_venta = isset($_POST["total_venta"]) ? limpiarCadena($_POST["total_venta"]) : "";

switch ($_GET["op"]) {
	case 'guardar':
		echo guardar();
		break;
	case 'anular':
		$rspta = $venta->anular($idventa);
		echo $rspta ? "Venta anulado correctamente" : "No se pudo anular la Venta";
		break;
	case 'entregar':
		$rspta = $venta->Entregar($idventa);
		echo $rspta ? "Venta entregada correctamente" : "No se pudo marcar como entregado la Venta";
		break;
	case 'completarpago':
		$rspta = $venta->CompletarPago($idventa);
		echo $rspta ? "Venta guardada correctamente" : "No se pudo completar el guardado de la Venta";
		break;

	case 'mostrar':
		$rspta = $venta->mostrar($idventa);
		$rspta['codigo'] = generarCodigoVenta($rspta['idventa'], $rspta['fecha_registro']);
		echo json_encode($rspta);
		break;

	case 'listarDetalle':
		echo listarDetalle();
		break;

	case 'listar':
		$fecha_desde = isset($_GET['fecha_desde']) ? $_GET['fecha_desde'] : '';
		$fecha_hasta = isset($_GET['fecha_hasta']) ? $_GET['fecha_hasta'] : '';
		$idcliente = isset($_GET['idcliente']) ? $_GET['idcliente'] : '';

		$rspta = $venta->listar($fecha_desde, $fecha_hasta, $idcliente);
		$data = array();

		while ($reg = $rspta->fetch_object()) {
			if ($reg->tipo_comprobante == 'Ticket') {
				$url = '../reportes/exTicket.php?id=';
			} else {
				$url = '../reportes/exFactura.php?id=';
			}

			$elemntPagado = '';

			if ((int) $reg->estado != 3) {
				$elemntPagado = ($reg->pagado == 1)
					? '<span class="label bg-green"><i class="fa fa-check-circle"></i> Pagado</span>'
					: '<span class="label bg-gray"><i class="fa fa-exclamation-triangle"></i>  Debe: S/.' . number_format($reg->total_venta - floatval($reg->adelanto), 2) . '</span>';
			}

			$data[] = array(
				"0" => (($reg->estado == 1) ?
					'<button class="btn btn-warning btn-xs" onclick="mostrar(' . $reg->idventa . ')"><i class="fa fa-eye"></i></button> ' .
					'<button class="btn btn-danger btn-xs" onclick="anular(' . $reg->idventa . ')"><i class="fa fa-times"></i></button> '
					:
					'<button class="btn btn-warning btn-xs" onclick="mostrar(' . $reg->idventa . ')"><i class="fa fa-eye"></i></button> '
				),
				"1" => $reg->fecha_registro,
				"2" => generarCodigoVenta($reg->idventa, $reg->fecha_registro),
				"3" => $reg->cliente . ' (Tel:' . $reg->telefono . ')',
				"4" => $reg->usuario,
				"5" => $reg->total_venta,
				// Estado de pago
				"6" => $elemntPagado,
				// Estado general
				"7" => ($reg->estado == 1) ? '<span class="label bg-blue">Entrega pendiente</span>'
					: (($reg->estado == 2) ? '<span class="label bg-green">Completado</span>'
						: '<span class="label bg-red">Anulado</span>')
			);
		}
		$results = array(
			"sEcho" => 1,//info para datatables
			"iTotalRecords" => count($data),//enviamos el total de registros al datatable
			"iTotalDisplayRecords" => count($data),//enviamos el total de registros a visualizar
			"aaData" => $data
		);
		echo json_encode($results);
		break;

	case 'listarArticulos':
		require_once "../modelos/Articulo.php";
		$articulo = new Articulo();

		$rspta = $articulo->listar();
		$data = array();

		while ($reg = $rspta->fetch_object()) {
			$data[] = array(
				"0" => '<button class="btn btn-warning" onclick="agregarDetalle(' . $reg->idarticulo . ',\'' . $reg->nombre . '\',' . $reg->precio_venta . ')"><span class="fa fa-plus"></span></button>',
				"1" => $reg->nombre,
				"2" => $reg->categoria,
				"3" => $reg->codigo,
				"4" => $reg->stock,
				"5" => $reg->precio_venta,
				"6" => "<img src='../files/articulos/" . $reg->imagen . "' height='50px' width='50px'>"

			);
		}
		$results = array(
			"sEcho" => 1,//info para datatables
			"iTotalRecords" => count($data),//enviamos el total de registros al datatable
			"iTotalDisplayRecords" => count($data),//enviamos el total de registros a visualizar
			"aaData" => $data
		);
		echo json_encode($results);

		break;
}
?>