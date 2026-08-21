# Semana 4: Transações Seguras de Banco (Integridade)

Nesta semana, você aprenderá a blindar o seu banco de dados contra falhas de sistema usando transações, garantindo que operações complexas só sejam salvas se tudo der 100% certo.

---

## 1. A Analogia Ilustrada
Pense em uma transferência bancária entre duas pessoas:
1. O sistema retira R$ 50,00 da conta do **Carlos**.
2. O sistema tenta adicionar R$ 50,00 na conta da **Ana**.

Imagine se o sistema do banco cai exatamente após o Passo 1. O dinheiro do Carlos sumiu e a Ana não recebeu nada!
Uma **Transação de Banco** cria uma cápsula de segurança ao redor dessas etapas. Se houver um erro no Passo 2, a transação executa um **Rollback** (desfaz a retirada de dinheiro do Carlos, voltando ao estado original). O dinheiro só é gravado de verdade (**Commit**) se os dois passos forem concluídos com sucesso.

---

## 2. A "Tradução" SQL (PostgreSQL) ➔ Eloquent (Laravel)

No PostgreSQL, para blindar alterações, usamos:
```sql
BEGIN;
UPDATE contas SET saldo = saldo - 50 WHERE id = 1;
-- Se ocorrer erro, executamos:
ROLLBACK;
-- Se der tudo certo, executamos:
COMMIT;
```

No Laravel, o Eloquent simplifica isso através do método `DB::transaction()` que gerencia o início, o commit e o rollback automaticamente em caso de falhas (Exceptions):
```php
use Illuminate\Support\Facades\DB;

DB::transaction(function () {
    // Se qualquer uma dessas linhas disparar um erro, o Laravel limpa o banco automaticamente!
    $carlos->sacar(50);
    $ana->depositar(50);
});
```

---

## 3. O Guia de Código Passo a Passo

### Passo 1: Criar um cenário de teste com falha simulada
Abra o console do Laravel Tinker para simularmos o fluxo em que tentamos criar um Pedido para um Cliente, mas simulamos um erro logo após criar o Pedido para garantir que ele seja desfeito.

---

## 4. A Execução no Tinker (A sua PoC 4)

Abra o Laravel Tinker:
```bash
php artisan tinker
```

Execute o código abaixo, que tenta cadastrar um novo cliente e um pedido dentro de uma transação, mas força um erro intencional (`throw new Exception`) no meio do caminho:

```php
use Illuminate\Support\Facades\DB;
use App\Models\Cliente;
use App\Models\Pedido;

// Verifique a contagem de clientes antes de rodar
$totalAntes = Cliente::count();

try {
    DB::transaction(function () {
        // Passo 1: Cria um novo cliente
        $novoCliente = Cliente::create([
            'nome' => 'Cliente Transacional S/A',
            'email' => 'transacao@teste.com'
        ]);

        // Passo 2: Força um erro de código simulando que o sistema de pagamentos falhou
        throw new \Exception("Falha no servidor de pagamentos externa!");

        // Passo 3: Cria o pedido (Este passo nunca será alcançado)
        $novoCliente->pedidos()->create(['valor' => 999.00]);
    });
} catch (\Exception $e) {
    dump("ERRO CAPTURADO: " . $e->getMessage());
}

// Verifique a contagem de clientes após a falha
$totalDepois = Cliente::count();

dump("Total de clientes antes: " . $totalAntes);
dump("Total de clientes depois: " . $totalDepois);
```

### O que você vai comprovar?
Você verá que `Total antes` e `Total depois` terão **o mesmo valor**. O registro "Cliente Transacional S/A" foi apagado (sofreu Rollback) automaticamente pelo Laravel porque a exceção foi disparada, garantindo que você não tenha registros órfãos ou inconsistências no banco PostgreSQL.
