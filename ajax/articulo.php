<?php
require_once "../modelos/Articulo.php";
if (strlen(session_id()) < 1)
	session_start();

$articulo = new Articulo();

function GuardarOEditar()
{
	global $articulo;

	$idusuario = $_SESSION["idusuario"];
	$idarticulo = isset($_POST["idarticulo"]) ? limpiarCadena($_POST["idarticulo"]) : null;
	$idcategoria = isset($_POST["idcategoria"]) ? limpiarCadena($_POST["idcategoria"]) : null;
	$codigo = isset($_POST["codigo"]) ? limpiarCadena($_POST["codigo"]) : null;
	$nombre = isset($_POST["nombre"]) ? limpiarCadena($_POST["nombre"]) : null;
	$descripcion = isset($_POST["descripcion"]) ? limpiarCadena($_POST["descripcion"]) : null;
	$imagen = isset($_POST["imagen"]) ? limpiarCadena($_POST["imagen"]) : null;
	$precioventa = isset($_POST["precio_venta"]) ? limpiarCadena($_POST["precio_venta"]) : null;
	$stockxtalla = isset($_POST["stockxtalla"]) ? json_decode($_POST["stockxtalla"], true) : null;
	$existe = $articulo->existeNombreOCodigo($nombre, $codigo, $idarticulo);

	if ($existe['codigo']) {
		echo "El código ya está siendo utilizado por otro artículo";
		exit;
	}

	if ($existe['nombre']) {
		echo "El nombre ya está siendo utilizado por otro artículo";
		exit;
	}

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
		$rspta = $articulo->editar($idarticulo, $idcategoria, $codigo, $nombre, $descripcion, $imagen, $precioventa, $stockxtalla, $idusuario);
		echo $rspta ? "Datos actualizados correctamente" : "No se pudo actualizar los datos";
	}
}

function Listar()
{
	global $articulo;

	$idcategoria = isset($_GET["idcategoria"]) ? limpiarCadena($_GET["idcategoria"]) : null;
	$idtalla = isset($_GET["idtalla"]) ? limpiarCadena($_GET["idtalla"]) : null;
	$condicion = isset($_GET["condicion"]) ? limpiarCadena($_GET["condicion"]) : null;

	$rspta = $articulo->listar($idcategoria, $idtalla, $condicion);
	$data = array();

	foreach ($rspta as $reg) {
		$detallestock = $articulo->ListarStockTallas($reg->idarticulo);
		$datadetallestock = array();

		foreach ($detallestock as $st) {
			$datadetallestock[] = array(
				"idtalla" => $st->idtalla,
				"talla" => $st->nombre,
				"stock" => $st->stock,
			);
		}

		$data[] = array(
			"idarticulo" => $reg->idarticulo,
			"codigo" => $reg->codigo,
			"nombre" => $reg->nombre,
			"condicion" => $reg->condicion,
			"categoria" => $reg->categoria,
			"stock" => $reg->stock,
			"imagen" => $reg->imagen,
			"descripcion" => $reg->descripcion,
			"precioventa" => $reg->precioventa,
			"detallestock" => $datadetallestock
		);
	}

	return $data;
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

	case 'validarNombreCodigo':
		$nombre = isset($_POST["nombre"]) ? limpiarCadena($_POST["nombre"]) : '';
		$codigo = isset($_POST["codigo"]) ? limpiarCadena($_POST["codigo"]) : '';
		$idarticulo = isset($_POST["idarticulo"]) ? limpiarCadena($_POST["idarticulo"]) : '';
		$existe = $articulo->existeNombreOCodigo($nombre, $codigo, $idarticulo);
		echo json_encode($existe);
		break;

	case 'eliminar':
		$rspta = $articulo->eliminar($idarticulo);
		echo $rspta ? "Artículo eliminado correctamente" : "No se pudo eliminar el artículo debido a que ya cuenta con historia.";
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
		echo json_encode(Listar());
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