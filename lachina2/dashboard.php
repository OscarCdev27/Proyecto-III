<?php
// 1. Iniciar sesión y validar autenticación
if (!isset($_SESSION)) {
    session_start();
}

// Redireccionar si no está autenticado
if (!isset($_SESSION['MM_Username'])) {
    header("Location: login.html");
    exit;
}

// Conectar a la base de datos
require_once('Connections/md_connect.php');
require_once('funciones/db_init.php');
require_once('funciones/track_activity.php');

// Obtener el rol real del usuario
$real_username = $_SESSION['MM_Username'];
$real_group = intval($_SESSION['MM_UserGroup']);

// Permitir simulación de roles a administradores y programadores (para testing conveniente)
$effective_group = $real_group;
if (($real_group == 2 || $real_group == 3) && isset($_GET['view_as'])) {
    $requested_view = intval($_GET['view_as']);
    if ($requested_view >= 1 && $requested_view <= 3) {
        $effective_group = $requested_view;
    }
}

// Inicializar variables de respuesta
$msg_success = "";
$msg_error = "";

// 2. Procesar Acciones de Administración (CRUD) si el rol efectivo o real es Administrador
if ($real_group == 3) {
    // A. CREAR USUARIO
    if (isset($_POST['action']) && $_POST['action'] == 'create') {
        $new_usuario = mysqli_real_escape_string($china_connect, $_POST['usuario']);
        $new_clave = mysqli_real_escape_string($china_connect, $_POST['clave']);
        $new_nombre = mysqli_real_escape_string($china_connect, $_POST['nombreyapellido']);
        $new_nivel = intval($_POST['nivel']);
        $new_status = intval($_POST['estatus'] ?? 1); // 1 = activo, 3 = bloqueado

        if (empty($new_usuario) || empty($new_clave) || empty($new_nombre)) {
            $msg_error = "Todos los campos son requeridos.";
        } else {
            // Verificar duplicados
            $chk = mysqli_query($china_connect, "SELECT id_usuario FROM usuario_web WHERE usuario='$new_usuario'");
            if (mysqli_num_rows($chk) == 0) {
                // Insertar en usuario_web
                $q1 = mysqli_query($china_connect, "INSERT INTO usuario_web (usuario, clave, nivel, nombreyapellido, correo, estatus) VALUES ('$new_usuario', '$new_clave', $new_nivel, '$new_nombre', '$new_usuario', $new_status)");
                
                // Insertar en suscripcion
                $fecha = date("Y-m-d");
                $hora = date("H:i:s");
                $q2 = mysqli_query($china_connect, "INSERT INTO suscripcion (correo, nombre, estatus, usuario, fecha, hora, tipo) VALUES ('$new_usuario', '$new_nombre', $new_status, '$new_usuario', '$fecha', '$hora', 0)");
                
                if ($q1 && $q2) {
                    $msg_success = "Usuario '$new_usuario' creado con éxito.";
                } else {
                    $msg_error = "Error al insertar en la base de datos: " . mysqli_error($china_connect);
                }
            } else {
                $msg_error = "El correo/usuario ya se encuentra registrado.";
            }
        }
    }

    // B. EDITAR USUARIO
    if (isset($_POST['action']) && $_POST['action'] == 'edit') {
        $id_usuario = intval($_POST['id_usuario']);
        $edit_usuario = mysqli_real_escape_string($china_connect, $_POST['usuario']);
        $edit_clave = mysqli_real_escape_string($china_connect, $_POST['clave']);
        $edit_nombre = mysqli_real_escape_string($china_connect, $_POST['nombreyapellido']);
        $edit_nivel = intval($_POST['nivel']);
        $edit_estatus = intval($_POST['estatus']); // 1 = activo, 3 = bloqueado

        if (empty($edit_usuario) || empty($edit_nombre) || empty($edit_clave)) {
            $msg_error = "El correo, la clave y el nombre completo son obligatorios.";
        } else {
            // Obtener el nombre de usuario anterior
            $orig_res = mysqli_query($china_connect, "SELECT usuario FROM usuario_web WHERE id_usuario = $id_usuario");
            $orig_row = mysqli_fetch_array($orig_res);
            if ($orig_row) {
                $orig_user = $orig_row['usuario'];

                // Actualizar usuario_web
                $q1 = mysqli_query($china_connect, "UPDATE usuario_web SET usuario='$edit_usuario', clave='$edit_clave', nivel=$edit_nivel, nombreyapellido='$edit_nombre', correo='$edit_usuario', estatus=$edit_estatus WHERE id_usuario = $id_usuario");
                
                // Actualizar suscripción
                $sub_chk = mysqli_query($china_connect, "SELECT id_suscripcion FROM suscripcion WHERE usuario='$orig_user'");
                if (mysqli_num_rows($sub_chk) > 0) {
                    $q2 = mysqli_query($china_connect, "UPDATE suscripcion SET usuario='$edit_usuario', correo='$edit_usuario', nombre='$edit_nombre', estatus=$edit_estatus WHERE usuario='$orig_user'");
                } else {
                    $fecha = date("Y-m-d");
                    $hora = date("H:i:s");
                    $q2 = mysqli_query($china_connect, "INSERT INTO suscripcion (correo, nombre, estatus, usuario, fecha, hora, tipo) VALUES ('$edit_usuario', '$edit_nombre', $edit_estatus, '$edit_usuario', '$fecha', '$hora', 0)");
                }

                if ($q1 && $q2) {
                    $msg_success = "Usuario actualizado correctamente.";
                } else {
                    $msg_error = "Error al actualizar en la base de datos: " . mysqli_error($china_connect);
                }
            } else {
                $msg_error = "Usuario no encontrado.";
            }
        }
    }

    // C. ELIMINAR USUARIO
    if (isset($_POST['action']) && $_POST['action'] == 'delete') {
        $id_usuario = intval($_POST['id_usuario']);
        $orig_res = mysqli_query($china_connect, "SELECT usuario FROM usuario_web WHERE id_usuario = $id_usuario");
        $orig_row = mysqli_fetch_array($orig_res);
        if ($orig_row) {
            $orig_user = $orig_row['usuario'];
            
            // No permitir auto-eliminarse
            if ($orig_user == $real_username) {
                $msg_error = "No puedes eliminar tu propio usuario administrador.";
            } else {
                $q1 = mysqli_query($china_connect, "DELETE FROM usuario_web WHERE id_usuario = $id_usuario");
                $q2 = mysqli_query($china_connect, "DELETE FROM suscripcion WHERE usuario = '$orig_user'");
                
                if ($q1 && $q2) {
                    $msg_success = "Usuario '$orig_user' eliminado con éxito.";
                } else {
                    $msg_error = "Error al eliminar: " . mysqli_error($china_connect);
                }
            }
        } else {
            $msg_error = "Usuario no encontrado.";
        }
    }
}

