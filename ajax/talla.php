<?php 
require_once "../modelos/Talla.php";

$talla = new Talla();

function listar(){
  global $talla;
  $rspta=$talla->listar();
  $data=Array();

  while ($reg = $rspta->fetch_object()) {
    $data[] = array(
      "idtalla" => $reg->idtalla,
      "nombre" => $reg->nombre
    );
  }

  return json_encode($data);
}

echo listar();

?>