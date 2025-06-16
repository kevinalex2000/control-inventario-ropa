<?php 

require_once "global.php";

$conexion=new mysqli(DB_HOST,DB_USERNAME,DB_PASSWORD,DB_NAME);

mysqli_query($conexion, 'SET NAMES "'.DB_ENCODE.'"');

//muestra posible error en la conexion
if (mysqli_connect_errno()) {
	printf("Falló en la conexion con la base de datos: %s\n",mysqli_connect_error());
	exit();
}

if (!function_exists('ejecutarConsulta')) {
	Function ejecutarConsulta($sql){ 
		global $conexion;
		$query=$conexion->query($sql);
		return $query;
	}

	function ejecutarConsultaSimpleFila($sql){
		global $conexion;
		$query=$conexion->query($sql);
		$row=$query->fetch_assoc();
		return $row;
	}

	function ejecutarSP($spName, $params = []) {
    global $conexion;

    $placeholders = implode(',', array_fill(0, count($params), '?'));
    $sql = "CALL $spName($placeholders)";

    if ($stmt = $conexion->prepare($sql)) {

			if (count($params) > 0) {
					$types = '';
					$bindParams = [];

					foreach ($params as $key => $param) {
							// Detectar tipo de parámetro
							if (is_int($param)) {
									$types .= 'i';
							} elseif (is_float($param)) {
									$types .= 'd';
							} elseif (is_null($param)) {
									$types .= 's'; // Trata null como string
							} else {
									$types .= 's';
							}

							$bindParams[] = &$params[$key];
					}

					array_unshift($bindParams, $types);
					call_user_func_array([$stmt, 'bind_param'], $bindParams);
			}

			$stmt->execute();

			// Verificar si hay resultado del SELECT
			if ($result = $stmt->get_result()) {
					return $result;
			} else {
					// El SP no devolvió ningún SELECT
					$stmt->close();
					return null;
			}
		} 
		else {
				// Error al preparar el SP
				die("Error al preparar el SP: " . $conexion->error);
		}
	}

	function ejecutarConsulta_retornarID($sql){
		global $conexion;
		$query=$conexion->query($sql);
		return $conexion->insert_id;
	}

	function limpiarCadena($str){
		global $conexion;
		$str=mysqli_real_escape_string($conexion,trim($str));

		if($str == ''){
			return null;
		}
		
		return htmlspecialchars($str);
	}

}

?>