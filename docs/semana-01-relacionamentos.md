# Tutorial Semana 1: Relacionamentos Essenciais no Eloquent (hasMany & belongsTo)

Nesta primeira semana do nosso roteiro, vamos mergulhar no coração do banco de dados do Laravel: o **Eloquent ORM**. O objetivo não é apenas copiar e colar código, mas entender profundamente **o que** estamos fazendo e **por que** estamos fazendo.

---

## 0. Pré-requisito: Blindando sua Conexão
Antes de criarmos tabelas, precisamos garantir que nossa fundação está sólida. Em arquiteturas baseadas em containers (com multi-stages e volumes compartilhados), é comum que o Laravel acabe se perdendo nas variáveis de ambiente.

Para garantir que a aplicação não ignore o MySQL e acabe criando um banco SQLite acidentalmente, verifique se a configuração do seu ambiente (seja no arquivo `.env` da raiz ou nas variáveis `environment` do seu orquestrador de containers) está explicitamente definida assim:

```env
DB_CONNECTION=mysql
```
*Por que fazer isso?* Isso força o backend a utilizar o driver do MySQL, garantindo que as migrations e as queries que faremos a seguir rodem no banco de dados correto.

---

## 1. O Conceito: O que é o Eloquent e por que usá-lo?

No desenvolvimento tradicional, se você quisesse buscar os pedidos de um cliente, teria que escrever uma string SQL "crua" (raw SQL), algo como `SELECT * FROM pedidos WHERE cliente_id = 1`. 

**Por que não fazemos isso no Laravel?**
1. **Segurança:** O Eloquent protege automaticamente sua aplicação contra *SQL Injection*.
2. **Manutenibilidade:** Se um dia você precisar mudar a lógica, é muito mais fácil alterar um método em PHP do que caçar strings SQL espalhadas pelo código.
3. **Orientação a Objetos:** O Eloquent transforma tabelas do banco em **Classes (Models)** e as linhas da tabela em **Objetos**. Isso significa que você manipula dados como se fossem variáveis normais do PHP.

### A Lógica dos Relacionamentos
Imagine um cenário clássico de e-commerce com **Clientes** e **Pedidos**.
* **hasMany (Tem Muitos):** Um Cliente (a entidade principal) pode fazer 1, 10 ou 100 compras. Portanto, o Cliente *tem muitos* Pedidos.
* **belongsTo (Pertence a):** Um Pedido específico (ex: o pedido #4092) não pode pertencer a João e a Maria ao mesmo tempo. Ele *pertence a* um único Cliente.

---

## 2. Passo a Passo Detalhado da Implementação

### Passo 1: Criando os Models e as Migrations
No terminal do seu projeto (ou dentro do container da aplicação), execute:

```bash
php artisan make:model Cliente -m
php artisan make:model Pedido -m
```

**Por que usamos a flag `-m`?**
A flag `-m` cria automaticamente um arquivo de **Migration** junto com o Model. Migrations são como um "controle de versão" (um Git) para o seu banco de dados. Em vez de abrir o DBeaver e criar tabelas manualmente, você escreve a estrutura em PHP. Isso garante que qualquer desenvolvedor que baixar seu projeto consiga recriar o banco exatamente igual com um único comando.

### Passo 2: Estruturando as Tabelas (As Migrations)
Vá até a pasta `database/migrations/` e abra o arquivo que termina em `_create_clientes_table.php`.

```php
Schema::create('clientes', function (Blueprint $table) {
    $table->id(); // Cria uma chave primária auto-incremental
    $table->string('nome');
    $table->string('email')->unique(); // O 'unique' impede emails duplicados no banco
    $table->timestamps(); // Cria as colunas 'created_at' e 'updated_at' magicamente
});
```

Agora, abra a migration `_create_pedidos_table.php`:

```php
Schema::create('pedidos', function (Blueprint $table) {
    $table->id();
    
    // A mágica acontece aqui:
    $table->foreignId('cliente_id')->constrained('clientes')->onDelete('cascade');
    
    $table->decimal('valor', 8, 2); // 8 dígitos no total, 2 após a vírgula
    $table->timestamps();
});
```

**Por que usamos `constrained()->onDelete('cascade')`?**
* `constrained('clientes')`: Avisa ao MySQL que esta coluna é uma Chave Estrangeira que aponta para o `id` da tabela `clientes`.
* `onDelete('cascade')`: É uma regra de ouro de integridade. Se um Cliente for deletado do sistema, o banco apagará automaticamente todos os pedidos dele. Sem isso, você teria "pedidos órfãos" causando erros na sua aplicação.

Rode o comando para criar as tabelas físicas no MySQL:
```bash
php artisan migrate
```

### Passo 3: Ensinando os Models a conversarem
O banco já sabe que existe uma relação, mas o PHP ainda não. Precisamos declarar isso.

Abra o arquivo `app/Models/Cliente.php`:
```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cliente extends Model
{
    // O $fillable protege contra "Mass Assignment Vulnerability"
    // Ele diz ao Laravel: "apenas estas colunas podem ser preenchidas via formulário/código"
    protected $fillable = ['nome', 'email'];

    public function pedidos(): HasMany
    {
        return $this->hasMany(Pedido::class);
    }
}
```

Abra `app/Models/Pedido.php`:
```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pedido extends Model
{
    protected $fillable = ['cliente_id', 'valor'];

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }
}
```

**Por que definimos tipos de retorno (`: HasMany` e `: BelongsTo`)?**
Embora o PHP funcione sem eles, tipar os métodos ajuda a sua IDE (como o VS Code ou PhpStorm) a entender exatamente o que aquele método retorna, ativando o autocompletar inteligente e prevenindo bugs no futuro.

---

## 3. A Tarefa Prática (Sua PoC 1)

Agora que você entendeu o conceito e o porquê de cada linha, é hora de provar o conceito (Proof of Concept) sujando as mãos.

**Seu Desafio:** 
Você não vai usar Clientes e Pedidos. Você vai criar um mini-sistema de **Autores e Livros**.

**Requisitos da PoC:**
1. Crie um model `Autor` (com `nome` e `nacionalidade`) e um model `Livro` (com `titulo`, `ano_publicacao` e a chave estrangeira `autor_id`).
2. Faça as migrations correspondentes garantindo a deleção em cascata.
3. Configure os métodos `livros()` no Autor e `autor()` no Livro.
4. **O Teste de Fogo:** Abra o terminal interativo do Laravel (`php artisan tinker`) e execute os seguintes comandos para provar que seu código funciona:

```php
// Passo 1 da PoC: Crie um Autor
$autor = App\Models\Autor::create(['nome' => 'J.R.R. Tolkien', 'nacionalidade' => 'Britânico']);

// Passo 2 da PoC: Crie dois livros para este autor usando o relacionamento!
$autor->livros()->create(['titulo' => 'O Hobbit', 'ano_publicacao' => 1937]);
$autor->livros()->create(['titulo' => 'O Senhor dos Anéis', 'ano_publicacao' => 1954]);

// Passo 3 da PoC: Busque os livros e veja a mágica do Eloquent
$autor->livros; 
```

**Critério de Sucesso:** Se o último comando retornar uma coleção na sua tela mostrando os dois livros conectados ao autor, sua PoC da Semana 1 está validada e concluída com sucesso!