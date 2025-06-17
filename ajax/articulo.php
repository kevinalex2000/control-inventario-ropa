<?php
require_once "../modelos/Articulo.php";

$articulo = new Articulo();

function GuardarOEditar()
{

	global $articulo;

	$idarticulo = isset($_POST["idarticulo"]) ? limpiarCadena($_POST["idarticulo"]) : null;
	$idcategoria = isset($_POST["idcategoria"]) ? limpiarCadena($_POST["idcategoria"]) : null;
	$codigo = isset($_POST["codigo"]) ? limpiarCadena($_POST["codigo"]) : null;
	$nombre = isset($_POST["nombre"]) ? limpiarCadena($_POST["nombre"]) : null;
	$descripcion = isset($_POST["descripcion"]) ? limpiarCadena($_POST["descripcion"]) : null;
	$imagen = isset($_POST["imagen"]) ? limpiarCadena($_POST["imagen"]) : null;
	$precioventa = isset($_POST["precio_venta"]) ? limpiarCadena($_POST["precio_venta"]) : null;
	$stockxtalla = isset($_POST["stockxtalla"]) ? json_decode($_POST["stockxtalla"], true) : null;

	if ($precioventa <= 0) {
		echo "El precio de venta debe ser mayor a 0.";
		exit;
	}

	if (!file_exists($_FILES['imagen']['tmp_name']) || !is_uploaded_file($_FILES['imagen']['tmp_name'])) {
		$imagen = $_POST["imagenactual"];
	} else {
		$ext = explode(".", $_FILES["imagen"]["name"]);
		if ($_FILES['imagen']['type'] == "image/jpg" || $_FILES['imagen']['type'] == "image/jpeg" || $_FILES['imagen']['type'] == "image/png") {
			$imagen = round(microtime(true)) . '.' . end($ext);
			move_uploaded_file($_FILES["imagen"]["tmp_name"], "../files/articulos/" . $imagen);
		}
	}

	if (empty($idarticulo)) {
		$rspta = $articulo->insertar($idcategoria, $codigo, $nombre, $descripcion, $imagen, $precioventa, $stockxtalla);
		echo $rspta ? "Datos registrados correctamente" : "No se pudo registrar los datos";
	} else {
		$rspta = $articulo->editar($idarticulo, $idcategoria, $codigo, $nombre, $descripcion, $imagen, $precioventa);
		echo $rspta ? "Datos actualizados correctamente" : "No se pudo actualizar los datos";
	}


}

$idarticulo = isset($_POST["idarticulo"]) ? limpiarCadena($_POST["idarticulo"]) : "";
$idcategoria = isset($_POST["idcategoria"]) ? limpiarCadena($_POST["idcategoria"]) : "";
$codigo = isset($_POST["codigo"]) ? limpiarCadena($_POST["codigo"]) : "";
$nombre = isset($_POST["nombre"]) ? limpiarCadena($_POST["nombre"]) : "";
$stock = isset($_POST["stock"]) ? limpiarCadena($_POST["stock"]) : "";
$descripcion = isset($_POST["descripcion"]) ? limpiarCadena($_POST["descripcion"]) : "";
$imagen = isset($_POST["imagen"]) ? limpiarCadena($_POST["imagen"]) : "";
$stock_s = isset($_POST["stock_s"]) ? limpiarCadena($_POST["stock_s"]) : 0;
$stock_m = isset($_POST["stock_m"]) ? limpiarCadena($_POST["stock_m"]) : 0;
$stock_l = isset($_POST["stock_l"]) ? limpiarCadena($_POST["stock_l"]) : 0;
$stock_xl = isset($_POST["stock_xl"]) ? limpiarCadena($_POST["stock_xl"]) : 0;
$precio_venta = isset($_POST["precio_venta"]) ? limpiarCadena($_POST["precio_venta"]) : 0;

switch ($_GET["op"]) {

	case 'guardaryeditar':
		GuardarOEditar();
		break;



	case 'eliminar':
		$rspta = $articulo->eliminar($idarticulo);
		echo $rspta ? "Artículo eliminado correctamente" : "No se pudo eliminar el artículo";
		break;

	case 'desactivar':
		$rspta = $articulo->desactivar($idarticulo);
		echo $rspta ? "Datos desactivados correctamente" : "No se pudo desactivar los datos";
		break;
	case 'activar':
		$rspta = $articulo->activar($idarticulo);
		echo $rspta ? "Datos activados correctamente" : "No se pudo activar los datos";
		break;

	case 'mostrar':
		$rspta = $articulo->mostrar($idarticulo);
		echo json_encode($rspta);
		break;

	case 'listar':
		$idcategoria = isset($_GET["idcategoria"]) ? limpiarCadena($_GET["idcategoria"]) : null;
		$idtalla = isset($_GET["idtalla"]) ? limpiarCadena($_GET["idtalla"]) : null;
		$condicion = isset($_GET["condicion"]) ? limpiarCadena($_GET["condicion"]) : null;

		$rspta = $articulo->listar($idcategoria, $idtalla, $condicion);
		$data = array();

		while ($reg = $rspta->fetch_object()) {
			$data[] = array(

				"0" => ($reg->condicion)
					? '<button class="btn btn-warning btn-xs" onclick="mostrar(' . $reg->idarticulo . ')"><i class="fa fa-pencil"></i></button>'
					. ' '
					. '<button class="btn btn-danger btn-xs" onclick="desactivar(' . $reg->idarticulo . ')"><i class="fa fa-close"></i></button>'
					: '<button class="btn btn-warning btn-xs" onclick="mostrar(' . $reg->idarticulo . ')"><i class="fa fa-pencil"></i></button>'
					. ' '
					. '<button class="btn btn-primary btn-xs" onclick="activar(' . $reg->idarticulo . ')"><i class="fa fa-check"></i></button>'
					. ' '
					. '<button class="btn btn-danger btn-xs" onclick="eliminar(' . $reg->idarticulo . ')"><i class="fa fa-trash"></i></button>',
				"1" => $reg->nombre,
				"2" => $reg->categoria,
				"3" => $reg->codigo,
				"4" => $reg->stock,
				"5" => "<img src='../files/articulos/" . $reg->imagen . "' height='50px' width='50px'>",
				"6" => $reg->descripcion,
				"7" => $reg->precio_venta,
				"8" => ($reg->condicion)
					? '<span class="label bg-green">Activado</span>'
					: '<span class="label bg-red">Desactivado</span>'
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

	case 'selectCategoria':

		require_once "../modelos/Categoria.php";
		$categoria = new Categoria();

		$rspta = $categoria->select();

		echo '<option value="" default>--Seleccione--</option>';
		while ($reg = $rspta->fetch_object()) {
			echo '<option value=' . $reg->idcategoria . '>' . $reg->nombre . '</option>';
		}
		break;
}
?>