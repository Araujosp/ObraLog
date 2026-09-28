-- =====================================================================
-- ObraLog — banco de dados
-- Baseado no modelo conceitual (Usuario -> Loja/Motorista, Veiculo,
-- Cliente, Entrega). Ver o rodape deste arquivo para as pequenas
-- adaptacoes feitas em cima do diagrama original e o porque de cada uma.
-- =====================================================================

CREATE DATABASE IF NOT EXISTS obralog
    CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE obralog;

-- ---------------------------------------------------------------------
-- USUARIO (generalizacao: toda loja e todo motorista tambem sao um
-- usuario, guardando o que e comum: nome, email, senha, tipo)
-- ---------------------------------------------------------------------
CREATE TABLE usuario (
    id_usuario    INT AUTO_INCREMENT PRIMARY KEY,
    nome          VARCHAR(150)                    NOT NULL,
    email         VARCHAR(150)                    NOT NULL,
    senha         VARCHAR(255)                    NOT NULL, -- hash (password_hash)
    tipo_usuario  ENUM('loja', 'motorista')       NOT NULL,
    criado_em     DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_usuario_email (email)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- LOJA (especializacao de usuario, ligacao 1-para-1 pela mesma PK)
-- ---------------------------------------------------------------------
CREATE TABLE loja (
    id_usuario     INT             PRIMARY KEY,
    cnpj           VARCHAR(14)     NOT NULL,
    nome_fantasia  VARCHAR(150)    NULL,
    telefone       VARCHAR(11)     NOT NULL,
    rua            VARCHAR(150)    NOT NULL,
    numero         VARCHAR(10)     NOT NULL,
    complemento    VARCHAR(100)    NULL,
    cep            VARCHAR(8)      NOT NULL,
    cidade         VARCHAR(100)    NOT NULL,
    estado         CHAR(2)         NOT NULL,
    UNIQUE KEY uq_loja_cnpj (cnpj),
    CONSTRAINT fk_loja_usuario FOREIGN KEY (id_usuario)
        REFERENCES usuario (id_usuario) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- VEICULO
-- ---------------------------------------------------------------------
CREATE TABLE veiculo (
    id_veiculo        INT AUTO_INCREMENT PRIMARY KEY,
    placa             VARCHAR(8)      NOT NULL,
    modelo            VARCHAR(100)    NULL,
    capacidade_carga  DECIMAL(10,2)   NULL COMMENT 'capacidade em kg',
    UNIQUE KEY uq_veiculo_placa (placa)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- MOTORISTA (especializacao de usuario). id_veiculo fica opcional:
-- o cadastro atual do site nao pede veiculo no momento do cadastro.
-- ---------------------------------------------------------------------
CREATE TABLE motorista (
    id_usuario   INT           PRIMARY KEY,
    cnh          VARCHAR(11)   NOT NULL,
    telefone     VARCHAR(11)   NOT NULL,
    id_veiculo   INT           NULL,
    UNIQUE KEY uq_motorista_cnh (cnh),
    CONSTRAINT fk_motorista_usuario FOREIGN KEY (id_usuario)
        REFERENCES usuario (id_usuario) ON DELETE CASCADE,
    CONSTRAINT fk_motorista_veiculo FOREIGN KEY (id_veiculo)
        REFERENCES veiculo (id_veiculo) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- CLIENTE (quem recebe a entrega). Ver nota sobre o CPF no rodape.
-- ---------------------------------------------------------------------
CREATE TABLE cliente (
    id_cliente   INT AUTO_INCREMENT PRIMARY KEY,
    nome         VARCHAR(150)   NOT NULL,
    cpf          VARCHAR(11)    NOT NULL,
    telefone     VARCHAR(11)    NOT NULL,
    endereco     VARCHAR(200)   NOT NULL,
    UNIQUE KEY uq_cliente_cpf (cpf)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- ENTREGA
-- id_motorista comeca NULL (= disponivel). Quando um motorista aceita,
-- gravamos o id_motorista e a data_entrega; isso substitui a
-- necessidade de uma coluna "status" separada.
-- ---------------------------------------------------------------------
CREATE TABLE entrega (
    id_entrega          INT AUTO_INCREMENT PRIMARY KEY,
    id_loja             INT            NOT NULL,
    id_motorista        INT            NULL,
    id_cliente          INT            NOT NULL,
    endereco_origem     VARCHAR(200)   NOT NULL,
    endereco_entrega    VARCHAR(200)   NOT NULL,
    materiais           TEXT           NOT NULL,
    data_solicitacao    DATETIME       DEFAULT CURRENT_TIMESTAMP,
    data_entrega         DATETIME       NULL COMMENT 'preenchida quando o motorista aceita',
    CONSTRAINT fk_entrega_loja FOREIGN KEY (id_loja)
        REFERENCES loja (id_usuario) ON DELETE CASCADE,
    CONSTRAINT fk_entrega_motorista FOREIGN KEY (id_motorista)
        REFERENCES motorista (id_usuario) ON DELETE SET NULL,
    CONSTRAINT fk_entrega_cliente FOREIGN KEY (id_cliente)
        REFERENCES cliente (id_cliente) ON DELETE CASCADE
) ENGINE=InnoDB;


-- =====================================================================
-- DADOS DE TESTE
-- Senha de TODOS os usuarios abaixo: senha123
-- (hash bcrypt gerado com password_hash($senha, PASSWORD_DEFAULT))
-- =====================================================================

INSERT INTO usuario (id_usuario, nome, email, senha, tipo_usuario) VALUES
(1, 'Depósito São Jorge',     'loja@obralog.com',      '$2y$10$2mDOthAbyelJDzbiKar2b.mwVxVdHDF8ro1F1ivTWHm9qDm9375gq', 'loja'),
(2, 'Casa do Construtor ABC', 'lojaabc@obralog.com',   '$2y$10$2mDOthAbyelJDzbiKar2b.mwVxVdHDF8ro1F1ivTWHm9qDm9375gq', 'loja'),
(3, 'João Martins',           'motorista@obralog.com', '$2y$10$2mDOthAbyelJDzbiKar2b.mwVxVdHDF8ro1F1ivTWHm9qDm9375gq', 'motorista'),
(4, 'Carlos Souza',           'carlos@obralog.com',    '$2y$10$2mDOthAbyelJDzbiKar2b.mwVxVdHDF8ro1F1ivTWHm9qDm9375gq', 'motorista');

INSERT INTO loja (id_usuario, cnpj, nome_fantasia, telefone, rua, numero, complemento, cep, cidade, estado) VALUES
(1, '11222333000181', 'Depósito São Jorge',     '11988887777', 'Av. das Industrias',     '1200', 'Galpão 4', '13560000', 'São Carlos', 'SP'),
(2, '44555666000199', 'Casa do Construtor ABC', '11977776666', 'Rua dos Materiais',      '450',  NULL,       '01310000', 'São Paulo',  'SP');

INSERT INTO veiculo (id_veiculo, placa, modelo, capacidade_carga) VALUES
(1, 'QNZ4B12', 'VW Delivery 9.170', 9000.00),
(2, 'RTX9A21', 'Mercedes Accelo',   6000.00);

INSERT INTO motorista (id_usuario, cnh, telefone, id_veiculo) VALUES
(3, '12345678900', '11966665555', 1),
(4, '98765432100', '11955554444', 2);

INSERT INTO cliente (id_cliente, nome, cpf, telefone, endereco) VALUES
(1, 'Marcos Silva',        '52998224725', '11933332222', 'Rua das Palmeiras, 88 - São Carlos/SP'),
(2, 'Fernanda Lima',       '11144477735', '11922221111', 'Av. Central, 320 - São Paulo/SP'),
(3, 'Ricardo Nogueira',    '93541134780', '11911110000', 'Rua Sete de Setembro, 55 - São Carlos/SP');

-- entregas disponiveis (id_motorista NULL) para o motorista aceitar
INSERT INTO entrega (id_loja, id_motorista, id_cliente, endereco_origem, endereco_entrega, materiais, data_solicitacao, data_entrega) VALUES
(1, NULL, 1, 'Av. das Industrias, 1200 - São Carlos/SP', 'Rua das Palmeiras, 88 - São Carlos/SP', '50 sacos de cimento CP-II (50kg cada) + 200 tijolos baianos', '2026-09-18 08:15:00', NULL),
(2, NULL, 2, 'Rua dos Materiais, 450 - São Paulo/SP',     'Av. Central, 320 - São Paulo/SP',       '30 vergalhões de aço 10mm (12m) + 15 sacos de areia média', '2026-09-19 10:40:00', NULL),
(1, NULL, 3, 'Av. das Industrias, 1200 - São Carlos/SP', 'Rua Sete de Setembro, 55 - São Carlos/SP', '10 telhas de fibrocimento + 1 caixa d\'água 1000L', '2026-09-20 14:00:00', NULL),
-- uma entrega ja aceita, so para o motorista de teste ver "Minhas entregas" e o PDF
(2, 3, 2, 'Rua dos Materiais, 450 - São Paulo/SP', 'Av. Central, 320 - São Paulo/SP', '5 sacos de argamassa + 40 blocos de concreto', '2026-09-17 09:00:00', '2026-09-17 15:30:00');


-- =====================================================================
-- NOTAS SOBRE ADAPTACOES EM CIMA DO MODELO CONCEITUAL ORIGINAL
-- (nenhum relacionamento foi removido ou invertido — apenas 4 ajustes
-- pequenos, necessarios para o fluxo pedido funcionar)
--
-- 1) cliente.cpf: o diagrama original nao tinha CPF em Cliente. Como
--    voce pediu explicitamente "dados do cliente, incluindo CPF" e o
--    PDF precisa exibir o CPF, foi adicionada essa unica coluna.
--
-- 2) entrega.id_motorista aceita NULL: no diagrama, "realiza" aparece
--    como (1,1) do lado do motorista, ou seja, toda entrega teria um
--    motorista obrigatorio. Isso é incompatível com "entrega fica
--    disponivel ate alguem aceitar". Mantive a mesma tabela e o mesmo
--    relacionamento, só permitindo NULL até o aceite.
--
-- 3) telefone virou 1 coluna em loja e em motorista (era multivalorado
--    no diagrama). O formulario de cadastro que ja existia no projeto
--    so coleta um telefone por usuario, entao nao criei uma tabela
--    separada para isso — simplicidade > fidelidade a um campo que a
--    tela nem preenche.
--
-- 4) motorista.id_veiculo é opcional (NULL): a tela de cadastro atual
--    nao tem campos de veiculo, so CNH. O relacionamento com Veiculo
--    continua existindo do jeito que o diagrama descreve (motorista
--    tem no maximo 1 veiculo), só nao é obrigatorio no momento do
--    cadastro.
-- =====================================================================
