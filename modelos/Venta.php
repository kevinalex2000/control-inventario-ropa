<?php
//incluir la conexion de base de datos
require "../config/Conexion.php";
class Venta
{


	//implementamos nuestro constructor
	public function __construct()
	{

	}

	public function insertar($idusuario, $idpersona, $fechahora, $totalventa, $idtipocancelacion, $adelanto, $detalleventa)
	{
		// Si es pago total (cancelación completa)
		if ($idtipocancelacion == 1) {
			$pagado = 1; // Completo
		} else {
			$pagado = 0;
		}

		$sql = "INSERT INTO venta (idcliente,idusuario,fecha_hora,total_venta,estado,idtipo_cancelacion,adelanto,pagado)
            VALUES ('$idpersona','$idusuario','$fechahora','$totalventa','1','$idtipocancelacion',$adelanto,$pagado)";

		$idventanew = ejecutarConsulta_retornarID($sql);
		$sw = true;

		foreach ($detalleventa as $item) {
			$idarticulo = $item['idarticulo'];
			$precioventa = $item['precioventa'];
			$cantidad = $item['cantidad'];
			$idtalla = $item['idtalla'];
			$descuento = $item['descuento'];

			$sql_detalle = "INSERT INTO detalle_venta (idventa,idarticulo,precio_venta,cantidad,idtalla,descuento)
                        VALUES ('$idventanew','$idarticulo','$precioventa','$cantidad','$idtalla','$descuento')";
			ejecutarConsulta($sql_detalle) or $sw = false;

			$sql_actualizarstock = "UPDATE articulo_talla SET stock=stock-$cantidad WHERE idarticulo=$idarticulo AND idtalla=$idtalla";
			ejecutarConsulta($sql_actualizarstock) or $sw = false;
		}

		return $sw;
	}

	public function anular($idventa)
	{
		// Cambiar estado de la venta
		$sql = "UPDATE venta SET estado='3' WHERE idventa='$idventa'";
		$anulada = ejecutarConsulta($sql);

		// Recuperar detalles y actualizar stock
		$sql_detalle = "SELECT idarticulo, idtalla, cantidad FROM detalle_venta WHERE idventa='$idventa'";
		$detalles = ejecutarConsulta($sql_detalle);
		while ($item = $detalles->fetch_object()) {
			$sql_stock = "UPDATE articulo_talla SET stock = stock + $item->cantidad WHERE idarticulo = $item->idarticulo AND idtalla = $item->idtalla";
			ejecutarConsulta($sql_stock);
		}

		return $anulada;
	}

	public function completar($idventa)
	{
		$sql = "UPDATE venta SET estado='2' WHERE idventa='$idventa'";
		return ejecutarConsulta($sql);
	}

	//implementar un metodopara mostrar los datos de unregistro a modificar
	public function mostrar($idventa)
	{
		$sql = "SELECT v.idventa,DATE(v.fecha_hora) as fecha,
			v.idcliente,p.nombre as cliente,u.idusuario,u.nombre as usuario, 
			v.tipo_comprobante,v.serie_comprobante,v.num_comprobante,v.total_venta,
			v.impuesto,v.estado,v.idtipo_cancelacion,v.adelanto, v.pagado
			FROM venta v INNER JOIN persona p ON v.idcliente=p.idpersona 
			INNER JOIN usuario u ON v.idusuario=u.idusuario 
			WHERE idventa='$idventa'";
		return ejecutarConsultaSimpleFila($sql);
	}

	public function listarDetalle($idventa)
	{
		$sql = "SELECT dv.idventa,dv.idarticulo,a.nombre,dv.cantidad,dv.precio_venta,dv.descuento,(dv.cantidad*dv.precio_venta-dv.descuento) as subtotal, 
						a.imagen,t.idtalla, t.nombre as talla
						FROM detalle_venta dv 
						INNER JOIN articulo a ON dv.idarticulo=a.idarticulo 
						INNER JOIN talla t ON dv.idtalla=t.idtalla 
						WHERE dv.idventa='$idventa'";
		return ejecutarConsulta($sql);
	}

	//listar registros
	public function listar($fecha_desde = '', $fecha_hasta = '', $idcliente = '')
	{
		$where = [];
		if ($fecha_desde) {
			$where[] = "DATE(v.fecha_hora) >= '$fecha_desde'";
		}
		if ($fecha_hasta) {
			$where[] = "DATE(v.fecha_hora) <= '$fecha_hasta'";
		}
		if ($idcliente) {
			$where[] = "v.idcliente = '$idcliente'";
		}

		$where_sql = '';
		if (count($where) > 0) {
			$where_sql = 'WHERE ' . implode(' AND ', $where);
		}

		$sql = "SELECT v.idventa,DATE(v.fecha_hora) as fecha, v.fecha_registro,v.idcliente,p.nombre as cliente, p.telefono,u.idusuario,u.nombre as usuario, 
    v.tipo_comprobante,v.serie_comprobante,v.num_comprobante,v.total_venta,v.impuesto,v.estado, v.idtipo_cancelacion, v.pagado, v.adelanto
    FROM venta v INNER JOIN persona p ON v.idcliente=p.idpersona 
    INNER JOIN usuario u ON v.idusuario=u.idusuario 
    $where_sql
    ORDER BY v.idventa DESC";
		return ejecutarConsulta($sql);
	}


	public function ventacabecera($idventa)
	{
		$sql = "SELECT v.idventa, v.idcliente, p.nombre AS cliente, p.direccion, p.tipo_documento, p.num_documento, p.email, p.telefono, v.idusuario, u.nombre AS usuario, v.tipo_comprobante, v.serie_comprobante, v.num_comprobante, DATE(v.fecha_hora) AS fecha, v.impuesto, v.total_venta FROM venta v INNER JOIN persona p ON v.idcliente=p.idpersona INNER JOIN usuario u ON v.idusuario=u.idusuario WHERE v.idventa='$idventa'";
		return ejecutarConsulta($sql);
	}

	public function ventadetalles($idventa)
	{
		$sql = "SELECT a.nombre AS articulo, a.codigo, d.cantidad, d.precio_venta, d.descuento, (d.cantidad*d.precio_venta-d.descuento) AS subtotal FROM detalle_venta d INNER JOIN articulo a ON d.idarticulo=a.idarticulo WHERE d.idventa='$idventa'";
		return ejecutarConsulta($sql);
	}

	public function Entregar($idventa)
	{
		$sql = "UPDATE venta SET estado = 2 WHERE idventa='$idventa'";
		return ejecutarConsulta($sql);
	}

	public function CompletarPago($idventa)
	{
		$sql = "UPDATE venta SET pagado = 1 WHERE idventa='$idventa'";
		echo $sql;
		return ejecutarConsulta($sql);
	}



}

?>