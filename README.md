# 🚀 Laravel + Vue.js: Roadmap de Estágio (12 Semanas)

Este repositório foi criado para organizar, documentar e demonstrar meu progresso técnico ao longo de um **Roadmap de Estudos de 12 Semanas**. O objetivo principal é consolidar conceitos fundamentais de desenvolvimento Web Full Stack através da construção de **Micro-Projetos de Validação (PoCs - Proof of Concepts)**.

O cronograma é focado em engenharia de software de verdade: boas práticas, performance, segurança e arquitetura desacoplada utilizando **Laravel (com banco PostgreSQL)** no backend e **Vue.js (Composition API)** no frontend.

---

## 📂 Estrutura do Repositório

Para manter o portfólio profissional e organizado, o repositório está estruturado da seguinte forma:

```text
├── docs/                        # Guias teóricos e passo a passo de cada semana
│   ├── semana-01-relacionamentos.md
│   ├── semana-02-performance-n1.md
│   ├── ...
│   └── semana-12-vue-integracao.md
├── backend/                     # Código-fonte da API Laravel (Semanas 1 a 8)
│   ├── app/
│   ├── database/
│   └── ...
└── frontend/                    # Código-fonte do app Vue.js (Semanas 9 a 12)
    ├── src/
    └── ...
```

---

## 📈 Painel de Controle & Progresso

Abaixo está o controle de evolução de cada semana do roadmap. Cada item possui o link direto para o seu respectivo **Guia de Estudos** e a indicação do status atual de entrega.

| Semana | Fase | Tópico Principal | Entrega Técnica (PoC) | Status | Guia de Estudos |
| :---: | :---: | :--- | :--- | :---: | :---: |
| **01** | 🗄️ Dados | Relacionamentos do Eloquent | Mapeamento `hasMany` / `belongsTo` no Tinker | `[x] Concluído` | [Ver Guia](./docs/semana-01-relacionamentos.md) |
| **02** | 🗄️ Dados | Performance de Queries (N+1) | Otimização com Eager Loading (`with()`) | `[ ] Em Progresso` | [Ver Guia](./docs/semana-02-performance-n1.md) |
| **03** | 🗄️ Dados | Versionamento e População de Dados | Migrations, Factories e Seeders com Faker | `[ ] Pendente` | [Ver Guia](./docs/semana-03-migrations-seeds.md) |
| **04** | 🗄️ Dados | Transações de Banco Seguras | Rollback automático usando `DB::transaction()` | `[ ] Pendente` | [Ver Guia](./docs/semana-04-transacoes-seguras.md) |
| **05** | 🐘 API | Validação Isolada | Regras em Form Requests (rejeição HTTP 422) | `[ ] Pendente` | [Ver Guia](./docs/semana-05-validacao-requests.md) |
| **06** | 🐘 API | Formatação de Respostas JSON | Padronização e máscara com API Resources | `[ ] Pendente` | [Ver Guia](./docs/semana-06-api-resources.md) |
| **07** | 🐘 API | Autenticação e Proteção de Rotas | Emissão e validação de Tokens com Sanctum | `[ ] Pendente` | [Ver Guia](./docs/semana-07-autenticacao-sanctum.md) |
| **08** | 🐘 API | Tratamento Global de Exceções | Padronização de respostas de erros (HTTP 404) | `[ ] Pendente` | [Ver Guia](./docs/semana-08-erros-http.md) |
| **09** | ⚡ Vue.js | Reatividade com Composition API | Estado reativo dinâmico (`ref` e `reactive`) | `[ ] Pendente` | [Ver Guia](./docs/semana-09-vue-reatividade.md) |
| **10** | ⚡ Vue.js | Comunicação Assíncrona com API | Consumo do backend Laravel usando Axios | `[ ] Pendente` | [Ver Guia](./docs/semana-10-vue-axios.md) |
| **11** | ⚡ Vue.js | Modularização de Componentes | Troca de dados via Props e eventos via Emits | `[ ] Pendente` | [Ver Guia](./docs/semana-11-vue-props-emits.md) |
| **12** | ⚡ Vue.js | Integração de Formulários & Erros | Envio de formulário e mapeamento de erros 422 | `[ ] Pendente` | [Ver Guia](./docs/semana-12-vue-integracao.md) |

---

## 🛠️ Tecnologias e Conceitos Demonstrados

Durante as entregas deste repositório, são validadas as seguintes competências técnicas:
- **Modelagem de Dados Relacional:** Mapeamento ORM acoplado ao banco PostgreSQL.
- **Performance de Consultas SQL:** Diagnóstico e mitigação de gargalos de rede e I/O.
- **Desenvolvimento de APIs RESTful:** Criação de rotas, controle de acesso, filtros de saída e tratamento de falhas.
- **Segurança da Informação:** Validação estrita de dados de entrada e controle por tokens de acesso.
- **Componentização Reativa:** Criação de interfaces de usuário desacopladas, limpas e reativas com Vue 3.

---

## 🚀 Como Executar o Projeto Localmente

### Pré-requisitos
Certifique-se de possuir instalado na sua máquina:
- PHP 8.2+
- Composer
- Node.js & NPM
- Servidor PostgreSQL ativo

### Setup do Backend (Laravel)
1. Navegue até a pasta do backend:
   ```bash
   cd backend
   ```
2. Instale as dependências do PHP:
   ```bash
   composer install
   ```
3. Configure o arquivo `.env` com suas credenciais do PostgreSQL:
   ```env
   DB_CONNECTION=pgsql
   DB_HOST=127.0.0.1
   DB_PORT=5432
   DB_DATABASE=seu_banco
   DB_USERNAME=seu_usuario
   DB_PASSWORD=sua_senha
   ```
4. Rode as migrations e alimente o banco com dados iniciais:
   ```bash
   php artisan migrate --seed
   ```
5. Inicie o servidor embutido:
   ```bash
   php artisan serve
   ```

### Setup do Frontend (Vue.js)
1. Navegue até a pasta do frontend:
   ```bash
   cd ../frontend
   ```
2. Instale as dependências do Node:
   ```bash
   npm install
   ```
3. Configure a URL da API no arquivo correspondente (ou `.env` do Vue, apontando para `http://localhost:8000/api`)
4. Inicie o servidor de desenvolvimento:
   ```bash
   npm run dev
   ```

---

*Desenvolvido como parte do meu plano de capacitação prática em Engenharia de Software Full Stack.*