<?php 
$mfecha=date("Y-m-d");
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta name="description" content="" />
        <meta name="author" content="" />
        <title>lachina2.ddns.net</title>
        <link href="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/style.min.css" rel="stylesheet" />
        <link href="css/styles.css" rel="stylesheet" />
        <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    </head>
    <body class="sb-nav-fixed" onLoad="mostrar();">

        <nav class="sb-topnav navbar navbar-expand navbar-dark bg-dark">
            <!-- Navbar Brand-->
            <a class="navbar-brand ps-3" href="index.html">LaChinaSportBook</a>
            <!-- Sidebar Toggle
            <button class="btn btn-link btn-sm order-1 order-lg-0 me-4 me-lg-0" id="sidebarToggle" href="#!"><i class="fas fa-bars"></i></button> -->
            <!-- Navbar Search-->
            <form class="d-none d-md-inline-block form-inline ms-auto me-0 me-md-3 my-2 my-md-0">
                <div class="input-group">
<!--                    <input class="form-control" type="text" placeholder="Search for..." aria-label="Search for..." aria-describedby="btnNavbarSearch" />
                    <button class="btn btn-primary" id="btnNavbarSearch" type="button"><i class="fas fa-search"></i></button> -->
                </div>
            </form>
            <!-- Navbar-->
            <ul class="navbar-nav ms-auto ms-md-0 me-3 me-lg-4">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" id="navbarDropdown" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false"><i class="fas fa-user fa-fw"></i></a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                        <li><a class="dropdown-item" href="register.html">Suscribase</a></li>
                        <li><hr class="dropdown-divider" /></li>
                        <li><a class="dropdown-item" href="login.html">Login</a></li>
                    </ul>
                </li>
            </ul>
        </nav>
        <div class="row col-sm-12">
          <div class="col-md-3 col-sm-12" style="background:#F90">
             <br><br><br>
             <div class="container">
                <nav class="sb-sidenav accordion sb-sidenav-dark" id="sidenavAccordion">
                    <div class="sb-sidenav-menu">
                        <div class="nav">
                            <div class="sb-sidenav-menu-heading"><font color="#FFFFFF" size="+1"><i>Deportes Resultados</i></font></div>
                            <a class="nav-link" onClick="cambia_deporte(1);">
                                <div class="sb-nav-link-icon"><i class="fas fa-chart-area"></i></div>
                                MLB
                            </a>
                            <a class="nav-link" onClick="cambia_deporte(2);">
                                <div class="sb-nav-link-icon"><i class="fas fa-chart-area"></i></div>
                                Basket
                            </a>
                            <a class="nav-link" onClick="cambia_deporte(3);">
                                <div class="sb-nav-link-icon"><i class="fas fa-chart-area"></i></div>
                                NFL
                            </a>
                            <a class="nav-link" onClick="cambia_deporte(4);">
                                <div class="sb-nav-link-icon"><i class="fas fa-chart-area"></i></div>
                                NHL
                            </a>
                            <a class="nav-link" onClick="cambia_deporte(5);">
                                <div class="sb-nav-link-icon"><i class="fas fa-chart-area"></i></div>
                                FUTBOL
                            </a>
                        </div>
                    </div>
                 </nav>   
              </div>
              <br>
          </div>
          <div class="col-md-6 col-sm-12">        
           <main>
              <div class="container-fluid px-4" style="height:830px; overflow-y: scroll;">
                        <br>
                        <br>
                        <br>
                        <div class="card mb-4" id="contenido"> </div>
                    </div>
                  </main>
                 </div>
                 <div class="col-md-3 col-sm-12" style="background:#F90"> 
             <br><br><br>
             <div class="container">
                    <div>
                       <div><font color="#FFFF00" size="+2"><i>Menu del SportBook</i></font></div><br>
                       <div id="contenido2"></div>
                    </div>
              </div>
              <br>
                 
                 </div>
                </div> 
                <footer class="py-4 bg-light mt-auto">
                    <div class="container-fluid px-4">
                        <div class="d-flex align-items-center justify-content-between small">
                            <div class="text-muted">Copyright &copy; Lachina2.ddns.net 2026</div>
                            <div>
                                <a href="#">Privacy Policy</a>
                                &middot;
                                <a href="#">Terms &amp; Conditions</a>
                            </div>
                        </div>
                    </div>
                </footer>
        <script src="js/jquery-1.12.4.min.js" type="text/javascript"></script>
		<script type="text/javascript" language="javascript">
            // Sistema
			function mostrar(){
 			 $("#contenido").load('resultados.php');  
			 $("#contenido2").load('rublos.php');  
			}
			
			function cambia_menu(v1){
			var parametros = {
			 "v1" : v1			 
			};
			$.get("menu_sportbook.php", {v1: v1}, function(respuesta){
			   $("#contenido").html(respuesta); }) 	   
			}
			
			function cambia_deporte(v1,v2){
			var parametros = {
			 "v1" : v1,
			 "v2" : v2			 
			};
			$.get("resultados.php", {v1: v1, v2: v2}, function(respuesta){
			   $("#contenido").html(respuesta); }) 	   
			}
        </script>     
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
        <script src="js/scripts.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/umd/simple-datatables.min.js" crossorigin="anonymous"></script>
        <script src="js/datatables-simple-demo.js"></script>
    </body>
</html>
