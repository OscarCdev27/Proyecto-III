<?php
function mysqli_result($result, $iRow, $field = 0) { 
 if(!mysqli_data_seek($result, $iRow)) return false; 
 if(!($row = mysqli_fetch_array($result))) return false; 
 if(!array_key_exists($field, $row)) return false; 
return $row[$field]; 
} 
$db_host = getenv('DB_HOST') ?: 'localhost';
$db_user = getenv('DB_USER') ?: 'root_mundo';
$db_pass = getenv('DB_PASS') !== false ? getenv('DB_PASS') : 'D2wZP8WhX_PyNAKc*';

$hostname_mundo = $db_host;
$database_mundo = "tucasa_bd";
$username_mundo = $db_user;
$password_mundo = $db_pass;
$mundo_connect = mysqli_connect($hostname_mundo,$username_mundo,$password_mundo,$database_mundo);

$hostname_webshop = $db_host;
$database_webshop = "sainvc30_bd";
$username_webshop = $db_user;
$password_webshop = $db_pass;
$webshop_connect = mysqli_connect($hostname_webshop,$username_webshop,$password_webshop,$database_webshop);

$hostname_china = $db_host;
$database_china = "lachina2_bd";
$username_china = $db_user;
$password_china = $db_pass;
$china_connect = mysqli_connect($hostname_china,$username_china,$password_china,$database_china);
?>
