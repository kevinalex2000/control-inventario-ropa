<?php 
//incluir la conexion de base de datos
require "../config/Conexion.php";
class Categoria{


	//implementamos nuestro constructor
public function __construct(){

}

//metodo insertar regiustro
public function insertar($nombre,$descripcion){
	$sql="INSERT INTO categoria (nombre,descripcion,condicion) VALUES ('$nombre','$descripcion','1')";
	return ejecutarConsulta($sql);
}

public function editar($idcategoria,$nombre,$descripcion){
	$sql="UPDATE categoria SET nombre='$nombre',descripcion='$descripcion' 
	WHERE idcategoria='$idcategoria'";
	return ejecutarConsulta($sql);
}

public function eliminar($idcategoria){
    // Verificar si existen artículos asociados a la categoría
    $sql_check = "SELECT COUNT(*) as total FROM articulo WHERE idcategoria = '$idcategoria'";
    $result = ejecutarConsultaSimpleFila($sql_check);

    if ($result['total'] > 0) {
        // Hay artículos asociados, no permitir eliminación
        return "No se puede eliminar la categoría porque tiene artículos asignados.";
    }

    // No hay artículos, proceder a eliminar
    $sql = "DELETE FROM categoria WHERE idcategoria = '$idcategoria'";
    $delete = ejecutarConsulta($sql);
    return ($delete) ? "Se elimino la categoria de manera exitosa" : "No se pudo eliminar la categoría";
}

/* public function desactivar($idcategoria){
	$sql="UPDATE categoria SET condicion='0' WHERE idcategoria='$idcategoria'";
	return ejecutarConsulta($sql);
}
public function activar($idcategoria){
	$sql="UPDATE categoria SET condicion='1' WHERE idcategoria='$idcategoria'";
	return ejecutarConsulta($sql);
}*/

//metodo para mostrar registros
public function mostrar($idcategoria){
	$sql="SELECT * FROM categoria WHERE idcategoria='$idcategoria'";
	return ejecutarConsultaSimpleFila($sql);
}

//listar registros
public function listar(){
    $sql = "SELECT c.*, 
                   (SELECT COUNT(*) FROM articulo a WHERE a.idcategoria = c.idcategoria) as cantidad_articulos
            FROM categoria c";
    return ejecutarConsulta($sql);
}
//listar y mostrar en selct
public function select(){
	$sql="SELECT * FROM categoria WHERE condicion=1";
	return ejecutarConsulta($sql);
}
}

 ?>
