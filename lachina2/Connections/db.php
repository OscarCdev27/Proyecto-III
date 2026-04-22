<?php
// En un entorno real, usarías una librería como vlucas/phpdotenv
// Para este proyecto escolar, simulamos la lectura del .env o usamos constantes:

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');

class Database {
    private $host = DB_HOST;
    private $user = DB_USER;
    private $pass = DB_PASS;
    public $conn;

    public function getConnection($dbName) {
        $this->conn = null;
        try {
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $dbName, $this->user, $this->pass);
            $this->conn->exec("set names utf8");
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $exception) {
            echo "Error de conexión: " . $exception->getMessage();
        }
        return $this->conn;
    }
}
?>