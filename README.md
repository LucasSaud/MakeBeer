# MakeBeer - Sistema de Gestão para Cervejarias Artesanais

![MakeBeer Logo](public/images/logo.png)

MakeBeer é um sistema completo de gestão desenvolvido especificamente para cervejarias artesanais, permitindo o controle total do processo de produção, desde a receita até o envase final dos produtos.

## 🍺 Sobre o Projeto

MakeBeer foi criado para ajudar cervejarias artesanais a otimizar seus processos produtivos, controlar estoques, gerenciar receitas e acompanhar a qualidade dos produtos. O sistema abrange todas as etapas da produção cerveciera, desde o planejamento até a expedição.

## 🚀 Funcionalidades

### 📋 Gestão de Produção
- Cadastro e acompanhamento de lotes de produção
- Controle de status (planejado, em produção, fermentando, maturando, finalizado)
- Registro de consumo de insumos
- Controle de parâmetros (densidade, pH, temperatura)
- Cálculo automático de rendimento

### 📖 Receitas e Ingredientes
- Cadastro de receitas completas
- Gestão de ingredientes (maltes, lúpulos, leveduras, etc.)
- Cálculo de quantidades por volume
- Histórico de formulações

### 📦 Controle de Estoque
- Gestão de insumos e produtos finais
- Controle de movimentações
- Alertas de estoque mínimo
- Rastreabilidade de lotes

### 🛢️ Envase e Barris
- Controle de envase em barris
- Gestão de estoque de barris
- Rastreabilidade de produtos envasados
- Controle de saídas

### 📊 Relatórios e Análises
- Relatórios de produção
- Análise de custos
- Controle de qualidade
- Histórico de lotes

### 👥 Gestão de Usuários
- Controle de acesso por perfis
- Histórico de atividades
- Auditoria de operações

## 🛠️ Tecnologias Utilizadas

- **Frontend**: HTML5, CSS3, JavaScript
- **Backend**: PHP 8+
- **Banco de Dados**: MySQL
- **Servidor**: Apache
- **Arquitetura**: MVC (Model-View-Controller)

## 📋 Pré-requisitos

- PHP 8.0 ou superior
- MySQL 5.7 ou superior
- Apache com mod_rewrite habilitado
- Extensões PHP: PDO, MySQLi, mbstring

## 🚀 Instalação

1. **Clone o repositório:**
```bash
git clone https://github.com/seu-usuario/makebeer.git
```

2. **Configure o ambiente:**
- Copie o projeto para o diretório do seu servidor web
- Configure as permissões de pasta:
  ```bash
  chmod -R 755 storage/
  chmod -R 755 public/uploads/
  ```

3. **Configuração do Banco de Dados:**
- Crie um banco de dados MySQL
- Execute os scripts de migração em `database/migrations/`
- Configure as credenciais em `app/config/database.php`

4. **Configuração do Sistema:**
- Ajuste as configurações em `app/config/config.php`
- Configure o caminho base da aplicação

5. **Acesso Inicial:**
- Acesse a aplicação via navegador
- Utilize as credenciais padrão:
  - Email: admin@atomos.com
  - Senha: admin123

## 📖 Estrutura do Projeto

```
makebeer/
├── app/                    # Código da aplicação
│   ├── config/            # Arquivos de configuração
│   ├── controllers/       # Controladores MVC
│   ├── helpers/           # Funções auxiliares
│   ├── models/            # Modelos de dados
│   └── views/             # Views/templates
├── database/              # Scripts de banco de dados
│   └── migrations/        # Migrações e dados iniciais
├── public/                # Arquivos públicos
│   ├── css/               # Estilos
│   ├── js/                # Scripts JavaScript
│   └── images/            # Imagens
├── storage/               # Arquivos de armazenamento
│   └── logs/              # Logs da aplicação
└── index.php              # Ponto de entrada da aplicação
```

## 👥 Perfis de Usuário

- **Administrador**: Acesso completo a todas as funcionalidades
- **Produção**: Controle de processos produtivos
- **Consulta**: Acesso a relatórios e consultas
- **Comprador**: Gestão de fornecedores e compras

## 📊 Relatórios Disponíveis

- Relatórios de produção por período
- Análise de custos de lotes
- Controle de validade de produtos
- Estatísticas de consumo de insumos
- Histórico de qualidade

## 🤝 Contribuindo

Contribuições são bem-vindas! Para contribuir:

1. Faça um fork do projeto
2. Crie uma branch para sua feature (`git checkout -b feature/AmazingFeature`)
3. Commit suas mudanças (`git commit -m 'Add some AmazingFeature'`)
4. Push para a branch (`git push origin feature/AmazingFeature`)
5. Abra um Pull Request

## 💖 Apoie o Projeto

Se este sistema foi útil para você ou sua cervejaria, considere fazer uma doação via PIX para ajudar no desenvolvimento contínuo:

**Chave PIX**: `53484890000110`  
**Tipo**: CNPJ  
**Banco**: NuBank  

Sua contribuição ajuda a manter o projeto ativo e em constante evolução!

## 📄 Licença

Este projeto está licenciado sob a Licença MIT - veja o arquivo [LICENSE](LICENSE) para detalhes.

## 📞 Contato

Lucas Saud - [@seu_perfil](https://github.com/seu-usuario)  
Email: lucas.saud@example.com

Link do Projeto: [https://github.com/seu-usuario/makebeer](https://github.com/seu-usuario/makebeer)

## 🙏 Agradecimentos

Agradecemos a todos os cervejeiros artesanais que contribuíram com feedback e sugestões para o desenvolvimento deste sistema. A paixão pela cerveja artesanal é o que move este projeto!

---

**Feito com 🍺 por cervejeiros para cervejeiros!**