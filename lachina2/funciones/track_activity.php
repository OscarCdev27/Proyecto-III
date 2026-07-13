<?php
if (!isset($_SESSION)) {
    session_start();
}

if (isset($_SESSION['MM_Username'])) {
    require_once(__DIR__ . '/../Connections/md_connect.php');
    if ($china_connect) {
        $user = mysqli_real_escape_string($china_connect, $_SESSION['MM_Username']);
        mysqli_query($china_connect, "UPDATE usuario_web SET ultimo_acceso = NOW() WHERE usuario = '$user'");
    }
}
?>
