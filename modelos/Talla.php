<?php

//incluir la conexion de base de datos
require "../config/Conexion.php";
class Talla
{

  //implementamos nuestro constructor
  public function __construct()
  {

  }

  public function listar(): bool|mysqli_result
  {
    $sql = "SELECT idtalla, nombre FROM talla";
    return ejecutarConsulta($sql);
  }

}

?>