<?php require_once('../Connections/produccion.php'); ?>
<?php

function buscaTexto($texto) {

$linea_borrada; 
$query_referencia="SELECT * FROM tabla_referencias WHERE id_tabla_referencia='$texto'";
$referencia = mysqli_query($mundo_connect,$query_referencia);
$row_referencia = mysqli_fetch_array($referencia);
$totalRows_referencia = mysqli_num_rows($referencia);
echo $row_referencia["referencia"];

}
?>
