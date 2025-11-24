<?php
class Database {
    private $connection;
    private $host = 'localhost';
    private $user = 'root';
    private $password = '';
    private $database = 'vendas_semijoias';

    public function connect() {
        $this->connection = new mysqli(
            $this->host,
            $this->user,
            $this->password,
            $this->database
        );

        if ($this->connection->connect_error) {
            die('Erro de conexão: ' . $this->connection->connect_error);
        }

        $this->connection->set_charset('utf8mb4');
        return $this->connection;
    }

    public function getConnection() {
        if (!$this->connection) {
            $this->connect();
        }
        return $this->connection;
    }

    public function execute($query, $types = '', $params = []) {
        $conn = $this->getConnection();
        $stmt = $conn->prepare($query);

        if ($types && $params) {
            $stmt->bind_param($types, ...$params);
        }

        $stmt->execute();
        return $stmt;
    }

    public function select($query) {
        $stmt = $this->execute($query);
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function close() {
        if ($this->connection) {
            $this->connection->close();
        }
    }
}
?>