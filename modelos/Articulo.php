<?php

//incluir la conexion de base de datos
require "../config/Conexion.php";
class Articulo
{

	//implementamos nuestro constructor
	public function __construct()
	{
	}

	//metodo insertar regiustro
	public function insertar($idcategoria, $codigo, $nombre, $descripcion, $imagen, $precioventa, $stockxtallas)
	{
		if ($idcategoria == null || $idcategoria == 0) {
			$idcategoria = 'NULL';
		}

		// Insertar el artículo principal
		$sql = "INSERT INTO articulo (idcategoria,codigo,nombre,descripcion,imagen,condicion,precio_venta)
          VALUES ($idcategoria,'$codigo','$nombre','$descripcion','$imagen','1','$precioventa')";
		$idarticulo_new = ejecutarConsulta_retornarID($sql);

		foreach ($stockxtallas as $stockTalla) {
			$idtalla = $stockTalla['idtalla'];
			$stock = $stockTalla['stock'];
			$sql_stockxtalla = "INSERT INTO articulo_talla (idarticulo, idtalla, stock, stock_inicial) VALUES ('$idarticulo_new', $idtalla, $stock, $stock)";
			ejecutarConsulta($sql_stockxtalla);
		}

		return true;
	}

	public function existeNombreOCodigo($nombre, $codigo, $idarticulo = null)
	{
		// Verificar código repetido
		$sqlCodigo = "SELECT idarticulo FROM articulo WHERE codigo='$codigo'";
		if ($idarticulo) {
			$sqlCodigo .= " AND idarticulo != '$idarticulo'";
		}
		$existeCodigo = ejecutarConsultaSimpleFila($sqlCodigo);

		// Verificar nombre repetido
		$sqlNombre = "SELECT idarticulo FROM articulo WHERE nombre='$nombre'";
		if ($idarticulo) {
			$sqlNombre .= " AND idarticulo != '$idarticulo'";
		}
		$existeNombre = ejecutarConsultaSimpleFila($sqlNombre);

		return [
			'codigo' => $existeCodigo ? true : false,
			'nombre' => $existeNombre ? true : false
		];
	}

	public function editar($idarticulo, $idcategoria, $codigo, $nombre, $descripcion, $imagen, $precioventa)
	{
		if ($idcategoria == null || $idcategoria == 0) {
			$idcategoria = 'NULL';
		}

		$sql = "UPDATE articulo SET idcategoria=$idcategoria,codigo='$codigo', nombre='$nombre',descripcion='$descripcion',imagen='$imagen', precio_venta='$precioventa' 
		WHERE idarticulo='$idarticulo'";
		return ejecutarConsulta($sql);
	}

	public function eliminar($idarticulo)
	{
		$sql = "DELETE FROM articulo WHERE idarticulo='$idarticulo'";
		return ejecutarConsulta($sql);
	}

	public function desactivar($idarticulo)
	{
		$sql = "UPDATE articulo SET condicion='0' WHERE idarticulo='$idarticulo'";
		return ejecutarConsulta($sql);
	}

	public function activar($idarticulo)
	{
		$sql = "UPDATE articulo SET condicion='1' WHERE idarticulo='$idarticulo'";
		return ejecutarConsulta($sql);
	}

	//metodo para mostrar registros
	public function mostrar($idarticulo)
	{
		$sql = "SELECT * FROM articulo WHERE idarticulo='$idarticulo'";
		$sqltalla = "SELECT art.idarticulo, tal.idtalla, tal.nombre, art.stock FROM `articulo_talla` art  join talla tal ON tal.idtalla = art.idtalla WHERE idarticulo = '$idarticulo'";
		$producto = ejecutarConsultaSimpleFila($sql);
		$stocktallas = ejecutarConsulta($sqltalla);

		$detallestock = [];
		$totalstock = 0;

		while ($fila = $stocktallas->fetch_assoc()) {
			$detallestock[] = [
				'idtalla' => $fila['idtalla'],
				'talla' => $fila['nombre'],
				'stock' => (int) $fila['stock']
			];

			$totalstock += (int) $fila['stock'];
		}

		$respuesta = [
			'idarticulo' => (int) $producto['idarticulo'],
			'idcategoria' => (int) $producto['idcategoria'],
			'codigo' => $producto['codigo'],
			'nombre' => $producto['nombre'],
			'descripcion' => $producto['descripcion'],
			'imagen' => $producto['imagen'],
			'condicion' => $producto['condicion'],
			'precioventa' => $producto['precio_venta'],
			'totalstock' => $totalstock,
			'detallestock' => $detallestock
		];

		//$producto->$tallas = $tallas;
		return $respuesta;
	}

	//listar registros 
	public function Listar($idcategoria, $idtalla, $condicion)
	{
		$resultado = array();
		$sp = "sp_listar_articulos";
		$rspta = ejecutarSP($sp, [$idcategoria, $idtalla, $condicion]);

		while ($reg = $rspta->fetch_object()) {
			$resultado[] = (object) [
				"idarticulo" => (int) $reg->idarticulo,
				"codigo" => $reg->codigo,
				"nombre" => $reg->nombre,
				"condicion" => $reg->condicion,
				"categoria" => $reg->categoria,
				"stock" => (int) $reg->stock,
				"imagen" => $reg->imagen,
				"descripcion" => $reg->descripcion,
				"precioventa" => (float) $reg->precio_venta,
			];
		}

		return $resultado;
	}

	public function ListarStockTallas($idarticulo)
	{
		$resultado = array();
		$sqltalla = "SELECT art.idarticulo, tal.idtalla, tal.nombre, art.stock, stock_inicial
									FROM `articulo_talla` art  
									JOIN talla tal ON tal.idtalla = art.idtalla 
									WHERE idarticulo = '$idarticulo'";
		$rspta = ejecutarConsulta($sqltalla);

		while ($reg = $rspta->fetch_object()) {
			$resultado[] = (object) [
				"idtalla" => (int) $reg->idtalla,
				"nombre" => $reg->nombre,
				"stock" => (int) $reg->stock,
				"stockinicial" => (int) $reg->stock_inicial,
			];
		}

		return $resultado;
	}

	//listar registros activos
	public function listarActivos()
	{
		$sql = "
			SELECT a.idarticulo,a.idcategoria,c.nombre as categoria,a.codigo, a.nombre,a.stock,a.descripcion,a.imagen,a.condicion 
			FROM articulo a 
			INNER JOIN Categoria c ON a.idcategoria=c.idcategoria WHERE a.condicion='1'";
		return ejecutarConsulta($sql);
	}

	//implementar un metodo para listar los activos, su ultimo precio y el stock(vamos a unir con el ultimo registro de la tabla detalle_ingreso)
	public function listarActivosVenta()
	{
		$sql = "SELECT a.idarticulo,a.idcategoria,c.nombre as categoria,a.codigo, a.nombre,a.stock,(SELECT precio_venta FROM detalle_ingreso WHERE idarticulo=a.idarticulo ORDER BY iddetalle_ingreso DESC LIMIT 0,1) AS precio_venta,a.descripcion,a.imagen,a.condicion FROM articulo a INNER JOIN Categoria c ON a.idcategoria=c.idcategoria WHERE a.condicion='1'";
		return ejecutarConsulta($sql);
	}
}