// 3. Consultar datos de métricas y listas para el dashboard
// Obtener información del usuario actual
$user_info_res = mysqli_query($china_connect, "SELECT * FROM usuario_web WHERE usuario='$real_username'");
$current_user_data = mysqli_fetch_array($user_info_res);

// Métricas Generales
$count_users_res = mysqli_query($china_connect, "SELECT COUNT(*) AS total FROM usuario_web");
$total_users = mysqli_fetch_array($count_users_res)['total'];

$count_online_res = mysqli_query($china_connect, "SELECT COUNT(*) AS total FROM usuario_web WHERE ultimo_acceso >= NOW() - INTERVAL 5 MINUTE");
$online_users = mysqli_fetch_array($count_online_res)['total'];

$count_logs_res = mysqli_query($china_connect, "SELECT COUNT(*) AS total FROM historial_conexiones");
$total_logs = mysqli_fetch_array($count_logs_res)['total'];

// Lista de usuarios activos ("Quién entró / En línea")
$online_list_res = mysqli_query($china_connect, "SELECT usuario, nombreyapellido, nivel, ultimo_acceso, ip FROM usuario_web WHERE ultimo_acceso >= NOW() - INTERVAL 5 MINUTE ORDER BY ultimo_acceso DESC");

// Lista de todos los usuarios (para Admin)
$all_users_res = mysqli_query($china_connect, "SELECT u.*, s.estatus AS suscripcion_estatus FROM usuario_web u LEFT JOIN suscripcion s ON u.usuario = s.usuario ORDER BY u.id_usuario DESC");

// Historial general de conexiones (para Admin / Programador)
$general_logs_res = mysqli_query($china_connect, "SELECT * FROM historial_conexiones ORDER BY id DESC LIMIT 100");

