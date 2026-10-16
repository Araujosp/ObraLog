# ObraLog

<p align="center">
 <img width="1218" height="532" alt="image" src="https://github.com/user-attachments/assets/9f8e61d9-dcd2-47ba-98d9-f0a85911d1b3" />

</p>

<p align="center">
  <img src="https://img.shields.io/badge/PHP-8-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP">
  <img src="https://img.shields.io/badge/MySQL-MariaDB-4479A1?style=for-the-badge&logo=mysql&logoColor=white" alt="SQL (MySQL/MariaDB)">
  <img src="https://img.shields.io/badge/Google%20Maps-Rotas-4285F4?style=for-the-badge&logo=googlemaps&logoColor=white" alt="Google Maps">
  <img src="https://img.shields.io/badge/PDF-FPDF-EC1C24?style=for-the-badge&logo=adobeacrobatreader&logoColor=white" alt="PDF (FPDF)">
</p>

Sistema web para gerenciar **entregas de materiais de construção**: lojas publicam cargas, motoristas aceitam e executam as entregas, e a loja acompanha o andamento em tempo quase real.

## Funcionalidades

**Loja**
- Cadastro e login (CNPJ validado pelos dígitos verificadores).
- Criação de entrega com cliente (CPF validado), endereço de origem/destino, **CEP** (máscara `00000-000`) e materiais.
- **Histórico de Entregas** com status colorido, filtro por status, busca por cliente ou nº e atualização automática a cada 15 s, sem recarregar a página.
- Detalhes da entrega com linha do tempo (criada → aceita → iniciada → finalizada).
- Comprovante em **PDF** quando a entrega é finalizada.

**Motorista**
- Cadastro e login (CNH).
- Lista de entregas disponíveis, detalhes e aceite (só um motorista consegue aceitar cada carga).
- **Minhas Entregas**: iniciar e finalizar entrega.
- Botão **Ver rota no Google Maps** com o endereço e o CEP da entrega.

## Status da entrega

O status é **derivado** das colunas da tabela `entrega` (não existe coluna `status`, então não há dados contraditórios):

| Situação | Status exibido |
|---|---|
| Sem motorista | Buscando motorista |
| Motorista aceitou | Entrega aceita |
| Motorista iniciou (`data_inicio`) | Entrega em andamento |
| Motorista finalizou (`data_finalizacao`) | Entrega finalizada |

Gerar ou abrir o PDF **não** altera o status. Para finalizar, a entrega precisa ter sido iniciada pelo motorista dono dela.

## Tecnologias

| | Tecnologia | Uso |
|---|---|---|
| <img src="https://cdn.simpleicons.org/php/777BB4" width="20"> | **PHP 8** | Backend sem framework, com PDO e *prepared statements* |
| <img src="https://cdn.simpleicons.org/mysql/4479A1" width="20"> | **SQL (MySQL / MariaDB)** | Banco de dados |
| <img src="https://cdn.simpleicons.org/googlemaps/4285F4" width="20"> | **Google Maps** | Botão de rota até o endereço da entrega |
| <img src="https://cdn.simpleicons.org/adobeacrobatreader/EC1C24" width="20"> | **PDF ([FPDF](http://www.fpdf.org/))** | Comprovante da entrega |
| <img src="https://cdn.simpleicons.org/javascript/F7DF1E" width="20"> | **HTML, CSS e JavaScript** | Interface, sem bibliotecas |
- Ambiente de desenvolvimento: XAMPP

## Estrutura

```
├── index.php, login.php, logout.php, cadastro.php
├── api/
│   ├── entrar.php                 # login (JSON/redirect)
│   └── historico-entregas.php     # JSON do histórico da loja
├── cadastrar-entrega.php          # loja: nova entrega
├── historico-entregas.php         # loja: histórico
├── detalhe-entrega-loja.php       # loja: detalhe de uma entrega
├── entregas.php                   # motorista: entregas disponíveis
├── entrega-detalhe.php            # motorista: detalhe + Google Maps
├── aceitar-entrega.php            # motorista: aceitar
├── iniciar-entrega.php            # motorista: iniciar
├── minhas-entregas.php            # motorista: entregas ativas
├── finalizar-entrega.php          # motorista: finalizar
├── relatorio.php                  # PDF da entrega
├── includes/                      # conexão, autenticação, regras de entrega, menus
├── database/
│   ├── obralog.sql                # banco completo + dados de teste
│   └── migracao-cep-status.sql    # atualiza bancos já existentes
├── tests/fluxo-entregas.sh        # teste de ponta a ponta
├── fpdf19/                        # biblioteca FPDF
├── style.css, script.js
└── img/
```

## Como rodar

1. Instale o [XAMPP](https://www.apachefriends.org/) e inicie **Apache** e **MySQL**.
2. Copie a pasta do projeto para `C:\xampp\htdocs\ObraLog`.
3. No phpMyAdmin, crie o banco importando `database/obralog.sql` (aba **Importar**, conjunto de caracteres `utf-8`).
   - Já tem o banco `obralog` com dados? Execute apenas `database/migracao-cep-status.sql` (idempotente, não apaga nada).
4. Confira as credenciais em `includes/conexao.php` (padrão XAMPP: usuário `root`, senha vazia).
5. Acesse `http://localhost/ObraLog/login.php` (ou a porta que seu Apache usar, ex.: `localhost:8080`).

Requisitos: PHP 8+ com as extensões `pdo_mysql` e `mbstring` (já vêm no XAMPP).

## Usuários de teste

| Tipo | E-mail | Senha |
|---|---|---|
| Loja | `loja@obralog.com` | `senha123` |
| Loja | `lojaabc@obralog.com` | `senha123` |
| Motorista | `motorista@obralog.com` | `senha123` |
| Motorista | `carlos@obralog.com` | `senha123` |

## Segurança

- Senhas com `password_hash` / `password_verify`.
- Todas as consultas usam *prepared statements*; saída HTML escapada.
- Autorização no servidor: a loja só enxerga as próprias entregas (o `id_loja` vem da sessão, nunca da URL), e o motorista só inicia/finaliza as entregas que aceitou.
- Mensagens de erro ao usuário não expõem detalhes do banco (o detalhe técnico vai para o log do PHP).

## Testes

Com o servidor rodando e um banco de desenvolvimento limpo:

```bash
tests/fluxo-entregas.sh http://localhost/ObraLog
```

Cobre criação, validação de CEP, aceite, início, finalização, Google Maps, isolamento entre lojas e falha de banco. **Grava entregas de teste no banco.**

## Limitações conhecidas

- O cadastro de cliente é compartilhado por CPF entre todas as lojas.
- Não há cancelamento de entrega nem rastreamento GPS (o Maps apenas abre a rota).
- O PDF usa fontes padrão (Windows-1252): emojis e símbolos fora desse conjunto aparecem como `?`.
- Entregas criadas antes do campo CEP ficam sem CEP.
