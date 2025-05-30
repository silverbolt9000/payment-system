# Sistema de Pagamento Simplificado

Este é um sistema de pagamento simplificado desenvolvido em PHP com Laravel, que permite transferências de dinheiro entre usuários comuns e lojistas.
Aplicação feita usando o Laravel Blade, tailwind e banco de dados sqlite  

## Funcionalidades

- Cadastro de usuários (comuns e lojistas)
- Transferências de dinheiro entre usuários
- Validação de saldo antes de transferências
- Consulta a serviço externo autorizador
- Transações reversíveis em caso de inconsistência
- Notificação de pagamento.

## Requisitos

- PHP 8.1 ou superior
- Composer
- SQLite


### Instalação Manual

1. Clone o repositório:
```bash
git clone https://github.com/silverbolt9000/payment-system-frontend.git
cd payment-system
```

2. Instale as dependências:
```bash
composer install
```

3. Copie o arquivo de ambiente:
```bash
cp .env.example .env
```

4. Configure o arquivo .env com suas credenciais de banco de dados

5. Gere a chave da aplicação:
```bash
php artisan key:generate
```

6. Execute as migrações:
```bash
php artisan migrate
```

7. Inicie o servidor:
```bash
php artisan serve
```

## Endpoints da API

### Cadastro de Usuário
```
POST /api/users
```
Parâmetros:
- `name`: Nome completo do usuário
- `email`: Email único do usuário
- `cpf_cnpj`: CPF ou CNPJ único
- `password`: Senha do usuário
- `user_type`: Tipo de usuário ('common' ou 'shopkeeper')

### Transferência de Dinheiro
```
POST /api/transfers
```
Parâmetros:
- `payee_id`: ID do usuário recebedor
- `amount`: Valor a ser transferido

## Testes

Para executar os testes:

```bash
php artisan test
```

## Estrutura do Projeto

- `app/Models`: Modelos do sistema (User, Wallet, Transaction)
- `app/Http/Controllers`: Controladores da API
- `app/Services`: Serviços de negócio
- `app/Repositories`: Repositórios para acesso a dados
- `app/Exceptions`: Tratamento de exceções personalizadas
- `tests`: Testes unitários e de integração

## Decisões de Arquitetura

- Utilização de padrões de projeto como Repository e Service
- Implementação de princípios SOLID
- Tratamento de erros consistente
- Validações de entrada
- Transações de banco de dados para garantir consistência

## Licença

Este projeto está licenciado sob a licença MIT.