// Historial de conexiones del usuario actual (para Usuario regular)
$user_logs_res = mysqli_query($china_connect, "SELECT * FROM historial_conexiones WHERE usuario='$real_username' ORDER BY id DESC LIMIT 20");

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Control - La China SportBook</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- FontAwesome para iconos -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <style>
        :root {
            --primary: #fd7e14;
            --primary-glow: rgba(253, 126, 20, 0.4);
            --bg-dark: #0f111a;
            --card-bg: rgba(255, 255, 255, 0.05);
            --card-border: rgba(255, 255, 255, 0.08);
            --text-main: #f8f9fa;
            --text-muted: #8a8d9a;
            --success: #00e676;
            --danger: #ff1744;
            --info: #00b0ff;
            --warning: #ffea00;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Inter', sans-serif;
            transition: background-color 0.3s, border-color 0.3s;
        }

        body {
            background-color: var(--bg-dark);
            color: var(--text-main);
            min-height: 100vh;
            overflow-x: hidden;
            position: relative;
        }

        /* Bubles decorativos de fondo */
        body::before {
            content: '';
            position: absolute;
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, rgba(253, 126, 20, 0.15) 0%, rgba(0,0,0,0) 70%);
            top: -100px;
            right: -50px;
            z-index: -1;
            pointer-events: none;
        }

        body::after {
            content: '';
            position: absolute;
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, rgba(111, 66, 193, 0.12) 0%, rgba(0,0,0,0) 70%);
            bottom: -150px;
            left: -100px;
            z-index: -1;
            pointer-events: none;
        }

        /* Estructura general */
        header {
            background: rgba(15, 17, 26, 0.7);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid var(--card-border);
            position: sticky;
            top: 0;
            z-index: 100;
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo-container {
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
            color: #fff;
        }

        .logo-icon {
            font-size: 1.8rem;
            color: var(--primary);
            filter: drop-shadow(0 0 8px var(--primary-glow));
        }

        .logo-text {
            font-weight: 700;
            font-size: 1.3rem;
            letter-spacing: 0.5px;
        }

        .logo-text span {
            color: var(--primary);
        }

        .user-nav {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .role-badge {
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            padding: 4px 10px;
            border-radius: 50px;
            letter-spacing: 0.5px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .role-admin {
            background: rgba(255, 23, 68, 0.15);
            color: #ff1744;
            border: 1px solid rgba(255, 23, 68, 0.3);
        }

        .role-prog {
            background: rgba(0, 176, 255, 0.15);
            color: #00b0ff;
            border: 1px solid rgba(0, 176, 255, 0.3);
        }

        .role-user {
            background: rgba(0, 230, 118, 0.15);
            color: #00e676;
            border: 1px solid rgba(0, 230, 118, 0.3);
        }

        .user-menu-btn {
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            color: #fff;
            padding: 8px 16px;
            border-radius: 8px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            font-weight: 500;
            font-size: 0.9rem;
            text-decoration: none;
            transition: 0.2s ease;
        }

        .user-menu-btn:hover {
            background: rgba(255, 255, 255, 0.1);
            border-color: rgba(255, 255, 255, 0.2);
        }

        .logout-btn {
            background: rgba(255, 23, 68, 0.1);
            color: #ff1744;
            border: 1px solid rgba(255, 23, 68, 0.2);
        }

        .logout-btn:hover {
            background: #ff1744;
            color: #fff;
        }

        /* Contenido Principal */
        .dashboard-container {
            max-width: 1400px;
            margin: 2rem auto;
            padding: 0 1.5rem;
        }

        /* Simulador de roles */
        .testing-bar {
            background: linear-gradient(90deg, rgba(253, 126, 20, 0.15) 0%, rgba(111, 66, 193, 0.15) 100%);
            border: 1px solid rgba(253, 126, 20, 0.3);
            border-radius: 12px;
            padding: 12px 20px;
            margin-bottom: 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
        }

        .testing-title {
            font-size: 0.9rem;
            font-weight: 600;
            color: var(--primary);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .testing-options {
            display: flex;
            gap: 10px;
        }

        .testing-btn {
            background: rgba(0, 0, 0, 0.3);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: var(--text-muted);
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 0.8rem;
            font-weight: 500;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.2s;
        }

        .testing-btn.active {
            background: var(--primary);
            color: #fff;
            border-color: var(--primary);
            box-shadow: 0 0 10px var(--primary-glow);
        }

        /* Alertas */
        .alert {
            padding: 12px 20px;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .alert-success {
            background: rgba(0, 230, 118, 0.1);
            border: 1px solid rgba(0, 230, 118, 0.3);
            color: #00e676;
        }

        .alert-danger {
            background: rgba(255, 23, 68, 0.1);
            border: 1px solid rgba(255, 23, 68, 0.3);
            color: #ff1744;
        }

        /* Fila de Tarjetas de Métricas */
        .metrics-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .metric-card {
            background: var(--card-bg);
            backdrop-filter: blur(10px);
            border: 1px solid var(--card-border);
            border-radius: 16px;
            padding: 1.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: relative;
            overflow: hidden;
        }

        .metric-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            background: var(--primary);
        }

        .metric-card.m-online::before { background: var(--success); }
        .metric-card.m-logs::before { background: var(--info); }
        .metric-card.m-time::before { background: var(--warning); }

        .metric-info h3 {
            font-size: 0.85rem;
            color: var(--text-muted);
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 6px;
        }

        .metric-info p {
            font-size: 1.8rem;
            font-weight: 700;
            color: #fff;
        }

        .metric-icon {
            font-size: 2.2rem;
            opacity: 0.2;
            color: #fff;
        }

        /* Contenedores de Paneles */
        .dashboard-row {
            display: grid;
            grid-template-columns: 1fr;
            gap: 2rem;
            margin-bottom: 2rem;
        }

        @media (min-width: 1024px) {
            .dashboard-row-split {
                grid-template-columns: 2fr 1fr;
            }
        }

        .dashboard-card {
            background: var(--card-bg);
            backdrop-filter: blur(10px);
            border: 1px solid var(--card-border);
            border-radius: 16px;
            padding: 1.8rem;
            margin-bottom: 2rem;
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 0.8rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }

        .card-title {
            font-size: 1.15rem;
            font-weight: 600;
            color: #fff;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .card-title i {
            color: var(--primary);
        }

        /* Tablas */
        .table-responsive {
            overflow-x: auto;
            width: 100%;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
            font-size: 0.9rem;
        }

        th {
            padding: 12px 16px;
            color: var(--text-muted);
            font-weight: 600;
            border-bottom: 2px solid rgba(255, 255, 255, 0.05);
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.5px;
        }

        td {
            padding: 14px 16px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            color: var(--text-main);
            vertical-align: middle;
        }

        tr:hover td {
            background: rgba(255, 255, 255, 0.02);
        }

        /* Círculo online animado */
        .online-dot {
            width: 10px;
            height: 10px;
            background-color: var(--success);
            border-radius: 50%;
            display: inline-block;
            box-shadow: 0 0 8px var(--success);
            position: relative;
        }

        .online-dot-pulse {
            position: absolute;
            width: 10px;
            height: 10px;
            background-color: var(--success);
            border-radius: 50%;
            top: 0;
            left: 0;
            animation: pulse 1.8s infinite ease-in-out;
            opacity: 0.6;
        }

        @keyframes pulse {
            0% {
                transform: scale(1);
                opacity: 0.6;
            }
            100% {
                transform: scale(2.8);
                opacity: 0;
            }
        }

        .offline-dot {
            width: 10px;
            height: 10px;
            background-color: var(--text-muted);
            border-radius: 50%;
            display: inline-block;
            opacity: 0.5;
        }

        /* Formularios */
        .form-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .form-group {
            margin-bottom: 1.2rem;
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .form-group label {
            font-size: 0.8rem;
            font-weight: 600;
            color: var(--text-muted);
            text-transform: uppercase;
        }

        .form-control {
            background: rgba(0, 0, 0, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            padding: 10px 14px;
            color: #fff;
            font-size: 0.9rem;
            outline: none;
            transition: all 0.2s;
        }

        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 2px var(--primary-glow);
        }

        .btn {
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.9rem;
            cursor: pointer;
            border: none;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: all 0.2s;
        }

        .btn-primary {
            background: var(--primary);
            color: #fff;
            box-shadow: 0 4px 12px var(--primary-glow);
        }

        .btn-primary:hover {
            background: #e06c11;
            transform: translateY(-1px);
        }

        .btn-secondary {
            background: rgba(255, 255, 255, 0.1);
            color: #fff;
            border: 1px solid rgba(255, 255, 255, 0.05);
        }

        .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.15);
        }

        .btn-danger {
            background: rgba(255, 23, 68, 0.15);
            color: #ff1744;
            border: 1px solid rgba(255, 23, 68, 0.2);
        }

        .btn-danger:hover {
            background: #ff1744;
            color: #fff;
        }

        .btn-sm {
            padding: 6px 12px;
            font-size: 0.8rem;
            border-radius: 6px;
        }

        /* Buscador / Filtro */
        .search-container {
            position: relative;
            max-width: 300px;
        }

        .search-container i {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
            font-size: 0.85rem;
        }

        .search-container .form-control {
            padding-left: 35px;
            font-size: 0.85rem;
        }

        /* Grid del Panel de Usuario */
        .user-panel-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 2rem;
        }

        @media (min-width: 768px) {
            .user-panel-grid {
                grid-template-columns: 1fr 2fr;
            }
        }

        .profile-card {
            background: linear-gradient(135deg, rgba(255,255,255,0.03) 0%, rgba(255,255,255,0.01) 100%);
            border: 1px solid var(--card-border);
            border-radius: 16px;
            padding: 2rem;
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 15px;
        }

        .profile-avatar {
            width: 90px;
            height: 90px;
            background: radial-gradient(circle, var(--primary) 0%, #d85c07 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            color: #fff;
            box-shadow: 0 0 20px var(--primary-glow);
            margin-bottom: 10px;
        }

        .profile-name {
            font-size: 1.4rem;
            font-weight: 700;
        }

        .profile-email {
            color: var(--text-muted);
            font-size: 0.9rem;
            margin-top: -8px;
        }

        .access-button-card {
            background: linear-gradient(135deg, rgba(253, 126, 20, 0.15) 0%, rgba(249, 144, 0, 0.05) 100%);
            border: 1px solid rgba(253, 126, 20, 0.3);
            border-radius: 16px;
            padding: 2rem;
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 15px;
            height: 100%;
        }

        .access-button-card h2 {
            font-size: 1.5rem;
            font-weight: 700;
            color: #fff;
        }

        .access-button-card p {
            color: var(--text-muted);
            font-size: 0.95rem;
            max-width: 400px;
            margin: 0 auto;
        }

        .access-btn-large {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            background: linear-gradient(135deg, var(--primary) 0%, #e06c11 100%);
            color: #fff;
            padding: 14px 30px;
            border-radius: 12px;
            font-size: 1.1rem;
            font-weight: 700;
            text-decoration: none;
            box-shadow: 0 8px 24px var(--primary-glow);
            transition: all 0.3s;
            margin-top: 10px;
        }

        .access-btn-large:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 30px rgba(253, 126, 20, 0.6);
        }

        /* Modal / Edit Box */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.7);
            backdrop-filter: blur(5px);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 1000;
            padding: 1rem;
        }

        .modal-card {
            background: #151824;
            border: 1px solid var(--card-border);
            border-radius: 16px;
            max-width: 550px;
            width: 100%;
            padding: 2rem;
            position: relative;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.5);
        }

        .modal-close {
            position: absolute;
            top: 1.5rem;
            right: 1.5rem;
            background: none;
            border: none;
            color: var(--text-muted);
            font-size: 1.2rem;
            cursor: pointer;
        }

        .modal-close:hover {
            color: #fff;
        }

        /* Programador Estilos */
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 1rem;
        }
        
        .info-item {
            background: rgba(0,0,0,0.15);
            border: 1px solid rgba(255,255,255,0.03);
            border-radius: 10px;
            padding: 12px;
        }

        .info-label {
            font-size: 0.75rem;
            color: var(--text-muted);
            text-transform: uppercase;
            font-weight: 600;
            margin-bottom: 4px;
        }

        .info-value {
            font-size: 0.95rem;
            font-weight: 500;
            color: #fff;
        }

        .tag-status {
            font-size: 0.75rem;
            font-weight: 600;
            padding: 2px 8px;
            border-radius: 4px;
        }

        .tag-active {
            background: rgba(0, 230, 118, 0.12);
            color: var(--success);
        }

        .tag-blocked {
            background: rgba(255, 23, 68, 0.12);
            color: var(--danger);
        }

        /* Paginación simple / Scroll */
        .scrollable-panel {
            max-height: 400px;
            overflow-y: auto;
            padding-right: 5px;
        }

        .scrollable-panel::-webkit-scrollbar {
            width: 6px;
        }

        .scrollable-panel::-webkit-scrollbar-track {
            background: rgba(0, 0, 0, 0.1);
        }

        .scrollable-panel::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 3px;
        }

        .scrollable-panel::-webkit-scrollbar-thumb:hover {
            background: rgba(255, 255, 255, 0.2);
        }
    </style>
