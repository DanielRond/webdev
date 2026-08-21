# Semana 2: Eliminando o Problema N+1 (Performance)

Nesta semana, você aprenderá a diagnosticar e resolver o maior vilão de performance em ORMs: o problema de consultas excessivas (N+1), utilizando o Carregamento Ansioso (*Eager Loading*).

---

## 1. A Analogia Ilustrada
Imagine que você é um gerente de restaurante e precisa que um garçom busque o prato principal de 10 mesas diferentes na cozinha.
* **Lazy Loading (Lento):** O garçom vai até a mesa 1, pergunta o pedido, vai na cozinha buscar o prato e entrega. Depois vai na mesa 2, vai na cozinha e volta... Ele faz **10 viagens de ida e volta**. Extremamente ineficiente.
* **Eager Loading (Otimizado):** O garçom anota o pedido de todas as 10 mesas de uma vez só, vai na cozinha, coloca todos os pratos em um carrinho grande e serve as 10 mesas em **uma única viagem**.

---

## 2. A "Tradução" SQL (PostgreSQL) ➔ Eloquent (Laravel)

### O Problema (Lazy Loading):
O Laravel faz uma query para listar os clientes, e depois **uma query para cada cliente** para descobrir seus pedidos:
```sql
SELECT * FROM clientes; -- Query 1
SELECT * FROM pedidos WHERE cliente_id = 1; -- Query 2
SELECT * FROM pedidos WHERE cliente_id = 2; -- Query 3
-- ...e assim por diante. Se forem 100 clientes, serão 101 queries!
```

### A Solução (Eager Loading):
O Laravel faz apenas **duas queries** no total, utilizando o operador `IN` que você aprende no PostgreSQL:
```sql
SELECT * FROM clientes; -- Query 1
SELECT * FROM pedidos WHERE cliente_id IN (1, 2, 3, 4, 5...); -- Query 2 (Traz tudo de uma vez!)
```

Em Eloquent, fazemos isso usando o método `with()`:
```php
$clientes = Cliente::with('pedidos')->get(); // Faz apenas as 2 queries acima!
```

---

## 3. O Guia de Código Passo a Passo

### Passo 1: Habilitar o Monitor de Consultas no Tinker
Para provar que estamos reduzindo as consultas ao banco, usaremos o método estático `DB::listen` no Laravel para "ouvir" e imprimir no console cada query SQL que está sendo disparada por baixo dos panos.

---

## 4. A Execução no Tinker (A sua PoC 2)

Abra o Laravel Tinker:
```bash
php artisan tinker
```

Cole este código para configurar o monitor de banco de dados temporário (ele imprimirá a query SQL toda vez que o Eloquent interagir com o PostgreSQL):
```php
DB::listen(function ($query) { dump("SQL EXECUTADO: " . $query->sql); });
```

### Teste 1: Simular o cenário ineficiente (Lazy Loading)
Execute o seguinte bloco de código:
```php
// Busca 3 clientes
$clientes = App\Models\Cliente::limit(3)->get();

// Percorre os clientes exibindo os pedidos (Dispara consultas extras)
foreach ($clientes as $cliente) {
    $pedidos = $cliente->pedidos; // Dispara uma query de pedidos para CADA linha do loop
}
```
*Observe a saída do console:* Você verá 1 query para clientes, e depois mais 3 queries separadas para buscar os pedidos. (Total: 4 queries).

### Teste 2: Executar o cenário otimizado (Eager Loading)
Agora execute a mesma busca, mas usando o `with()`:
```php
$clientesOtimizados = App\Models\Cliente::with('pedidos')->limit(3)->get();

foreach ($clientesOtimizados as $cliente) {
    $pedidos = $cliente->pedidos; // Não dispara NENHUMA query nova aqui! O dado já está na memória.
}
```
*Observe a saída do console:* Você verá apenas **2 queries** no total, provando a otimização fantástica de performance!
