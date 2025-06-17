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
	public function insertar($idcategoria, $codigo, $nombre, $descripcion, $imagen)
	{
		if ($idcategoria == null || $idcategoria == 0) {
			$idcategoria = 'NULL';
		}

		// Insertar el artículo principal
		$sql = "INSERT INTO articulo (idcategoria,codigo,nombre,descripcion,imagen,condicion)
          VALUES ($idcategoria,'$codigo','$nombre','$descripcion','$imagen','1')";
		$idarticulo_new = ejecutarConsulta_retornarID($sql);

		// Insertar stock por talla
		$sql_s = "INSERT INTO articulo_talla (idarticulo, idtalla, stock) VALUES ('$idarticulo_new', 1, '$stock_s')";
		$sql_m = "INSERT INTO articulo_talla (idarticulo, idtalla, stock) VALUES ('$idarticulo_new', 2, '$stock_m')";
		$sql_l = "INSERT INTO articulo_talla (idarticulo, idtalla, stock) VALUES ('$idarticulo_new', 3, '$stock_l')";
		$sql_xl = "INSERT INTO articulo_talla (idarticulo, idtalla, stock) VALUES ('$idarticulo_new', 4, '$stock_xl')";

		ejecutarConsulta($sql_s);
		ejecutarConsulta($sql_m);
		ejecutarConsulta($sql_l);
		ejecutarConsulta($sql_xl);

		return true;
	}

	public function editar($idarticulo, $idcategoria, $codigo, $nombre, $stock, $descripcion, $imagen)
	{
		if ($idcategoria == null || $idcategoria == 0) {
			$idcategoria = 'NULL';
		}

		$sql = "UPDATE articulo SET idcategoria=$idcategoria,codigo='$codigo', nombre='$nombre',stock='$stock',descripcion='$descripcion',imagen='$imagen' 
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
		$sqltalla = "SELECT art.idarticulo, tal.idtalla, tal.nombre as talla, art.stock FROM `articulo_talla` art  join talla tal ON tal.idtalla = art.idtalla WHERE idarticulo = '$idarticulo'";
		$producto = ejecutarConsultaSimpleFila($sql);
		$stocktallas = ejecutarConsulta($sqltalla);

		$detallestock = [];
		$totalstock = 0;

		while ($fila = $stocktallas->fetch_assoc()) {
			$detallestock[] = [
				'idtalla' => $fila['idtalla'],
				'talla' => $fila['talla'],
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
			'totalstock' => $totalstock,
			'detallestock' => $detallestock
		];

		//$producto->$tallas = $tallas;
		return $respuesta;
	}

	//listar registros 
	public function listar($idcategoria, $idtalla, $condicion)
	{
		$sp = "sp_listar_articulos";
		return ejecutarSP($sp, [$idcategoria, $idtalla, $condicion]);
	}

	//listar registros activos
	public function listarActivos()
	{
		$sql = "SELECT a.idarticulo,a.idcategoria,c.nombre as categoria,a.codigo, a.nombre,a.stock,a.descripcion,a.imagen,a.condicion FROM articulo a INNER JOIN Categoria c ON a.idcategoria=c.idcategoria WHERE a.condicion='1'";
		return ejecutarConsulta($sql);
	}

	//implementar un metodo para listar los activos, su ultimo precio y el stock(vamos a unir con el ultimo registro de la tabla detalle_ingreso)
	public function listarActivosVenta()
	{
		$sql = "SELECT a.idarticulo,a.idcategoria,c.nombre as categoria,a.codigo, a.nombre,a.stock,(SELECT precio_venta FROM detalle_ingreso WHERE idarticulo=a.idarticulo ORDER BY iddetalle_ingreso DESC LIMIT 0,1) AS precio_venta,a.descripcion,a.imagen,a.condicion FROM articulo a INNER JOIN Categoria c ON a.idcategoria=c.idcategoria WHERE a.condicion='1'";
		return ejecutarConsulta($sql);
	}
}

?>