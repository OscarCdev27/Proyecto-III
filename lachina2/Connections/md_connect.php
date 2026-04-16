<?php
function mysqli_result($result, $iRow, $field = 0) { 
 if(!mysqli_data_seek($result, $iRow)) return false; 
 if(!($row = mysqli_fetch_array($result))) return false; 
 if(!array_key_exists($field, $row)) return false; 
return $row[$field]; 
} 
$hostname_mundo = "localhost";
$database_mundo = "tucasa_bd";
$username_mundo = "root_mundo";
$password_mundo = "D2wZP8WhX_PyNAKc*";
$mundo_connect = mysqli_connect($hostname_mundo,$username_mundo,$password_mundo,$database_mundo);

$hostname_webshop = "localhost";
$database_webshop = "sainvc30_bd";
$username_webshop = "root_mundo";
$password_webshop = "D2wZP8WhX_PyNAKc*";
$webshop_connect = mysqli_connect($hostname_webshop,$username_webshop,$password_webshop,$database_webshop);

$hostname_china = "localhost";
$database_china = "lachina2_bd";
$username_china = "root_mundo";
$password_china = "D2wZP8WhX_PyNAKc*";
$china_connect = mysqli_connect($hostname_china,$username_china,$password_china,$database_china);
?>
