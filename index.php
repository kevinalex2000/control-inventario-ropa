<?php 
session_start();

// Si ya inici� sesi�n (por ejemplo, si hay un idusuario en la sesi�n)
if (isset($_SESSION['idusuario'])) {
    header('Location: vistas/escritorio.php');
} else {
    header('Location: vistas/login.html');
}
exit;
?>
