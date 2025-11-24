-- Tabelas principais do sistema

CREATE TABLE usuarios (
    id_usuario INT PRIMARY KEY AUTO_INCREMENT,
    nome VARCHAR(150) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    senha VARCHAR(255) NOT NULL,
    ativo BOOLEAN DEFAULT 1,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE clientes (
    id_cliente INT PRIMARY KEY AUTO_INCREMENT,
    nome VARCHAR(150) NOT NULL,
    email VARCHAR(100),
    telefone VARCHAR(20),
    endereco VARCHAR(255),
    cpf_cnpj VARCHAR(20),
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ativo BOOLEAN DEFAULT 1,
    observacoes TEXT
);

CREATE TABLE vendas (
    id_venda INT PRIMARY KEY AUTO_INCREMENT,
    id_cliente INT NOT NULL,
    id_usuario INT NOT NULL,
    data_venda TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    valor_total DECIMAL(10, 2) NOT NULL,
    valor_pago DECIMAL(10, 2) DEFAULT 0,
    saldo_devedor DECIMAL(10, 2) NOT NULL,
    quantidade_parcelas INT DEFAULT 1,
    status_geral ENUM('aberta', 'parcial', 'paga', 'cancelada') DEFAULT 'aberta',
    observacoes_pagamento TEXT,
    data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_cliente) REFERENCES clientes(id_cliente),
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario)
);

CREATE TABLE itens_venda (
    id_item INT PRIMARY KEY AUTO_INCREMENT,
    id_venda INT NOT NULL,
    codigo_produto VARCHAR(50) NOT NULL,
    descricao VARCHAR(255) NOT NULL,
    quantidade INT DEFAULT 1,
    valor_unitario DECIMAL(10, 2) NOT NULL,
    valor_total DECIMAL(10, 2) NOT NULL,
    status_pagamento ENUM('pendente', 'parcial', 'pago') DEFAULT 'pendente',
    valor_pago DECIMAL(10, 2) DEFAULT 0,
    saldo_item DECIMAL(10, 2) NOT NULL,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_venda) REFERENCES vendas(id_venda) ON DELETE CASCADE
);

CREATE TABLE financeiro_parcelas (
    id_parcela INT PRIMARY KEY AUTO_INCREMENT,
    id_venda INT NOT NULL,
    id_item INT,
    numero_parcela INT NOT NULL,
    valor_previsto DECIMAL(10, 2) NOT NULL,
    valor_efetivo DECIMAL(10, 2),
    data_vencimento DATE NOT NULL,
    data_pagamento DATE,
    status ENUM('aberta', 'paga', 'vencida', 'cancelada') DEFAULT 'aberta',
    saldo_parcela DECIMAL(10, 2) NOT NULL,
    lembrete_enviado TINYINT DEFAULT 0,
    FOREIGN KEY (id_venda) REFERENCES vendas(id_venda) ON DELETE CASCADE,
    FOREIGN KEY (id_item) REFERENCES itens_venda(id_item) ON DELETE SET NULL
);

CREATE TABLE financeiro_movimentacoes (
    id_movimentacao INT PRIMARY KEY AUTO_INCREMENT,
    id_venda INT NOT NULL,
    id_parcela INT,
    id_item INT,
    valor_pago DECIMAL(10, 2) NOT NULL,
    forma_pagamento VARCHAR(50),
    data_pagamento TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    observacoes TEXT,
    FOREIGN KEY (id_venda) REFERENCES vendas(id_venda) ON DELETE CASCADE,
    FOREIGN KEY (id_parcela) REFERENCES financeiro_parcelas(id_parcela) ON DELETE SET NULL,
    FOREIGN KEY (id_item) REFERENCES itens_venda(id_item) ON DELETE SET NULL
);

CREATE TABLE configuracoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    chave VARCHAR(255) UNIQUE NOT NULL,
    valor LONGTEXT,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Índices para melhor performance
CREATE INDEX idx_venda_cliente ON vendas(id_cliente);
CREATE INDEX idx_venda_status ON vendas(status_geral);
CREATE INDEX idx_parcela_vencimento ON financeiro_parcelas(data_vencimento);
CREATE INDEX idx_parcela_status ON financeiro_parcelas(status);

-- Inserir configurações padrão
INSERT IGNORE INTO configuracoes (chave, valor) VALUES
('nome_empresa', 'Minha Empresa'),
('email_empresa', ''),
('telefone_empresa', ''),
('endereco_empresa', ''),
('cnpj_empresa', ''),
('multa_atraso', '0'),
('juros_mensais', '0'),
('dias_carencia', '0'),
('email_host', ''),
('email_port', ''),
('email_user', ''),
('email_pass', ''),
('email_de', ''),
('notificar_pagamento', '1');
