<?php
//incluir la conexion de base de datos
require "../config/Conexion.php";

class Consultas
{

	//implementamos nuestro constructor
	public function __construct()
	{

	}

	//listar registros
	public function comprasfecha($fecha_inicio, $fecha_fin)
	{
		$sql = "SELECT DATE(i.fecha_hora) as fecha, u.nombre as usuario, p.nombre as proveedor, i.tipo_comprobante, i.serie_comprobante, i.num_comprobante, i.total_compra,i.impuesto,i.estado FROM ingreso i INNER JOIN persona p ON i.idproveedor=p.idpersona INNER JOIN usuario u ON i.idusuario=u.idusuario WHERE DATE(i.fecha_hora)>='$fecha_inicio' AND DATE(i.fecha_hora)<='$fecha_fin'";
		return ejecutarConsulta($sql);
	}


	public function ventasfechacliente($fecha_inicio, $fecha_fin, $idcliente)
	{
		$sql = "SELECT DATE(v.fecha_hora) as fecha, u.nombre as usuario, p.nombre as cliente, v.tipo_comprobante,v.serie_comprobante, v.num_comprobante , v.total_venta, v.impuesto, v.estado FROM venta v INNER JOIN persona p ON v.idcliente=p.idpersona INNER JOIN usuario u ON v.idusuario=u.idusuario WHERE DATE(v.fecha_hora)>='$fecha_inicio' AND DATE(v.fecha_hora)<='$fecha_fin' AND v.idcliente='$idcliente'";
		return ejecutarConsulta($sql);
	}

	public function totalcomprahoy()
	{
		$sql = "SELECT IFNULL(SUM(total_compra), 0) AS total_compra
			FROM ingreso
			WHERE DATE_FORMAT(fecha_hora, '%Y-%m') = DATE_FORMAT(CURDATE(), '%Y-%m');";
		return ejecutarConsulta($sql);
	}

	public function totalventahoy()
	{
		$sql = "SELECT IFNULL(SUM(total_venta), 0) AS total_venta
						FROM venta
						WHERE MONTH(fecha_hora) = MONTH(CURDATE())
							AND YEAR(fecha_hora) = YEAR(CURDATE());";
		return ejecutarConsulta($sql);
	}

	public function comprasultimos_10dias()
	{
		$sql = " SELECT CONCAT(DAY(fecha_hora),'-',MONTH(fecha_hora)) AS fecha, SUM(total_compra) AS total FROM ingreso GROUP BY fecha_hora ORDER BY fecha_hora DESC LIMIT 0,10";
		return ejecutarConsulta($sql);
	}

	public function ventasultimos_30dias()
	{
		$sql = "SELECT DATE_FORMAT(fecha_hora, '%d %b') AS fecha, SUM(total_venta) AS total
		FROM venta
		WHERE fecha_hora >= CURDATE() - INTERVAL 30 DAY
		GROUP BY DATE(fecha_hora)
		ORDER BY fecha_hora ASC;
		";
		return ejecutarConsulta($sql);
	}

	public function ventasultimos_12meses()
	{
		$sql = " SELECT DATE_FORMAT(fecha_hora,'%M') AS fecha, SUM(total_venta) AS total FROM venta GROUP BY MONTH(fecha_hora) ORDER BY fecha_hora DESC LIMIT 0,12";
		return ejecutarConsulta($sql);
	}


}

?>