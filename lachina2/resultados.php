<?php require_once('Connections/md_connect.php'); ?>
<?php
if (!isset($_SESSION)) { 
  session_start();
}
date_default_timezone_set('America/caracas');

$fecha=$_GET['v2'];

if ($fecha==""){
  $fecha=date("Y-m-d");
  $muestrafecha=date("d-m-Y");
  $fechax=date("d/m/Y");
}else{
  $tiempo = explode ("-", $fecha);
  $xdia=$tiempo[0];
  $xmes=$tiempo[1];
  $xano=$tiempo[2];
  $fecha=$xano."-".$xmes."-".$xdia;
  $muestrafecha=$xdia."-".$xmes."-".$xano;
  $fechax=$xdia."/".$xmes."/".$xano;
  if ($fecha=="//"){
  $fecha=date("Y-m-d");
  $muestrafecha=date("d-m-Y");
  $fechax=date("d/m/Y"); }
}

$msigla=$_GET["v1"];
$msx=$_GET["v1"];
if ($msigla==""){ $mid_grupo="1"; }else{ $mid_grupo=$msigla; }

if($mid_grupo==1){ $mdeporte="Beisbol"; $mid_grupo=1; $msigla="MLB"; }
elseif($mid_grupo==2){ $mdeporte="Basket"; $mid_grupo=2; $msigla="NBA"; }
elseif($mid_grupo==3){ $mdeporte="NFL"; $mid_grupo=3; $msigla="NFL"; }
elseif($mid_grupo==4){ $mdeporte="NHL"; $mid_grupo=4; $msigla="NHL"; }
elseif($mid_grupo==5){ $mdeporte="FUTBOL"; $mid_grupo=5; $msigla="FUTBOL"; }
elseif($mndeporte==6){ $mdeporte="MAS"; $mid_grupo=6; }
$midr=$msigla;

$query_logros = "SELECT * FROM logros WHERE fecha='$fecha' AND id_grupo_logro='$mid_grupo' ORDER BY id_grupo_logro ASC, country ASC, club ASC, p DESC, hora ASC";
$logros =  mysqli_query($mundo_connect,$query_logros);
$row_logros = mysqli_fetch_array($logros);
$totalRows_logros =  mysqli_num_rows($logros);
?>
  <div class="card-header">
      <i class="fas fa-table me-1"></i>
      <?php echo $mdeporte." ".$muestrafecha; ?>
  </div>
  <div class="card-body">
    <table width="60%" align="center" id="datatablesSimple">
        <thead>
            <tr>
               <th>Hora</th>
               <th></th>
               <th>Equipo</th>
               <th>Puntos</th>
            </tr>
         </thead>
         <tfoot>
         </tfoot>
         <tbody>
		  <?php 
		    if($totalRows_logros>0){
            $mclub="";	  
            $linea=0;
            do { 
               if($linea==0){ $color="#CCCCCC"; $linea=1; }else{ $color="#FFFFFF"; $linea=0; }  ?>
          <?php if($mclub!=$row_logros['club']){ 
		     $mclub=$row_logros['club']; ?>
            <tr>
              <th colspan="4" valign="bottom" bgcolor="#CCCCCC">
                 <font color="#000000" size="+1"><i>
                 <?php echo $row_logros['country']." ".$row_logros['club']; ?></i></font>
              </th>
            </tr>
          <?php } ?>
            <tr>
              <td align="center"><?php 
				$tiempo=strtotime($row_logros['hora']);
				$formatos = array('h:i A');
				foreach($formatos as $formato)
				echo date($formato, $tiempo);				  
			  ?></td>
              <td><img src="<?php echo $row_logros['imagen2']; ?>" alt="" width="35" height="35"></td>
              <td><?php echo $row_logros['equipo1']; ?></td>
              <td align="center"><font color="#000000" size="+2"><b><?php echo (int)$row_logros['carrera1']; ?></b></font></td>
            </tr>
            <tr>
              <td align="center"></td>
              <td><img src="<?php echo $row_logros['imagen1']; ?>" alt="" width="35" height="35"></td>
              <td><?php echo $row_logros['equipo2']; ?></td>
              <td align="center"><font color="#000000" size="+2"><b><?php echo (int)$row_logros['carrera2']; ?></b></font></td>
            </tr>
            <?php } while ($row_logros = mysqli_fetch_array($logros)); } ?> 
      </tbody>
    </table>
   </div>
<?php
mysqli_free_result($logros);
?>