</head>
<body>

    <!-- Cabecera -->
    <header>
        <a href="dashboard.php" class="logo-container">
            <i class="fa-solid fa-gamepad logo-icon"></i>
            <span class="logo-text">LaChina<span>Sports</span></span>
        </a>
        <div class="user-nav">
            <div style="text-align: right; display: none; display: block;" class="d-none d-md-block">
                <div style="font-weight: 600; font-size: 0.9rem; color: #fff;"><?php echo htmlspecialchars($current_user_data['nombreyapellido'] ?: $real_username); ?></div>
                <div style="font-size: 0.75rem; color: var(--text-muted);"><?php echo htmlspecialchars($real_username); ?></div>
            </div>
            
            <!-- Badge de Rol -->
            <?php if ($effective_group == 3): ?>
                <span class="role-badge role-admin"><i class="fa-solid fa-shield-halved"></i> Administrador</span>
            <?php elseif ($effective_group == 2): ?>
                <span class="role-badge role-prog"><i class="fa-solid fa-code"></i> Programador</span>
            <?php else: ?>
                <span class="role-badge role-user"><i class="fa-solid fa-user"></i> Usuario</span>
            <?php endif; ?>

            <!-- Botones -->
            <a href="menu_usuario.php" class="user-menu-btn"><i class="fa-solid fa-trophy"></i> Ir al SportBook</a>
            <a href="menu_usuario.php?doLogout=true" class="user-menu-btn logout-btn"><i class="fa-solid fa-right-from-bracket"></i> Salir</a>
        </div>
    </header>

    <div class="dashboard-container">

        <!-- Barra de Simulación para Testing (Solo disponible si el rol REAL es Administrador o Programador) -->
        <?php if ($real_group == 2 || $real_group == 3): ?>
            <div class="testing-bar">
                <div class="testing-title">
                    <i class="fa-solid fa-sliders"></i>
                    Modo Desarrollo: Probar Vistas del Dashboard
                </div>
                <div class="testing-options">
                    <a href="dashboard.php?view_as=1" class="testing-btn <?php echo $effective_group == 1 ? 'active' : ''; ?>">Vista Usuario</a>
                    <a href="dashboard.php?view_as=2" class="testing-btn <?php echo $effective_group == 2 ? 'active' : ''; ?>">Vista Programador</a>
                    <a href="dashboard.php?view_as=3" class="testing-btn <?php echo $effective_group == 3 ? 'active' : ''; ?>">Vista Administrador</a>
                    <?php if ($effective_group != $real_group): ?>
                        <a href="dashboard.php" class="testing-btn" style="border-color: var(--primary); color: #fff;">Restaurar Real</a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Alertas de Éxito / Error -->
        <?php if (!empty($msg_success)): ?>
            <div class="alert alert-success">
                <i class="fa-solid fa-circle-check"></i>
                <div><?php echo $msg_success; ?></div>
            </div>
        <?php endif; ?>
        <?php if (!empty($msg_error)): ?>
            <div class="alert alert-danger">
                <i class="fa-solid fa-circle-exclamation"></i>
                <div><?php echo $msg_error; ?></div>
            </div>
        <?php endif; ?>

        <!-- METRICAS DE PANEL (Visibles para Administradores y Programadores) -->
        <?php if ($effective_group == 2 || $effective_group == 3): ?>
            <div class="metrics-grid">
                <div class="metric-card">
                    <div class="metric-info">
                        <h3>Usuarios Registrados</h3>
                        <p><?php echo $total_users; ?></p>
                    </div>
                    <i class="fa-solid fa-users metric-icon"></i>
                </div>
                <div class="metric-card m-online">
                    <div class="metric-info">
                        <h3>En Línea Ahora</h3>
                        <p><?php echo $online_users; ?></p>
                    </div>
                    <i class="fa-solid fa-signal metric-icon"></i>
                </div>
                <div class="metric-card m-logs">
                    <div class="metric-info">
                        <h3>Accesos Totales</h3>
                        <p><?php echo $total_logs; ?></p>
                    </div>
                    <i class="fa-solid fa-list-check metric-icon"></i>
                </div>
                <div class="metric-card m-time">
                    <div class="metric-info">
                        <h3>Hora Servidor</h3>
                        <p id="live-clock"><?php echo date("H:i:s"); ?></p>
                    </div>
                    <i class="fa-solid fa-clock metric-icon"></i>
                </div>
            </div>
        <?php endif; ?>

        <!-- ============================================== -->
        <!-- 1. DASHBOARD DE ADMINISTRADOR                 -->
        <!-- ============================================== -->
        <?php if ($effective_group == 3): ?>
            
            <div class="dashboard-row dashboard-row-split" style="display: grid;">
                
                <!-- Panel Central: Gestión de Usuarios -->
                <div class="dashboard-card">
                    <div class="card-header">
                        <div class="card-title">
                            <i class="fa-solid fa-users-gear"></i>
                            Gestión y Registro de Usuarios
                        </div>
                        <div style="display: flex; gap: 10px; align-items: center;">
                            <div class="search-container">
                                <i class="fa-solid fa-magnifying-glass"></i>
                                <input type="text" id="userSearch" class="form-control" placeholder="Buscar usuario..." onkeyup="filterUsers()">
                            </div>
                            <button class="btn btn-primary btn-sm" onclick="openCreateModal()"><i class="fa-solid fa-user-plus"></i> Registrar</button>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table id="usersTable">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nombre Completo</th>
                                    <th>Usuario / Correo</th>
                                    <th>Rol</th>
                                    <th>Estatus</th>
                                    <th>Último Acceso</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                mysqli_data_seek($all_users_res, 0);
                                while ($row = mysqli_fetch_array($all_users_res)): 
                                    $lvl = intval($row['nivel']);
                                    $status = intval($row['suscripcion_estatus']);
                                    
                                    // Comprobar si está en línea (actividad en últimos 5 min)
                                    $is_online = false;
                                    if ($row['ultimo_acceso']) {
                                        $last_time = strtotime($row['ultimo_acceso']);
                                        $diff = time() - $last_time;
                                        if ($diff <= 300) {
                                            $is_online = true;
                                        }
                                    }
                                ?>
                                    <tr>
                                        <td><?php echo $row['id_usuario']; ?></td>
                                        <td style="font-weight: 500;"><?php echo htmlspecialchars($row['nombreyapellido']); ?></td>
                                        <td><?php echo htmlspecialchars($row['usuario']); ?></td>
                                        <td>
                                            <?php if ($lvl == 3): ?>
                                                <span class="role-badge role-admin" style="font-size:0.65rem; display:inline-flex;">Admin</span>
                                            <?php elseif ($lvl == 2): ?>
                                                <span class="role-badge role-prog" style="font-size:0.65rem; display:inline-flex;">Prog</span>
                                            <?php else: ?>
                                                <span class="role-badge role-user" style="font-size:0.65rem; display:inline-flex;">Usuario</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($status == 3): ?>
                                                <span class="tag-status tag-blocked">Bloqueado</span>
                                            <?php else: ?>
                                                <span class="tag-status tag-active">Activo</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div style="display: flex; align-items: center; gap: 8px;">
                                                <?php if ($is_online): ?>
                                                    <span class="online-dot"><span class="online-dot-pulse"></span></span>
                                                    <span style="font-size: 0.8rem; color: var(--success); font-weight: 500;">En línea</span>
                                                <?php else: ?>
                                                    <span class="offline-dot"></span>
                                                    <span style="color: var(--text-muted); font-size: 0.8rem;">
                                                        <?php echo $row['ultimo_acceso'] ? date("d/m H:i", strtotime($row['ultimo_acceso'])) : 'Nunca'; ?>
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td>
                                            <div style="display: flex; gap: 6px;">
                                                <button class="btn btn-secondary btn-sm" 
                                                        onclick="openEditModal(
                                                            '<?php echo $row['id_usuario']; ?>',
                                                            '<?php echo htmlspecialchars($row['usuario'], ENT_QUOTES); ?>',
                                                            '<?php echo htmlspecialchars($row['clave'], ENT_QUOTES); ?>',
                                                            '<?php echo htmlspecialchars($row['nombreyapellido'], ENT_QUOTES); ?>',
                                                            '<?php echo $row['nivel']; ?>',
                                                            '<?php echo ($status == 3) ? 3 : 1; ?>'
                                                        )">
                                                    <i class="fa-solid fa-pen-to-square"></i>
                                                </button>
                                                <?php if ($row['usuario'] != $real_username): ?>
                                                    <form method="POST" onsubmit="return confirm('¿Estás seguro de eliminar este usuario por completo?');" style="display:inline;">
                                                        <input type="hidden" name="action" value="delete">
                                                        <input type="hidden" name="id_usuario" value="<?php echo $row['id_usuario']; ?>">
                                                        <button type="submit" class="btn btn-danger btn-sm"><i class="fa-solid fa-trash-can"></i></button>
                                                    </form>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Segunda Fila Separada: Quién Entró y Log de Conexiones -->
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 2rem;">
                    
                    <!-- Panel "¿Quién entró?" (Monitoreo En Vivo) -->
                    <div class="dashboard-card">
                        <div class="card-header">
                            <div class="card-title">
                                <i class="fa-solid fa-signal" style="color: var(--success);"></i>
                                Panel "Quién Entró" (En Línea)
                            </div>
                            <span class="role-badge role-user" style="background: rgba(0, 230, 118, 0.1); color: var(--success);"><span class="online-dot"><span class="online-dot-pulse"></span></span> <?php echo $online_users; ?> Activos</span>
                        </div>
                        <div class="scrollable-panel">
                            <div style="display: flex; flex-direction: column; gap: 12px;">
                                <?php 
                                $has_online = false;
                                mysqli_data_seek($online_list_res, 0);
                                while ($row = mysqli_fetch_array($online_list_res)): 
                                    $has_online = true;
                                    $lvl = intval($row['nivel']);
                                ?>
                                    <div style="background: rgba(255,255,255,0.02); border: 1px solid var(--card-border); border-radius: 10px; padding: 12px; display: flex; justify-content: space-between; align-items: center;">
                                        <div>
                                            <div style="display: flex; align-items: center; gap: 8px;">
                                                <span class="online-dot"><span class="online-dot-pulse"></span></span>
                                                <span style="font-weight: 600; font-size: 0.9rem;"><?php echo htmlspecialchars($row['nombreyapellido'] ?: $row['usuario']); ?></span>
                                            </div>
                                            <div style="font-size: 0.75rem; color: var(--text-muted); padding-left: 18px;">
                                                <?php echo htmlspecialchars($row['usuario']); ?> &bull; IP: <?php echo htmlspecialchars($row['ip']); ?>
                                            </div>
                                        </div>
                                        <div>
                                            <?php if ($lvl == 3): ?>
                                                <span class="role-badge role-admin" style="font-size:0.6rem; padding: 2px 6px;">Admin</span>
                                            <?php elseif ($lvl == 2): ?>
                                                <span class="role-badge role-prog" style="font-size:0.6rem; padding: 2px 6px;">Prog</span>
                                            <?php else: ?>
                                                <span class="role-badge role-user" style="font-size:0.6rem; padding: 2px 6px;">User</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                                <?php if (!$has_online): ?>
                                    <div style="text-align: center; padding: 2rem; color: var(--text-muted); font-size: 0.9rem;">
                                        <i class="fa-regular fa-face-frown" style="font-size: 1.8rem; margin-bottom: 8px; display: block;"></i>
                                        Nadie en línea en los últimos 5 minutos.
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Panel Historial Completo de Conexiones -->
                    <div class="dashboard-card">
                        <div class="card-header">
                            <div class="card-title">
                                <i class="fa-solid fa-clock-rotate-left" style="color: var(--info);"></i>
                                Historial de Accesos Recientes
                            </div>
                            <div class="search-container">
                                <i class="fa-solid fa-magnifying-glass"></i>
                                <input type="text" id="logSearch" class="form-control" placeholder="Filtrar logs..." onkeyup="filterLogs()">
                            </div>
                        </div>
                        <div class="scrollable-panel">
                            <table id="logsTable" style="font-size: 0.8rem;">
                                <thead>
                                    <tr>
                                        <th>Usuario</th>
                                        <th>Fecha / Hora</th>
                                        <th>IP</th>
                                        <th>Estado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($log = mysqli_fetch_array($general_logs_res)): 
                                        $is_success = (strpos($log['estado'], 'Exitoso') !== false);
                                    ?>
                                        <tr>
                                            <td style="font-weight: 500;">
                                                <?php echo htmlspecialchars($log['usuario']); ?>
                                                <div style="font-size: 0.7rem; color: var(--text-muted);"><?php echo htmlspecialchars($log['nombreyapellido']); ?></div>
                                            </td>
                                            <td><?php echo date("d/m/Y H:i:s", strtotime($log['fecha_hora'])); ?></td>
                                            <td><?php echo htmlspecialchars($log['ip']); ?></td>
                                            <td>
                                                <?php if ($is_success): ?>
                                                    <span style="color: var(--success); font-weight: 500;"><i class="fa-solid fa-circle-check"></i> Exitoso</span>
                                                <?php else: ?>
                                                    <span style="color: var(--danger); font-weight: 500;" title="<?php echo htmlspecialchars($log['estado']); ?>"><i class="fa-solid fa-circle-xmark"></i> Falló</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>

            </div>

        <!-- ============================================== -->
        <!-- 2. DASHBOARD DE PROGRAMADOR                   -->
        <!-- ============================================== -->
        <?php elseif ($effective_group == 2): ?>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
                
                <!-- Panel del Servidor y Entorno -->
                <div class="dashboard-card">
                    <div class="card-header">
                        <div class="card-title">
                            <i class="fa-solid fa-server" style="color: var(--info);"></i>
                            Estado del Servidor y Entorno
                        </div>
                    </div>
                    <div class="info-grid">
                        <div class="info-item">
                            <div class="info-label">Sistema Operativo</div>
                            <div class="info-value"><?php echo php_uname('s') . ' ' . php_uname('r'); ?></div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Versión PHP</div>
                            <div class="info-value"><?php echo PHP_VERSION; ?></div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Software Web</div>
                            <div class="info-value"><?php echo $_SERVER['SERVER_SOFTWARE']; ?></div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Límite de Memoria PHP</div>
                            <div class="info-value"><?php echo ini_get('memory_limit'); ?></div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Tiempo Máx. Ejecución</div>
                            <div class="info-value"><?php echo ini_get('max_execution_time') . 's'; ?></div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Zona Horaria</div>
                            <div class="info-value"><?php echo date_default_timezone_get(); ?></div>
                        </div>
                    </div>
                    
                    <div style="margin-top: 1.5rem; background: rgba(0,0,0,0.2); padding: 15px; border-radius: 10px; border: 1px solid var(--card-border);">
                        <div style="font-size:0.85rem; font-weight:600; margin-bottom:8px;"><i class="fa-solid fa-circle-nodes" style="color:var(--info);"></i> Conexión a Base de Datos</div>
                        <div style="display:flex; justify-content:space-between; font-size:0.8rem; margin-bottom:4px;">
                            <span style="color:var(--text-muted);">Servidor Host:</span>
                            <span><?php echo DB_HOST; ?></span>
                        </div>
                        <div style="display:flex; justify-content:space-between; font-size:0.8rem; margin-bottom:4px;">
                            <span style="color:var(--text-muted);">Usuario DB:</span>
                            <span><?php echo DB_USER; ?></span>
                        </div>
                        <div style="display:flex; justify-content:space-between; font-size:0.8rem;">
                            <span style="color:var(--text-muted);">Motor de Conexión:</span>
                            <span style="color:var(--success); font-weight:500;">MySQLi (Activo)</span>
                        </div>
                    </div>
                </div>

                <!-- Explorador de Tablas (DB Schema Info) -->
                <div class="dashboard-card">
                    <div class="card-header">
                        <div class="card-title">
                            <i class="fa-solid fa-database" style="color: var(--warning);"></i>
                            Estructura de la Base de Datos (tucasa_bd / lachina2_bd)
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th>Nombre Tabla</th>
                                    <th>Registros</th>
                                    <th>Motor</th>
                                    <th>Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $tables = ['suscripcion', 'usuario_web', 'historial_conexiones'];
                                foreach ($tables as $tbl) {
                                    $cnt_res = mysqli_query($china_connect, "SELECT COUNT(*) AS total FROM `$tbl`");
                                    $cnt = $cnt_res ? mysqli_fetch_array($cnt_res)['total'] : 'Error';
                                    
                                    $status_res = mysqli_query($china_connect, "SHOW TABLE STATUS LIKE '$tbl'");
                                    $status_data = $status_res ? mysqli_fetch_array($status_res) : null;
                                    $engine = $status_data ? $status_data['Engine'] : 'N/D';
                                ?>
                                    <tr>
                                        <td style="font-family: monospace; font-weight: 600; color: var(--info);"><?php echo $tbl; ?></td>
                                        <td><?php echo $cnt; ?></td>
                                        <td><span class="tag-status" style="background:rgba(255,255,255,0.05);"><?php echo $engine; ?></span></td>
                                        <td><button class="btn btn-secondary btn-sm" style="padding: 3px 8px; font-size:0.75rem;" onclick="alert('Explorando tabla: <?php echo $tbl; ?>. Funcionalidad de desarrollo simulada.')"><i class="fa-solid fa-code-branch"></i> Estructura</button></td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Historial de Accesos Completo (Para auditoría del Programador) -->
                <div class="dashboard-card" style="grid-column: span 2;">
                    <div class="card-header">
                        <div class="card-title">
                            <i class="fa-solid fa-terminal"></i>
                            Historial General de Logs del Sistema (Consola del Programador)
                        </div>
                        <div class="search-container">
                            <i class="fa-solid fa-magnifying-glass"></i>
                            <input type="text" id="logSearchProg" class="form-control" placeholder="Buscar logs..." onkeyup="filterProgLogs()">
                        </div>
                    </div>
                    <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                        <table id="progLogsTable">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Usuario</th>
                                    <th>Nombre Registrado</th>
                                    <th>Rol</th>
                                    <th>Fecha y Hora</th>
                                    <th>Dirección IP</th>
                                    <th>Resultado Acceso</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                mysqli_data_seek($general_logs_res, 0);
                                while ($log = mysqli_fetch_array($general_logs_res)): 
                                    $lvl = intval($log['nivel']);
                                    $is_success = (strpos($log['estado'], 'Exitoso') !== false);
                                ?>
                                    <tr>
                                        <td>#<?php echo $log['id']; ?></td>
                                        <td style="font-family: monospace;"><?php echo htmlspecialchars($log['usuario']); ?></td>
                                        <td><?php echo htmlspecialchars($log['nombreyapellido']); ?></td>
                                        <td>
                                            <?php if ($lvl == 3): ?>
                                                <span class="role-badge role-admin" style="font-size:0.6rem; padding: 2px 6px; display:inline-flex;">Admin</span>
                                            <?php elseif ($lvl == 2): ?>
                                                <span class="role-badge role-prog" style="font-size:0.6rem; padding: 2px 6px; display:inline-flex;">Prog</span>
                                            <?php elseif ($lvl == 1): ?>
                                                <span class="role-badge role-user" style="font-size:0.6rem; padding: 2px 6px; display:inline-flex;">User</span>
                                            <?php else: ?>
                                                <span class="role-badge" style="font-size:0.6rem; padding: 2px 6px; display:inline-flex; background:rgba(255,255,255,0.05); color:#fff;">Ninguno</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo date("Y-m-d H:i:s", strtotime($log['fecha_hora'])); ?></td>
                                        <td style="font-family: monospace;"><?php echo htmlspecialchars($log['ip']); ?></td>
                                        <td>
                                            <?php if ($is_success): ?>
                                                <span style="color: var(--success); font-weight: 600;"><i class="fa-solid fa-circle-check"></i> <?php echo htmlspecialchars($log['estado']); ?></span>
                                            <?php else: ?>
                                                <span style="color: var(--danger); font-weight: 600;"><i class="fa-solid fa-circle-xmark"></i> <?php echo htmlspecialchars($log['estado']); ?></span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>

        <!-- ============================================== -->
        <!-- 3. DASHBOARD DE USUARIO                       -->
        <!-- ============================================== -->
        <?php else: ?>
            
            <div class="user-panel-grid">
                
                <!-- Columna Izquierda: Perfil y Datos Personales -->
                <div style="display: flex; flex-direction: column; gap: 2rem;">
                    
                    <div class="profile-card">
                        <div class="profile-avatar">
                            <i class="fa-solid fa-user-astronaut"></i>
                        </div>
                        <div class="profile-name"><?php echo htmlspecialchars($current_user_data['nombreyapellido'] ?: 'Usuario General'); ?></div>
                        <div class="profile-email"><?php echo htmlspecialchars($real_username); ?></div>
                        
                        <div style="width: 100%; border-top: 1px solid rgba(255,255,255,0.08); margin: 10px 0; padding-top: 15px; text-align: left;">
                            <div style="display:flex; justify-content:space-between; font-size:0.8rem; margin-bottom:8px;">
                                <span style="color:var(--text-muted);">Nivel de Acceso:</span>
                                <span style="color:var(--success); font-weight:600;">Usuario Suscrito</span>
                            </div>
                            <div style="display:flex; justify-content:space-between; font-size:0.8rem; margin-bottom:8px;">
                                <span style="color:var(--text-muted);">Última IP registrada:</span>
                                <span style="font-family:monospace;"><?php echo htmlspecialchars($current_user_data['ip'] ?: 'Desconocida'); ?></span>
                            </div>
                            <div style="display:flex; justify-content:space-between; font-size:0.8rem;">
                                <span style="color:var(--text-muted);">Último Ingreso:</span>
                                <span><?php echo $current_user_data['ip_fecha'] . ' ' . $current_user_data['ip_hora']; ?></span>
                            </div>
                        </div>
                    </div>

                    <!-- Enlace Rápido al SportBook -->
                    <div class="access-button-card">
                        <i class="fa-solid fa-trophy" style="font-size: 2.8rem; color: var(--primary); filter: drop-shadow(0 0 10px var(--primary-glow));"></i>
                        <h2>SportBook La China</h2>
                        <p>Accede de inmediato a los resultados y estadísticas actualizadas de la MLB, NFL, NHL, Basket y Fútbol.</p>
                        <a href="menu_usuario.php" class="access-btn-large">
                            <i class="fa-solid fa-circle-play"></i>
                            Entrar al SportBook
                        </a>
                    </div>

                </div>

                <!-- Columna Derecha: Mi Historial de Conexiones (Seguridad de Cuenta) -->
                <div class="dashboard-card">
                    <div class="card-header">
                        <div class="card-title">
                            <i class="fa-solid fa-shield-halved" style="color: var(--success);"></i>
                            Historial de Seguridad y Accesos de mi Cuenta
                        </div>
                    </div>
                    <p style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 1.5rem;">
                        Verifica el registro detallado de las conexiones a tu cuenta. Si notas accesos desde IPs desconocidas, te sugerimos contactar al administrador.
                    </p>

                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th>Fecha y Hora de Acceso</th>
                                    <th>Dirección IP</th>
                                    <th>Estatus Conexión</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $has_user_logs = false;
                                while ($log = mysqli_fetch_array($user_logs_res)): 
                                    $has_user_logs = true;
                                    $is_success = (strpos($log['estado'], 'Exitoso') !== false);
                                ?>
                                    <tr>
                                        <td style="font-weight: 500;"><?php echo date("d/m/Y g:i A", strtotime($log['fecha_hora'])); ?></td>
                                        <td style="font-family: monospace;"><?php echo htmlspecialchars($log['ip']); ?></td>
                                        <td>
                                            <?php if ($is_success): ?>
                                                <span style="color: var(--success); font-weight: 600;"><i class="fa-solid fa-circle-check"></i> Conexión Correcta</span>
                                            <?php else: ?>
                                                <span style="color: var(--danger); font-weight: 600;"><i class="fa-solid fa-circle-xmark"></i> <?php echo htmlspecialchars($log['estado']); ?></span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                                <?php if (!$has_user_logs): ?>
                                    <tr>
                                        <td colspan="3" style="text-align: center; padding: 2rem; color: var(--text-muted);">
                                            No se encontraron registros de accesos previos.
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>

        <?php endif; ?>

    </div>

    <!-- ============================================== -->
    <!-- MODAL DE CREACIÓN DE USUARIO (ADMIN)           -->
    <!-- ============================================== -->
    <div id="createModal" class="modal-overlay">
        <div class="modal-card">
            <button class="modal-close" onclick="closeCreateModal()"><i class="fa-solid fa-xmark"></i></button>
            <h3 style="margin-bottom: 1.5rem; display: flex; align-items: center; gap: 8px;"><i class="fa-solid fa-user-plus" style="color: var(--primary);"></i> Registrar Nuevo Usuario</h3>
            
            <form method="POST">
                <input type="hidden" name="action" value="create">
                
                <div class="form-group">
                    <label for="c_nombre">Nombre y Apellido</label>
                    <input type="text" id="c_nombre" name="nombreyapellido" class="form-control" placeholder="Ej: Juan Pérez" required>
                </div>
                
                <div class="form-group">
                    <label for="c_usuario">Usuario / Correo Electrónico</label>
                    <input type="email" id="c_usuario" name="usuario" class="form-control" placeholder="Ej: juan@gmail.com" required>
                </div>

                <div class="form-group">
                    <label for="c_clave">Contraseña</label>
                    <input type="text" id="c_clave" name="clave" class="form-control" placeholder="Clave de ingreso" required>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="c_nivel">Rol / Nivel de Acceso</label>
                        <select id="c_nivel" name="nivel" class="form-control" required>
                            <option value="1">Usuario Regular</option>
                            <option value="2">Programador</option>
                            <option value="3">Administrador</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="c_estatus">Estado</label>
                        <select id="c_estatus" name="estatus" class="form-control" required>
                            <option value="1">Activo</option>
                            <option value="3">Bloqueado</option>
                        </select>
                    </div>
                </div>

                <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 1.5rem;">
                    <button type="button" class="btn btn-secondary" onclick="closeCreateModal()">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Registrar Usuario</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ============================================== -->
    <!-- MODAL DE EDICIÓN DE USUARIO (ADMIN)             -->
    <!-- ============================================== -->
    <div id="editModal" class="modal-overlay">
        <div class="modal-card">
            <button class="modal-close" onclick="closeEditModal()"><i class="fa-solid fa-xmark"></i></button>
            <h3 style="margin-bottom: 1.5rem; display: flex; align-items: center; gap: 8px;"><i class="fa-solid fa-user-pen" style="color: var(--primary);"></i> Editar Usuario</h3>
            
            <form method="POST">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" id="e_id_usuario" name="id_usuario">
                
                <div class="form-group">
                    <label for="e_nombre">Nombre y Apellido</label>
                    <input type="text" id="e_nombre" name="nombreyapellido" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label for="e_usuario">Usuario / Correo Electrónico</label>
                    <input type="email" id="e_usuario" name="usuario" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="e_clave">Contraseña</label>
                    <input type="text" id="e_clave" name="clave" class="form-control" required>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="e_nivel">Rol / Nivel de Acceso</label>
                        <select id="e_nivel" name="nivel" class="form-control" required>
                            <option value="1">Usuario Regular</option>
                            <option value="2">Programador</option>
                            <option value="3">Administrador</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="e_estatus">Estado</label>
                        <select id="e_estatus" name="estatus" class="form-control" required>
                            <option value="1">Activo</option>
                            <option value="3">Bloqueado</option>
                        </select>
                    </div>
                </div>

                <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 1.5rem;">
                    <button type="button" class="btn btn-secondary" onclick="closeEditModal()">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Pie de página -->
    <footer style="margin-top: 4rem; padding: 2rem; border-top: 1px solid var(--card-border); text-align: center; font-size: 0.8rem; color: var(--text-muted);">
        <div>Copyright &copy; LaChinaSportBook 2026. Todos los derechos reservados.</div>
        <div style="margin-top: 5px;">Dashboard Premium optimizado para múltiples roles.</div>
    </footer>

    <!-- Scripts JavaScript -->
    <script>
        // 1. Reloj en vivo para hora del servidor
        setInterval(() => {
            const clock = document.getElementById('live-clock');
            if (clock) {
                const date = new Date();
                let h = date.getHours().toString().padStart(2, '0');
                let m = date.getMinutes().toString().padStart(2, '0');
                let s = date.getSeconds().toString().padStart(2, '0');
                clock.textContent = `${h}:${m}:${s}`;
            }
        }, 1000);

        // 2. Modales de Creación
        function openCreateModal() {
            document.getElementById('createModal').style.display = 'flex';
        }
        function closeCreateModal() {
            document.getElementById('createModal').style.display = 'none';
        }

        // 3. Modales de Edición
        function openEditModal(id, usuario, clave, nombre, nivel, estatus) {
            document.getElementById('e_id_usuario').value = id;
            document.getElementById('e_usuario').value = usuario;
            document.getElementById('e_clave').value = clave;
            document.getElementById('e_nombre').value = nombre;
            document.getElementById('e_nivel').value = nivel;
            document.getElementById('e_estatus').value = estatus;
            document.getElementById('editModal').style.display = 'flex';
        }
        function closeEditModal() {
            document.getElementById('editModal').style.display = 'none';
        }

        // Cerrar modales al hacer clic fuera del contenido
        window.onclick = function(event) {
            const createModal = document.getElementById('createModal');
            const editModal = document.getElementById('editModal');
            if (event.target == createModal) {
                closeCreateModal();
            }
            if (event.target == editModal) {
                closeEditModal();
            }
        }

        // 4. Buscador en tiempo real de usuarios
        function filterUsers() {
            const input = document.getElementById("userSearch");
            const filter = input.value.toLowerCase();
            const table = document.getElementById("usersTable");
            const tr = table.getElementsByTagName("tr");

            for (let i = 1; i < tr.length; i++) {
                let show = false;
                const tds = tr[i].getElementsByTagName("td");
                for (let j = 0; j < tds.length - 1; j++) {
                    if (tds[j]) {
                        const txtValue = tds[j].textContent || tds[j].innerText;
                        if (txtValue.toLowerCase().indexOf(filter) > -1) {
                            show = true;
                            break;
                        }
                    }
                }
                tr[i].style.display = show ? "" : "none";
            }
        }

        // 5. Buscador en tiempo real de logs (Admin)
        function filterLogs() {
            const input = document.getElementById("logSearch");
            const filter = input.value.toLowerCase();
            const table = document.getElementById("logsTable");
            const tr = table.getElementsByTagName("tr");

            for (let i = 1; i < tr.length; i++) {
                let show = false;
                const tds = tr[i].getElementsByTagName("td");
                for (let j = 0; j < tds.length; j++) {
                    if (tds[j]) {
                        const txtValue = tds[j].textContent || tds[j].innerText;
                        if (txtValue.toLowerCase().indexOf(filter) > -1) {
                            show = true;
                            break;
                        }
                    }
                }
                tr[i].style.display = show ? "" : "none";
            }
        }

        // 6. Buscador en tiempo real de logs (Programador)
        function filterProgLogs() {
            const input = document.getElementById("logSearchProg");
            const filter = input.value.toLowerCase();
            const table = document.getElementById("progLogsTable");
            const tr = table.getElementsByTagName("tr");

            for (let i = 1; i < tr.length; i++) {
                let show = false;
                const tds = tr[i].getElementsByTagName("td");
                for (let j = 0; j < tds.length; j++) {
                    if (tds[j]) {
                        const txtValue = tds[j].textContent || tds[j].innerText;
                        if (txtValue.toLowerCase().indexOf(filter) > -1) {
                            show = true;
                            break;
                        }
                    }
                }
                tr[i].style.display = show ? "" : "none";
            }
        }
    </script>
</body>
</html>
