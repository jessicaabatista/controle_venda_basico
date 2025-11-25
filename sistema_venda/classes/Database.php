<?php
/**
 * Classe Database - Gerenciamento de Conexão com MySQL
 * Arquivo: classes/Database.php
 * Usa constantes do arquivo config/config.php
 */

class Database {
    private $connection;
    private static $instance = null;

    /**
     * Padrão Singleton para garantir apenas uma conexão
     * @return Database
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Método estático alternativo para conectar (NOVO - para compatibilidade)
     * @return mysqli
     */
    public static function conectar() {
        return self::getInstance()->getConnection();
    }

    public function __construct() {
        $this->connect();
    }

    /**
     * Estabelece conexão com o banco de dados
     * @return mysqli
     */
    public function connect() {
        $this->connection = new mysqli(
            DB_HOST,
            DB_USER,
            DB_PASS,
            DB_NAME
        );

        if ($this->connection->connect_error) {
            error_log("Erro de conexão com banco de dados: " . $this->connection->connect_error);
            die('Erro de conexão com o banco de dados. Por favor, contate o administrador.');
        }

        $this->connection->set_charset(DB_CHARSET);
        return $this->connection;
    }

    /**
     * Obtém a conexão MySQL
     * @return mysqli
     */
    public function getConnection() {
        if (!$this->connection || !$this->connection->ping()) {
            $this->connect();
        }
        return $this->connection;
    }

    /**
     * Executa uma query preparada com segurança contra SQL Injection
     * @param string $query Query com placeholders (?)
     * @param string $types Tipos de dados (i=int, d=double, s=string, b=blob)
     * @param array $params Parâmetros para bind
     * @return mysqli_stmt Statement preparado
     */
    public function execute($query, $types = '', $params = []) {
        $conn = $this->getConnection();
        $stmt = $conn->prepare($query);

        if (!$stmt) {
            error_log("Erro ao preparar query: " . $conn->error);
            throw new Exception("Erro ao preparar a query: " . $conn->error);
        }

        if (!empty($types) && !empty($params)) {
            if (!$stmt->bind_param($types, ...$params)) {
                error_log("Erro ao fazer bind de parâmetros: " . $stmt->error);
                throw new Exception("Erro ao processar parâmetros");
            }
        }

        if (!$stmt->execute()) {
            error_log("Erro ao executar query: " . $stmt->error);
            throw new Exception("Erro ao executar a query: " . $stmt->error);
        }

        return $stmt;
    }

    /**
     * Executa um SELECT e retorna todos os resultados
     * @param string $query Query SELECT
     * @return array Array associativo com os resultados
     */
    public function select($query) {
        $stmt = $this->execute($query);
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Executa um INSERT, UPDATE ou DELETE
     * @param string $query Query com placeholders
     * @param string $types Tipos de dados
     * @param array $params Parâmetros
     * @return bool
     */
    public function query($query, $types = '', $params = []) {
        try {
            $stmt = $this->execute($query, $types, $params);
            return $stmt->affected_rows >= 0;
        } catch (Exception $e) {
            error_log("Erro na execução de query: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtém último ID inserido
     * @return int
     */
    public function lastInsertId() {
        return $this->connection->insert_id;
    }

    /**
     * Obtém número de linhas afetadas pela última query
     * @return int
     */
    public function affectedRows() {
        return $this->connection->affected_rows;
    }

    /**
     * Inicia uma transação
     * @return bool
     */
    public function beginTransaction() {
        return $this->connection->begin_transaction();
    }

    /**
     * Confirma uma transação
     * @return bool
     */
    public function commit() {
        return $this->connection->commit();
    }

    /**
     * Desfaz uma transação
     * @return bool
     */
    public function rollback() {
        return $this->connection->rollback();
    }

    /**
     * Escapa uma string para evitar SQL Injection (usar apenas em último caso)
     * @param string $string
     * @return string
     */
    public function escape($string) {
        return $this->connection->real_escape_string($string);
    }

    /**
     * Fecha a conexão
     * @return bool
     */
    public function close() {
        if ($this->connection) {
            return $this->connection->close();
        }
        return false;
    }

    /**
     * Destrutor - fecha conexão automaticamente
     */
    public function __destruct() {
        $this->close();
    }
}
?>