<?php
require_once(__DIR__ . '/../Connections/md_connect.php');

if (!$china_connect) {
    die("Error de conexión a la base de datos de La China: " . mysqli_connect_error());
}

// 1. Crear la tabla historial_conexiones si no existe
$sql_historial = "CREATE TABLE IF NOT EXISTS `historial_conexiones` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `usuario` VARCHAR(100) NOT NULL,
  `nombreyapellido` VARCHAR(100) DEFAULT NULL,
  `nivel` INT NOT NULL,
  `fecha_hora` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `ip` VARCHAR(45) DEFAULT NULL,
  `estado` VARCHAR(50) DEFAULT 'Exitoso'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

mysqli_query($china_connect, $sql_historial);

// 2. Comprobar si existe la columna ultimo_acceso en usuario_web y agregarla si no
$result_col = mysqli_query($china_connect, "SHOW COLUMNS FROM `usuario_web` LIKE 'ultimo_acceso'");
if (mysqli_num_rows($result_col) == 0) {
    $sql_alter = "ALTER TABLE `usuario_web` ADD COLUMN `ultimo_acceso` DATETIME DEFAULT NULL";
    mysqli_query($china_connect, $sql_alter);
}
?>
