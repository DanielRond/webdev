# Tutorial Semana 2: O Vilão da Performance (Problema N+1 e Eager Loading)

Nesta segunda semana, vamos resolver o maior pesadelo de performance de quem utiliza ORMs (como o Eloquent do Laravel, o Hibernate do Java ou o Prisma do Node): o temido **Problema N+1**.

Na semana passada, você aprendeu como conectar tabelas. Porém, o jeito "padrão" que o Laravel busca esses dados relacionados pode estrangular o banco de dados da sua aplicação quando a quantidade de dados cresce. Vamos entender o **porquê** disso acontecer e como resolver.

---

## 1. O Conceito: O que é o Problema N+1?

### A Analogia Ilustrada
Imagine que você é o gerente de um restaurante e pede para um garçom buscar o prato principal de 10 mesas diferentes na cozinha.
* **O Jeito Preguiçoso (*Lazy Loading*):** O garçom vai até a mesa 1, anota o pedido, vai na cozinha, pega o prato e entrega. Depois vai na mesa 2, vai na cozinha, pega o prato e volta... Ele faz **10 viagens completas de ida e volta**. Cansativo e ineficiente, certo?
* **O Jeito Ansioso/Antecipado (*Eager Loading*):** O garçom anota os pedidos das 10 mesas de uma vez só, vai na cozinha, coloca os 10 pratos em um carrinho grande e traz todos em **uma única viagem**. Muito mais rápido!

### A "Tradução" SQL
Quando você roda `$clientes = Cliente::all()` e depois faz um loop (`foreach`) para exibir os pedidos de cada cliente, o Laravel usa o **Lazy Loading** (Carregamento Preguiçoso).

O banco de dados executa isso:
```sql
SELECT * FROM clientes; -- (Esta é a query "1")
```
E depois, para cada cliente encontrado, ele dispara uma nova query:
```sql
SELECT * FROM pedidos WHERE cliente_id = 1; -- (Estas são as queries "N")
SELECT * FROM pedidos WHERE cliente_id = 2;
SELECT * FROM pedidos WHERE cliente_id = 3;
-- Se você tiver 100 clientes, o banco receberá 101 requisições seguidas!
```
Isso é o que chamamos de **Problema N+1** (1 query para buscar a entidade principal, mais N queries para buscar os relacionamentos).

---

## 2. A Solução: Eager Loading no Laravel

Para resolver isso e fazer a "única viagem com o carrinho", o Eloquent fornece o método **`with()`**. 

Quando você escreve `$clientes = Cliente::with('pedidos')->get();`, você está dizendo ao Laravel: *"Ei, já sei que vou precisar dos pedidos. Traga todos eles de uma vez!"*.

Por trás dos panos, o Laravel faz apenas **duas consultas**, usando a cláusula `IN` do SQL:
```sql
SELECT * FROM clientes;
SELECT * FROM pedidos WHERE cliente_id IN (1, 2, 3...); -- Traz tudo de uma vez!
```
Depois, o próprio Laravel pega esses resultados e os "costura" inteligentemente na memória do PHP. Quando você fizer o seu `foreach`, ele não baterá mais no banco de dados!

---

## 3. A Tarefa Prática (Sua PoC 2)

Na semana passada, você construiu um mini-sistema de **Autores e Livros**. Agora vamos provar o problema de performance e aplicar a solução diretamente na prática!

**O Cenário de Teste:**
Para ver o problema N+1 acontecer, primeiro vamos criar um pequeno script que cria vários autores e livros de uma só vez, e depois usaremos uma ferramenta de auditoria de banco do próprio Laravel para espionar as queries SQL.

### Passo 1: Ouvir as batidas no banco
Abra o seu terminal interativo:
```bash
php artisan tinker
```
Cole o seguinte comando para ligar o "escutador" de queries SQL. Toda vez que o Laravel fizer uma consulta ao banco, ela será impressa em vermelho/amarelo na sua tela:
```php
DB::listen(function ($query) { dump("SQL EXECUTADO: " . $query->sql); });
```

### Passo 2: Criando massa de dados
Cole este pequeno loop para gerarmos rapidamente 5 novos Autores, cada um com 2 livros (assim teremos volume suficiente para ver o problema).
```php
for ($i = 1; $i <= 5; $i++) {
    $a = App\Models\Autor::create(['nome' => "Autor Teste $i", 'nacionalidade' => 'BR']);
    $a->livros()->create(['titulo' => "Livro A do $i", 'ano_publicacao' => 2020]);
    $a->livros()->create(['titulo' => "Livro B do $i", 'ano_publicacao' => 2021]);
}
```
*(Você verá várias queries de `INSERT` na tela. Ignore-as por enquanto, o foco é a leitura)*.

### Passo 3: O Pesadelo (Lazy Loading)
Agora vamos buscar todos os autores e tentar listar seus livros **sem otimização**. Cole este bloco:
```php
$autores = App\Models\Autor::all(); // Query 1

foreach ($autores as $autor) {
    // Isso vai disparar uma nova query no banco para CADA autor impresso!
    echo "O autor " . $autor->nome . " escreveu " . $autor->livros->count() . " livros.
";
}
```
🚨 **Preste muita atenção no console:** Você verá uma enxurrada de textos `SQL EXECUTADO: select * from livros where autor_id = X`. Isso é o vilão N+1 esgotando a performance do seu banco!

### Passo 4: O Herói (Eager Loading)
Agora vamos consertar. Cole este bloco, usando o `with()` para fazer o Carregamento Ansioso (Eager Loading):
```php
// O 'with' traz os relacionamentos antecipadamente.
$autoresOtimizados = App\Models\Autor::with('livros')->get(); // A mágica acontece aqui!

foreach ($autoresOtimizados as $autor) {
    // Agora, acessar $autor->livros lê direto da memória, o banco nem pisca!
    echo "O autor " . $autor->nome . " escreveu " . $autor->livros->count() . " livros.
";
}
```
✅ **Critério de Sucesso:** Se, no Passo 4, o seu terminal imprimir a frase de todos os autores disparando **APENAS DUAS queries SQL** no console (uma para `autores` e uma para `livros` com a cláusula `in`), sua PoC da Semana 2 está validada! Você acaba de aprender a salvar servidores inteiros de caírem por excesso de processamento.
