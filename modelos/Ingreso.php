<?php
//incluir la conexion de base de datos
require "../config/Conexion.php";
class Ingreso
{


	//implementamos nuestro constructor
	public function __construct()
	{
	}

	public function insertar($idusuario, $idpersona, $fechahora, $totalcompra, $detalleingreso)
	{
		$sql = "INSERT INTO ingreso (idproveedor,idusuario,fecha_hora,total_compra,estado) VALUES ('$idpersona','$idusuario','$fechahora','$totalcompra','Aceptado')";
		$idingresonew = ejecutarConsulta_retornarID($sql);
		$sw = true;

		foreach ($detalleingreso as $item) {
			$idarticulo = $item['idarticulo'];
			$preciocompra = $item['preciocompra'];
			$cantidad = $item['cantidad'];
			$idtalla = $item['idtalla'];

			$sql_detalle = "INSERT INTO detalle_ingreso (idingreso,idarticulo,cantidad,precio_compra, idtalla) VALUES('$idingresonew','$idarticulo','$cantidad','$preciocompra','$idtalla')";
			ejecutarConsulta($sql_detalle) or $sw = false;

			$sql_actualizarstock = "UPDATE articulo_talla SET stock=stock+$cantidad WHERE idarticulo=$idarticulo AND idtalla=$idtalla";
			ejecutarConsulta($sql_actualizarstock) or $sw = false;
		}

		return $sw;
	}

	public function listar_ingre($fechadesde, $fechahasta, $idproveedor)
	{
		$sp = "sp_listar_ingresos";
		return ejecutarSP($sp, [$fechadesde, $fechahasta, $idproveedor]);
	}

	public function anular($idingreso)
	{
		date_default_timezone_set('America/Lima'); // O tu zona

		// Obtener la fecha del ingreso
		$sqlFecha = "SELECT DATE(fecha_registro) as fecha FROM ingreso WHERE idingreso='$idingreso'";
		$rsptaFecha = ejecutarConsultaSimpleFila($sqlFecha);

		$fechaIngreso = $rsptaFecha['fecha'] ?? null;
		$fechaHoy = date('Y-m-d');

		if (!$fechaIngreso) {
			return array('status' => 'error', 'message' => 'No se encontró el ingreso.');
		}

		// Si ya pasó el día, no permitir
		if ($fechaIngreso != $fechaHoy) {
			return array('status' => 'error', 'message' => '¡No se puede anular el ingreso porque no es del día actual!');
		}

		// Obtener detalles del ingreso
		$sqlDetalle = "SELECT idarticulo, idtalla, cantidad FROM detalle_ingreso WHERE idingreso='$idingreso'";
		$rsptaDetalle = ejecutarConsulta($sqlDetalle);

		// Restar el stock de cada artículo/talla
		while ($reg = $rsptaDetalle->fetch_object()) {
			$idarticulo = $reg->idarticulo;
			$idtalla = $reg->idtalla;
			$cantidad = $reg->cantidad;

			$sqlUpdateStock = "UPDATE articulo_talla SET stock = stock - $cantidad WHERE idarticulo = $idarticulo AND idtalla = $idtalla";
			ejecutarConsulta($sqlUpdateStock);
		}

		// Cambiar el estado a anulado solo si es aceptado
		$sql = "UPDATE ingreso SET estado='Anulado' WHERE idingreso='$idingreso' AND estado='Aceptado'";
		$ok = ejecutarConsulta($sql);

		if ($ok) {
			return array('status' => 'ok', 'message' => 'Ingreso anulado correctamente');
		} else {
			return array('status' => 'error', 'message' => 'No se pudo anular el ingreso');
		}
	}

	//metodo para mostrar registros
	public function mostrar($idingreso)
	{
		$sql = "SELECT i.idingreso,DATE(i.fecha_hora) as fecha,i.idproveedor,p.nombre as proveedor,u.idusuario,u.nombre as usuario, i.tipo_comprobante,i.serie_comprobante,i.num_comprobante,i.total_compra,i.impuesto,i.estado 
		FROM ingreso i
		INNER JOIN persona p ON i.idproveedor=p.idpersona 
		INNER JOIN usuario u ON i.idusuario=u.idusuario 
		WHERE idingreso='$idingreso'";
		return ejecutarConsultaSimpleFila($sql);
	}

	public function listarDetalle($idingreso)
	{
		$sql = "SELECT di.idingreso,
		di.idarticulo, t.nombre as talla, a.nombre as articulo, 
		a.imagen, di.cantidad, di.precio_compra, di.precio_venta 
		FROM detalle_ingreso di 
		INNER JOIN articulo a ON di.idarticulo=a.idarticulo 
		INNER JOIN talla t ON t.idtalla = di.idtalla  
		WHERE di.idingreso='$idingreso'";
		return ejecutarConsulta($sql);
	}

	//listar registros
	public function listar()
	{
		$sql = "SELECT i.idingreso, DATE(i.fecha_hora) as fecha, i.fecha_registro, i.idproveedor,p.nombre as proveedor,u.idusuario,u.nombre 
		as usuario, i.tipo_comprobante,i.serie_comprobante,i.num_comprobante,i.total_compra,i.impuesto,i.estado 

		FROM ingreso i 
		INNER JOIN persona p ON i.idproveedor=p.idpersona 
		INNER JOIN usuario u ON i.idusuario=u.idusuario 
		ORDER BY i.idingreso DESC";
		return ejecutarConsulta($sql);
	}
}
