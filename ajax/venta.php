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

	$idcliente = $data['idcliente'];
	$fechahora = $data['fechahora'];
	$idtipocancelacion = (int) $data['idtipocancelacion'];
	$adelanto = $data['adelanto'];
	$totalventa = 0;
	$detalleventa = [];

	foreach ($data['detalle'] as $item) {
		$idarticulo = $item['idarticulo'];
		$preciocompra = $item['precioventa'];
		$cantidad = $item['cantidad'];
		$idtalla = $item['idtalla'];
		$descuento = $item['descuento'];
		$subtotal = (floatval($preciocompra) * floatval($cantidad)) - $descuento;
		$totalventa += $subtotal;
		array_push($detalleventa, [
			'precioventa' => $preciocompra,
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
		echo $rspta ? "Ingreso anulado correctamente" : "No se pudo anular el ingreso";
		break;

	case 'mostrar':
		$rspta = $venta->mostrar($idventa);
		echo json_encode($rspta);
		break;

	case 'listarDetalle':
		//recibimos el idventa
		$id = $_GET['id'];

		$rspta = $venta->listarDetalle($id);
		$total = 0;
		echo ' <thead style="background-color:#A9D0F5">
        <th>Opciones</th>
        <th>Articulo</th>
        <th>Cantidad</th>
        <th>Precio Venta</th>
        <th>Descuento</th>
        <th>Subtotal</th>
       </thead>';
		while ($reg = $rspta->fetch_object()) {
			echo '<tr class="filas">
			<td></td>
			<td>' . $reg->nombre . '</td>
			<td>' . $reg->cantidad . '</td>
			<td>' . $reg->precio_venta . '</td>
			<td>' . $reg->descuento . '</td>
			<td>' . $reg->subtotal . '</td></tr>';
			$total = $total + ($reg->precio_venta * $reg->cantidad - $reg->descuento);
		}
		echo '<tfoot>
         <th>TOTAL</th>
         <th></th>
         <th></th>
         <th></th>
         <th></th>
         <th><h4 id="total">S/. ' . $total . '</h4><input type="hidden" name="total_venta" id="total_venta"></th>
       </tfoot>';
		break;

	case 'listar':
		$rspta = $venta->listar();
		$data = array();

		while ($reg = $rspta->fetch_object()) {
			if ($reg->tipo_comprobante == 'Ticket') {
				$url = '../reportes/exTicket.php?id=';
			} else {
				$url = '../reportes/exFactura.php?id=';
			}

			$data[] = array(
				"0" => (($reg->estado == 'Aceptado') ? '<button class="btn btn-warning btn-xs" onclick="mostrar(' . $reg->idventa . ')"><i class="fa fa-eye"></i></button>' . ' ' . '<button class="btn btn-danger btn-xs" onclick="anular(' . $reg->idventa . ')"><i class="fa fa-close"></i></button>' : '<button class="btn btn-warning btn-xs" onclick="mostrar(' . $reg->idventa . ')"><i class="fa fa-eye"></i></button>') .
					'<a target="_blank" href="' . $url . $reg->idventa . '"> <button class="btn btn-info btn-xs"><i class="fa fa-file"></i></button></a>',
				"1" => $reg->fecha,
				"2" => $reg->cliente,
				"3" => $reg->usuario,
				"4" => $reg->tipo_comprobante,
				"5" => $reg->serie_comprobante . '-' . $reg->num_comprobante,
				"6" => $reg->total_venta,
				"7" => ($reg->estado == 'Aceptado') ? '<span class="label bg-green">Aceptado</span>' : '<span class="label bg-red">Anulado</span>'
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

	case 'selectCliente':
		require_once "../modelos/Persona.php";
		$persona = new Persona();

		$rspta = $persona->listarc();

		while ($reg = $rspta->fetch_object()) {
			echo '<option value=' . $reg->idpersona . '>' . $reg->nombre . '</option>';
		}
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