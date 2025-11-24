<?php
class Usuario {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    public function autenticar($email, $senha) {
        $query = "SELECT * FROM usuarios WHERE email = ? AND ativo = 1";
        $stmt = $this->db->execute($query, 's', [$email]);
        $usuario = $stmt->get_result()->fetch_assoc();

        if ($usuario && password_verify($senha, $usuario['senha'])) {
            return $usuario;
        }
        return false;
    }

    public function criar($nome, $email, $senha) {
        $query = "SELECT id_usuario FROM usuarios WHERE email = ?";
        $stmt = $this->db->execute($query, 's', [$email]);
        
        if ($stmt->get_result()->num_rows > 0) {
            return ['sucesso' => false, 'mensagem' => 'Email já registrado'];
        }

        $senhaHash = password_hash($senha, PASSWORD_BCRYPT);
        $query = "INSERT INTO usuarios (nome, email, senha) VALUES (?, ?, ?)";
        $stmt = $this->db->execute($query, 'sss', [$nome, $email, $senhaHash]);

        if ($stmt) {
            return [
                'sucesso' => true,
                'mensagem' => 'Usuário criado com sucesso',
                'id_usuario' => $this->db->getConnection()->insert_id
            ];
        }
        return ['sucesso' => false, 'mensagem' => 'Erro ao criar usuário'];
    }

    public function obter($idUsuario) {
        $query = "SELECT id_usuario, nome, email FROM usuarios WHERE id_usuario = ?";
        $stmt = $this->db->execute($query, 'i', [$idUsuario]);
        return $stmt->get_result()->fetch_assoc();
    }
}
?>