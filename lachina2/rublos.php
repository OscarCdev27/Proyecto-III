<?php require_once('Connections/md_connect.php'); ?>
<?php

$query_referencias="SELECT DISTINCT t1.id_tabla_referencia, t1.estatus, t2.referencia, t2.orden FROM fastfood t1 INNER JOIN tabla_referencias t2 ON t1.id_tabla_referencia=t2.id_tabla_referencia WHERE t1.estatus = 0 ORDER BY t2.orden ASC";
$referencias = mysqli_query($webshop_connect,$query_referencias);
$row_referencias = mysqli_fetch_array($referencias); 
$totalRows_referencias = mysqli_num_rows($referencias);

do { 
 $midr=$row_referencias['id_tabla_referencia'];   
 echo "<a class='btn btn-secondary btn-lg me-2 mb-2' onclick='cambia_menu(".$midr.")' >".$row_referencias["referencia"]."</a>";
} while ($row_referencias = mysqli_fetch_array($referencias)); 
 
?>