<?php 
session_start();

// Si ya inició sesión (por ejemplo, si hay un idusuario en la sesión)
if (isset($_SESSION['idusuario'])) {
    header('Location: vistas/escritorio.php');
} else {
    header('Location: vistas/login.html');
}
exit;
?>
