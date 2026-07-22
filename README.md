# 🎰 Centro de Apuestas "La China Sports"

[![PHP Version](https://img.shields.io/badge/PHP-8.2-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://www.php.net/)
[![MySQL](https://img.shields.io/badge/MySQL-8.0-4479A1?style=for-the-badge&logo=mysql&logoColor=white)](https://www.mysql.com/)
[![Docker](https://img.shields.io/badge/Docker-Enabled-2496ED?style=for-the-badge&logo=docker&logoColor=white)](https://www.docker.com/)
[![Apache](https://img.shields.io/badge/Apache-2.4-D22128?style=for-the-badge&logo=apache&logoColor=white)](https://httpd.apache.org/)
[![Estado](https://img.shields.io/badge/Estado-Funcional-success?style=for-the-badge)](#)

Sistema web integral de administración y gestión para el Centro de Apuestas **La China Sports**. La plataforma ofrece un entorno modular para la gestión de apuestas deportivas, tableros administrativos, autenticación de usuarios, suscripciones y visualización de resultados en tiempo real.

---

## 📌 Tabla de Contenidos

- [📖 Descripción del Proyecto](#-descripción-del-proyecto)
- [🚀 Características Principales](#-características-principales)
- [🛠️ Tecnologías Utilizadas](#️-tecnologías-utilizadas)
- [📁 Estructura del Repositorio](#-estructura-del-repositorio)
- [⚙️ Requisitos Previos](#️-requisitos-previos)
- [🏃‍♂️ Instalación y Despliegue](#️-instalación-y-despliegue)
  - [Opción A: Instalación Automática (Windows PowerShell)](#opción-a-instalación-automática-windows-powershell)
  - [Opción B: Despliegue Manual con Docker Compose](#opción-b-despliegue-manual-con-docker-compose)
- [🔑 Acceso y Credenciales de Prueba](#-acceso-y-credenciales-de-prueba)
- [🗄️ Arquitectura de Base de Datos](#️-arquitectura-de-base-de-datos)
- [🔧 Comandos Útiles de Mantenimiento](#-comandos-útiles-de-mantenimiento)
- [❓ Solución de Problemas](#-solución-de-problemas)

---

## 📖 Descripción del Proyecto

El sistema del Centro de Apuestas **La China Sports** ha sido desarrollado para digitalizar y automatizar los procesos de recepción de apuestas, control de usuarios, publicación de logros y resultados deportivos, y administración general del establecimiento. 

El proyecto cuenta con un entorno completamente endockerizado que integra un servidor web **PHP 8.2 + Apache** y un motor de base de datos **MySQL 8.0**, garantizando un despliegue rápido, portátil y aislado en cualquier entorno.

---

## 🚀 Características Principales

- 🔐 **Autenticación y Registro**: Sistema seguro de login, registro de nuevos usuarios y recuperación de contraseña.
- 📊 **Panel de Control (Dashboard)**: Interfaz administrativa centralizada para gestionar operaciones, usuarios y estadísticas.
- ⚽ **SportBook / Menú Deportivo**: Módulo interactivo para la consulta de apuestas deportivas y parleys.
- 📬 **Integración PHPMailer**: Envío automatizado de correos de confirmación y notificaciones.
- 🐳 **Entorno Dockerizado**: Despliegue instantáneo mediante contenedores aislados de servidor web y MySQL.
- 🔄 **Inicialización Automática de BD**: Scripts automáticos que crean las esquemas requeridos e importan respaldos SQL al iniciar los contenedores.

---

## 🛠️ Tecnologías Utilizadas

- **Lenguaje Principal**: PHP 8.2
- **Servidor Web**: Apache 2.4 (con módulo `mod_rewrite` activo)
- **Base de Datos**: MySQL 8.0
- **Contenedores**: Docker & Docker Compose v3.8
- **Frontend**: HTML5, CSS3, JavaScript
- **Librerías Adicionales**: PHPMailer
- **Automatización de Despliegue**: PowerShell (`.ps1`) y Bash (`.sh`)

---

## 📁 Estructura del Repositorio

```text
Proyecto-III/
├── bd/                                          # Volcados e importaciones iniciales de SQL
│   ├── deportes_db.sql                          # Base de datos de deportes y logros
│   ├── lachina2_bd.sql                          # Base de datos principal del sistema
│   └── sainvc_bd.sql                            # Base de datos complementaria de usuarios/sistema
├── lachina2/                                    # Código fuente del aplicativo PHP/HTML
│   ├── Connections/                             # Conexiones a base de datos
│   ├── PHPMailer/                               # Librería de envío de correos
│   ├── assets/ & css/ & js/                     # Estilos, scripts e imágenes de la interfaz
│   ├── funciones/                               # Helpers y funciones modulares
│   ├── dashboard.php                            # Panel de control principal
│   ├── index.php                                # Página de inicio / Landing
│   ├── login.html & register.html               # Formularios de acceso y registro
│   ├── menu_sportbook.php & menu_usuario.php    # Módulos de apuestas y gestión
│   └── validar_login.php                        # Lógica de validación de credenciales
├── Dockerfile                                   # Imagen Docker personalizada para PHP 8.2 + Apache
├── docker-compose.yml                           # Orquestación de servicios (Web + MySQL)
├── init-db.sh                                   # Script de inicialización bash para MySQL
├── instalar_y_levantar.ps1                      # Script de automatización PowerShell para Windows
└── README.md                                    # Documentación del proyecto
```

---

## ⚙️ Requisitos Previos

Asegúrate de contar con los siguientes elementos instalados en tu sistema:

1. **Docker Desktop** (con Docker Engine y Docker Compose).
2. **Git** para la clonación del repositorio.
3. *(Opcional para Windows)* **PowerShell 5.1+** si deseas ejecutar la instalación automatizada.

---

## 🏃‍♂️ Instalación y Despliegue

### Opción A: Instalación Automática (Windows PowerShell)

Si estás en Windows y cuentas con el instalador de Docker o deseas ejecutar el script automatizado del proyecto, abre una terminal de PowerShell como Administrador y ejecuta:

```powershell
Set-ExecutionPolicy Unrestricted -Scope Process
.\instalar_y_levantar.ps1
```

> ℹ️ **¿Qué hace este script?**
> 1. Verifica/Instala Docker Desktop en modo desatendido.
> 2. Inicializa el servicio Docker Engine.
> 3. Ejecuta `docker-compose up --build -d`.
> 4. Abre automáticamente el navegador en `http://localhost:8080`.

---

### Opción B: Despliegue Manual con Docker Compose

Recomendado para Linux, macOS o desarrolladores en Windows que ya tengan Docker activo:

1. **Clonar el repositorio:**
   ```bash
   git clone https://github.com/OscarCdev27/Proyecto-III.git
   cd Proyecto-III
   ```

2. **Levantar los servicios con Docker Compose:**
   ```bash
   docker-compose up --build -d
   ```

3. **Verificar el estado de los contenedores:**
   ```bash
   docker-compose ps
   ```

4. **Acceder a la aplicación:**
   Abre tu navegador e ingresa a: **`http://localhost:8080`**

---

## 🔑 Acceso y Credenciales de Prueba

Para realizar pruebas en el entorno local una vez desplegado el proyecto:

| Parámetro | Valor |
| :--- | :--- |
| **URL del Sistema** | [http://localhost:8080](http://localhost:8080) |
| **URL de Iniciar Sesión** | [http://localhost:8080/login.html](http://localhost:8080/login.html) |
| **Correo de Prueba** | `jazpaczl@hotmail.com` |
| **Contraseña de Prueba** | `4321` |

---

## 🗄️ Arquitectura de Base de Datos

El contenedor de MySQL (`lachina_db`) inicializa automáticamente **3 bases de datos** a partir de los scripts contenidos en la carpeta `bd/`:

1. **`lachina2_bd`**: Base de datos principal de la plataforma (usuarios, sesiones, apuestas y configuración).
2. **`tucasa_bd`**: Esquema dedicado a deportes, equipos y eventos.
3. **`sainvc30_bd`**: Esquema complementario de registros y soporte.

### Credenciales Internas de Base de Datos (Contenedor a Contenedor)

- **Host**: `db` (puerto `3306`)
- **Usuario**: `root_mundo`
- **Contraseña**: `D2wZP8WhX_PyNAKc*`
- **Root Pass**: `secret_root_pass`

---

## 🔧 Comandos Útiles de Mantenimiento

### Ver logs en tiempo real
```bash
# Logs del servidor web Apache/PHP
docker-compose logs -f web

# Logs de la base de datos MySQL
docker-compose logs -f db
```

### Reiniciar el entorno
```bash
docker-compose restart
```

### Detener y eliminar contenedores y volúmenes
```bash
docker-compose down -v
```

---

## ❓ Solución de Problemas

<details>
<summary><b>1. El puerto 8080 o 3306 está ocupado</b></summary>

Si los puertos ya están en uso por otra aplicación en tu equipo, modifica el archivo `docker-compose.yml`:
```yaml
ports:
  - "8081:80"   # Cambiar 8080 por 8081 u otro disponible
```
Y para la base de datos:
```yaml
ports:
  - "3307:3306"  # Cambiar 3306 por 3307
```
</details>

<details>
<summary><b>2. La base de datos no se inicializó correctamente</b></summary>

Si realizaste modificaciones en los archivos `.sql` de la carpeta `bd/` y deseas forzar la reimportación:
```bash
docker-compose down -v
docker-compose up --build -d
```
*Nota: El parámetro `-v` elimina los volúmenes persistentes para forzar la ejecución de `init-db.sh` nuevamente.*
</details>

---

## 📄 Licencia

Este proyecto fue desarrollado como parte de un proyecto académico / desarrollo institucional para el **Centro de Apuestas La China Sports**. Todos los derechos reservados.
