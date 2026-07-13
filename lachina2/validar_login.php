<?php require_once('Connections/md_connect.php'); ?>
<?php require_once('funciones/db_init.php'); ?>
<?php
if (!isset($_SESSION)) {
  session_start();
}
if (isset($_POST['inputEmail'])) { $loginUsername=$_POST['inputEmail']; }
if (isset($_POST['inputPassword'])) { $password=$_POST['inputPassword']; }
if($loginUsername!="" AND $password!=""){ 
  $MM_fldUserAuthorization = "nivel";
  $MM_redirectLoginSuccess = "dashboard.php";
  $MM_redirectLoginFailed = "401.html";
  $MM_redirecttoReferrer = false;
  
  $LoginRS__query="SELECT usuario, clave, nivel, nombreyapellido FROM usuario_web WHERE usuario='$loginUsername' AND clave='$password'"; 
  $LoginRS = mysqli_query($china_connect,$LoginRS__query)  or die(mysqli_error($china_connect));
  $row_LoginRS = mysqli_fetch_array($LoginRS);
  $totalRows_LoginRS = mysqli_num_rows($LoginRS);  
  
  if ($totalRows_LoginRS==1) {
        $datosv_query="SELECT usuario, estatus FROM suscripcion WHERE usuario='$loginUsername'"; 
        $datosv= mysqli_query($china_connect,$datosv_query)  or die(mysqli_error($china_connect));
        $row_datosv = mysqli_fetch_array($datosv);
		$estado = $row_datosv ? (int)$row_datosv["estatus"] : 0;
		
	    if($estado==1){
			$loginStrGroup  = $row_LoginRS["nivel"];
			if (PHP_VERSION >= 5.1) {session_regenerate_id(true);} else {session_regenerate_id();}
			$_SESSION['MM_Username'] = $loginUsername;
			$_SESSION['MM_UserGroup'] = $loginStrGroup;	      
			
			// Registrar acceso exitoso y actualizar estado
			$ip = $_SERVER['REMOTE_ADDR'];
			$fecha = date("Y-m-d");
			$hora = date("H:i:s");
			$user_clean = mysqli_real_escape_string($china_connect, $loginUsername);
			$nombre_clean = mysqli_real_escape_string($china_connect, $row_LoginRS['nombreyapellido']);
			$nivel = intval($row_LoginRS['nivel']);
			
			mysqli_query($china_connect, "UPDATE usuario_web SET ip='$ip', ip_fecha='$fecha', ip_hora='$hora', ultimo='$hora', ultimo_acceso=NOW() WHERE usuario='$user_clean'");
			mysqli_query($china_connect, "INSERT INTO historial_conexiones (usuario, nombreyapellido, nivel, ip, estado) VALUES ('$user_clean', '$nombre_clean', $nivel, '$ip', 'Exitoso')");
			
    		print("<script>window.location.replace('$MM_redirectLoginSuccess');</script>");
		}else{
     	    if($estado!=3){
				$loginStrGroup  = $row_LoginRS["nivel"];
				if (PHP_VERSION >= 5.1) {session_regenerate_id(true);} else {session_regenerate_id();}
				$_SESSION['MM_Username'] = $loginUsername;
				$_SESSION['MM_UserGroup'] = $loginStrGroup;	      
				
				// Registrar acceso exitoso y actualizar estado
				$ip = $_SERVER['REMOTE_ADDR'];
				$fecha = date("Y-m-d");
				$hora = date("H:i:s");
				$user_clean = mysqli_real_escape_string($china_connect, $loginUsername);
				$nombre_clean = mysqli_real_escape_string($china_connect, $row_LoginRS['nombreyapellido']);
				$nivel = intval($row_LoginRS['nivel']);
				
				mysqli_query($china_connect, "UPDATE usuario_web SET ip='$ip', ip_fecha='$fecha', ip_hora='$hora', ultimo='$hora', ultimo_acceso=NOW() WHERE usuario='$user_clean'");
				mysqli_query($china_connect, "INSERT INTO historial_conexiones (usuario, nombreyapellido, nivel, ip, estado) VALUES ('$user_clean', '$nombre_clean', $nivel, '$ip', 'Exitoso')");
				
			   print("<script>window.location.replace('$MM_redirectLoginSuccess');</script>");
			}else{
				// Registrar acceso fallido por bloqueo
				$ip = $_SERVER['REMOTE_ADDR'];
				$user_clean = mysqli_real_escape_string($china_connect, $loginUsername);
				$nombre_clean = mysqli_real_escape_string($china_connect, $row_LoginRS['nombreyapellido']);
				$nivel = intval($row_LoginRS['nivel']);
				mysqli_query($china_connect, "INSERT INTO historial_conexiones (usuario, nombreyapellido, nivel, ip, estado) VALUES ('$user_clean', '$nombre_clean', $nivel, '$ip', 'Fallido (Usuario Bloqueado)')");
				
				$mensaje = "Usuario bloqueado..!";
				print "<script>alert('$mensaje')</script>";
				$MM_redirectLoginFailed = "401.html";
				print("<script>window.location.replace('$MM_redirectLoginFailed');</script>");		  
			}
		}
  } else {
        // Registrar acceso fallido por credenciales incorrectas
        $ip = $_SERVER['REMOTE_ADDR'];
        $user_clean = mysqli_real_escape_string($china_connect, $loginUsername);
        mysqli_query($china_connect, "INSERT INTO historial_conexiones (usuario, nombreyapellido, nivel, ip, estado) VALUES ('$user_clean', 'Desconocido', 0, '$ip', 'Fallido (Credenciales Incorrectas)')");
        
	    print("<script>window.location.replace('$MM_redirectLoginFailed');</script>");		  
  }
} else {
	$mensaje = "Coloque un usuario y una clave..!";
    print "<script>alert('$mensaje')</script>";
    $MM_redirectLoginFailed = "401.html";
    print("<script>window.location.replace('$MM_redirectLoginFailed');</script>");		  
  }
?>
