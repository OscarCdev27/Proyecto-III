<?php require_once('Connections/md_connect.php'); ?>
<?php
if (!isset($_SESSION)) {
  session_start();
}
if (isset($_POST['inputEmail'])) { $loginUsername=$_POST['inputEmail']; }
if (isset($_POST['inputPassword'])) { $password=$_POST['inputPassword']; }
if($loginUsername!="" AND $password!=""){ 
  $MM_fldUserAuthorization = "nivel";
  $MM_redirectLoginSuccess = "menu_usuario.php";
  $MM_redirectLoginFailed = "401.html";
  $MM_redirecttoReferrer = false;
  $LoginRS__query="SELECT usuario, clave, nivel FROM usuario_web WHERE usuario='$loginUsername' AND clave='$password'"; 
  $LoginRS = mysqli_query($china_connect,$LoginRS__query)  or die(mysqli_error($china_connect));
  $row_LoginRS = mysqli_fetch_array($LoginRS);
  $totalRows_LoginRS = mysqli_num_rows($LoginRS);  
  if ($totalRows_LoginRS==1) {
        $datosv_query="SELECT usuario, estatus FROM suscripcion WHERE usuario='$loginUsername'"; 
        $datosv= mysqli_query($china_connect,$datosv_query)  or die(mysqli_error($mundo_connect));
        $row_datosv = mysqli_fetch_array($datosv);
		$estado=(int)$row_datosv["estatus"];
	    if($estado==1){
			$loginStrGroup  = $row_LoginRS["nivel"];
			if (PHP_VERSION >= 5.1) {session_regenerate_id(true);} else {session_regenerate_id();}
			$_SESSION['MM_Username'] = $loginUsername;
			$_SESSION['MM_UserGroup'] = $loginStrGroup;	      
			if (isset($_SESSION['PrevUrl']) && false) {
			  $MM_redirectLoginSuccess = $_SESSION['PrevUrl'];	
			}
    		print("<script>window.location.replace('$MM_redirectLoginSuccess');</script>");
		}else{
    	    if($estado!=3){
				$loginStrGroup  = $row_LoginRS["nivel"];
				if (PHP_VERSION >= 5.1) {session_regenerate_id(true);} else {session_regenerate_id();}
				$_SESSION['MM_Username'] = $loginUsername;
				$_SESSION['MM_UserGroup'] = $loginStrGroup;	      
				if (isset($_SESSION['PrevUrl']) && false) {
				  $MM_redirectLoginSuccess = $_SESSION['PrevUrl'];	
				}
			   print("<script>window.location.replace('$MM_redirectLoginSuccess');</script>");
			}else{
				$mensaje = "Usuario bloqueado..!";
				print "<script>alert('$mensaje')</script>";
				$MM_redirectLoginFailed = "401.html";
				print("<script>window.location.replace('$MM_redirectLoginFailed');</script>");		  
			}
		}
  } else {
	    print("<script>window.location.replace('$MM_redirectLoginFailed');</script>");		  
  }
} else {
	$mensaje = "Coloque un usuario y una clave..!";
    print "<script>alert('$mensaje')</script>";
    $MM_redirectLoginFailed = "401.html";
    print("<script>window.location.replace('$MM_redirectLoginFailed');</script>");		  
  }
?>
