<?php require_once('Connections/md_connect.php'); ?>
<?php
$midtref=$_GET["v1"];
if($midtref==0){ $midtref=2; }

$query_inventario="SELECT t1.id_fastfood, t1.codigo, t1.descripcion, t1.unidad, t1.precio1, t1.foto, t1.estatus, t2.referencia FROM fastfood t1 INNER JOIN tabla_referencias t2 ON t1.id_tabla_referencia=t2.id_tabla_referencia WHERE t1.id_tabla_referencia='$midtref' AND t1.estatus = 0 ORDER BY t1.descripcion ASC";
$inventario = mysqli_query($webshop_connect,$query_inventario);
$row_inventario = mysqli_fetch_array($inventario); 
$totalRows_inventario = mysqli_num_rows($inventario);

do { 
 $mfx=$row_inventario['foto']; 
 if($mfx=="http://sisadm250601.ddns.net/images/fotos/"){ $mfx="images/fps.jpg";} 
 echo "<a class='btn btn-secondary btn-lg me-2 mb-2'>";
 echo "<img src='http://sisadm250601.ddns.net/".$mfx."' width='120' height='140'>";
 echo "<font color='#000'><b><br>".(double)$row_inventario['precio1']." $ </b></font>";
 echo "<font color='#FFF' size='-1'><br>".$row_inventario['descripcion']."</font><br></a>";
 echo "</a>";
} while ($row_inventario = mysqli_fetch_array($inventario));

?>