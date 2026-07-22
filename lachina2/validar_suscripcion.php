<?php require_once('funciones/Secure_Encriptions.php'); ?>
<?php require_once('Connections/md_connect.php'); ?>
<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/Exception.php';
require 'PHPMailer/PHPMailer.php';
require 'PHPMailer/SMTP.php'; 

function is_valid_email($str)
{
  $matches = null;
  return (1 === preg_match('/^[A-z0-9\\._-]+@[A-z0-9][A-z0-9-]*(\\.[A-z0-9_-]+)*\\.([A-z]{2,6})$/', $str, $matches));
}

session_start();

$mnombre = mysqli_real_escape_string($china_connect, $_POST['inputFirstName']);	 
$mapellido = mysqli_real_escape_string($china_connect, $_POST['inputLastName']);	 
$memail = mysqli_real_escape_string($china_connect, $_POST['inputEmail']);	 
$musuario = mysqli_real_escape_string($china_connect, $_POST['inputUsername']);	 
$clave = mysqli_real_escape_string($china_connect, $_POST['inputPassword']);	 
$clave2 = mysqli_real_escape_string($china_connect, $_POST['inputPasswordConfirm']);

if($clave!=$clave2){
    $mensaje = "La clave no coincide..!"; 
    print "<script>alert('$mensaje')</script>";
    print "<script>javascript:history.go(-1)</script>"; 
  }else{

    if($mnombre=="" OR $mapellido=="" OR $musuario=="" OR $clave==""){
      $mensaje="Todos los campos son requeridos..!";	
      print "<script>alert('$mensaje')</script>";
      print "<script>javascript:history.go(-1)</script>"; 
    }else{
      // Validar el correo si se ingresó
      $versicorreo = 1;
      if ($memail != "") {
          $versicorreo = is_valid_email($memail);	
      }
      
      if((int)$versicorreo==0){
        $mensaje="Valide el Correo..!"; 
        print "<script>alert('$mensaje')</script>";
        print "<script>javascript:history.go(-1)</script>"; 
      }else{

          // Verificar en suscripcion
          $query_verx = "SELECT * FROM suscripcion WHERE usuario='$musuario'";
          $verx = mysqli_query($china_connect,$query_verx);
          $totalRows_verx = mysqli_num_rows($verx);

          $totalRows_verx2 = 0;
          if ($memail != "") {
              $query_verx2 = "SELECT * FROM suscripcion WHERE correo='$memail'";
              $verx2 = mysqli_query($china_connect,$query_verx2);
              $totalRows_verx2 = mysqli_num_rows($verx2);
          }

          // Verificar en usuario_web
          $query_verx3 = "SELECT * FROM usuario_web WHERE usuario='$musuario'";
          $verx3 = mysqli_query($china_connect,$query_verx3);
          $totalRows_verx3 = mysqli_num_rows($verx3);

          $totalRows_verx4 = 0;
          if ($memail != "") {
              $query_verx4 = "SELECT * FROM usuario_web WHERE correo='$memail'";
              $verx4 = mysqli_query($china_connect,$query_verx4);
              $totalRows_verx4 = mysqli_num_rows($verx4);
          }

          if($totalRows_verx==0 AND $totalRows_verx2==0 AND $totalRows_verx3==0 AND $totalRows_verx4==0){ 

          date_default_timezone_set("America/Caracas");
          $hora=date("H:i:s");
          $fecha=date("Y-m-d");

          $mv0=$musuario;
          $mv1=$memail;
          $mv2=$mnombre." ".$mapellido;
          $mv3="";
          $mv6=0; //estatus

          $insertSQL="INSERT INTO suscripcion (correo,nombre,telefono,estatus,tipo,usuario,fecha,hora)";
          $insertSQL.= "VALUES ('".$mv1."', '".$mv2."', '".$mv3."', '".$mv6."', '".$mv6."', '".$mv0."', '".$fecha."', '".$hora."' )";
          $Result1 = mysqli_query($china_connect,$insertSQL);

          $mid_suscripcion=mysqli_insert_id($china_connect);

          $vx3=$clave;
          $vx5=1;

          $query_crear= "INSERT INTO usuario_web (usuario,clave,nombreyapellido,correo,nivel)";
          $query_crear.= "VALUES ('".$mv0."', '".$vx3."', '".$mv2."', '".$mv1."', '".$vx5."' )";
          $respuesta=mysqli_query($china_connect,$query_crear);

          //************************************************************
            
          //************************************************************
          $Mfecha=date("d/m/Y");
          $Mhora=date("H:i:s");
          $text_message=" Este mensaje de correo fue enviado automaticamente por http://lachina2.ddns.net porque alguien intento crear una cuenta en este portal  usando este correo electronico."; 
          $text_message2="lachina2.ddns.net © 2026 - Todos los derechos reservados."; 
          //************************************************************ 
          $message  = "<html><body>";
          $message .= "<table width='100%' bgcolor='#e0e0e0' cellpadding='0' cellspacing='0' border='0'>";
          $message .= "<tr><td>";
          $message .= "<table align='center' width='100%' border='0' cellpadding='0' cellspacing='0' style='max-width:650px; background-color:#fff; font-family:Verdana, Geneva, sans-serif;'>";
          $message .= "<thead>
            <tr height='80'>
            <th colspan='4' style='background-color:#f5f5f5; border-bottom:solid 1px #bdbdbd; font-family:Verdana, Geneva, sans-serif; color:#333; font-size:34px;' >
             </th>
            </tr>
                       </thead>";
          $message .= "<tbody>
                <tr>
                <td colspan='4' style='padding:15px;'>
                <p style='font-size:20px;'>Hola ".$mv0.", se ha creado una cuenta en lachina2.ddns.net</p>
                <hr />
              
                <tr align='center' height='50' style='font-family:Verdana, Geneva, sans-serif;'>
                  <td colspan='4' style='background-color:#FF9900; text-align:center;'>Su Usuario: ".$mv0." y la clave: ".$vx3."</td>
              </tr>	
 
                <tr>
                <td colspan='4' style='padding:15px;' align='center'>
                 <p style='font-size:12px; font-family:Verdana, Geneva, sans-serif;'>".$text_message.".</p>
                </td>
                </tr>
                <tr>
                  <td colspan='4' style='padding:12x;' align='center'><p style='font-size:12px; font-family:Verdana, Geneva, sans-serif;'>".$text_message2.".</p></td>
                </tr>
                        </tbody>";
          $message .= "</table>";
          $message .= "</td></tr>";
          $message .= "</table>";
          $message .= "</body></html>";
          
          if ($memail != "") {
              $Mcorreoelec=$mv1;
              $mail = new PHPMailer(true);

              try {
                  $mail->SMTPDebug = 0;                      // Enable verbose debug output
                  $mail->isSMTP();                                            // Send using SMTP
                  $mail->Host       = 'smtp.gmail.com';                    // Set the SMTP server to send through
                  $mail->SMTPAuth   = true;                                   // Enable SMTP authentication
                  $mail->Username   = 'jazpimzr@gmail.com';                     // SMTP username
                  $mail->Password   = 'cvaapkytivgfyjdq';                            // SMTP password
                  $mail->SMTPSecure = 'tls';  // Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` also accepted
                  $mail->Port       = 587;                                    // TCP port to connect to
                  $mail->setFrom('jazpimzr@gmail.com', 'lachina2.ddns.net');
                  $mail->addAddress($Mcorreoelec);     // Add a recipient
                  $mail->isHTML(true);                                  // Set email format to HTML
                  $mail->Subject = 'lachina2.ddns.net le da bienvenida';
                  $mail->Body    = $message;
                  $mail->AltBody    = $message;
       //           $mail->send();
                  echo 'LaChina2.ddns.net ha enviado un Correo a Su Cuenta!'.$mv1;
                } catch (Exception $e) {
                  echo "Ocurrio un Error!. Mailer Error: {$mail->ErrorInfo}";
              }
              //************************************************************ 
              $mensaje = "lachina2.ddns.net le ha enviado un Correo..!, \n Verifique su bandeja de entrada";
              print "<script>alert('$mensaje')</script>";
          } else {
              $mensaje = "¡Cuenta creada exitosamente!";
              print "<script>alert('$mensaje')</script>";
          }
          print("<script>window.location.replace('index.php');</script>");
        }else{
          $mensaje="";	
          if($totalRows_verx>0 || $totalRows_verx3>0){ $mensaje.= "Este usuario ya esta registrado..! * "; }
          if($totalRows_verx2>0 || $totalRows_verx4>0){ $mensaje.= " Esta Cuenta Correo ya esta registrada..!"; }
          print "<script>alert('$mensaje')</script>";
          print("<script>window.location.replace('index.php');</script>");
        }
      } 
    }
}  
?>
