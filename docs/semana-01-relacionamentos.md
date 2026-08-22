# Semana 1: Relacionamentos Essenciais (hasMany & belongsTo)

Nesta semana, você aprenderá como conectar tabelas no banco de dados usando a Orientação a Objetos do Laravel (Eloquent ORM), em vez de escrever consultas SQL manuais complexas.

---

## 1. A Analogia Ilustrada
Imagine uma **Carteira** (Cliente) e os **Recibos** (Pedidos) guardados dentro dela.
* **hasMany (Tem Muitos):** Se você abrir a carteira, verá que ela possui múltiplos recibos de compra. A carteira *tem muitos* recibos.
* **belongsTo (Pertence a):** Se você pegar um recibo específico do chão, olhará para o topo e verá a qual carteira ele *pertence*. Ele não pode pertencer a duas carteiras diferentes ao mesmo tempo.

---

## 2. A "Tradução" SQL (PostgreSQL) ➔ Eloquent (Laravel)

No PostgreSQL, para buscar todos os pedidos do cliente de ID 1, você escreveria:
```sql
SELECT * FROM pedidos WHERE cliente_id = 1;
```

No Laravel, usando o Eloquent, você define isso como uma propriedade dinâmica. O Laravel faz a busca no banco de dados por trás dos panos:
```php
$cliente = Cliente::find(1);
$pedidos = $cliente->pedidos; // Eloquent faz a query SQL acima automaticamente!
```

---

## 3. O Guia de Código Passo a Passo

### Passo 1: Criar os Models e as Migrations
No terminal do seu projeto, crie os Models do Cliente e do Pedido junto com suas migrações:
```bash
php artisan make:model Cliente -m
php artisan make:model Pedido -m
```

### Passo 2: Definir a estrutura das Tabelas (Migrations)
Abra o arquivo de migração do Cliente (`database/migrations/..._create_clientes_table.php`) e adicione:
```php
Schema::create('clientes', function (Blueprint $table) {
    $table->id();
    $table->string('nome');
    $table->string('email')->unique();
    $table->timestamps();
});
```

Abra a migração do Pedido (`database/migrations/..._create_pedidos_table.php`) e configure a chave estrangeira:
```php
Schema::create('pedidos', function (Blueprint $table) {
    $table->id();
    // Cria a coluna 'cliente_id' como chave estrangeira apontando para 'clientes'
    $table->foreignId('cliente_id')->constrained('clientes')->onDelete('cascade');
    $table->decimal('valor', 8, 2);
    $table->timestamps();
});
```
Rode as migrações:
```bash
php artisan migrate
```

### Passo 3: Configurar os Relacionamentos nos Models
Abra o Model `app/Models/Cliente.php` e adicione o método `pedidos()`:
```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cliente extends Model
{
    protected $fillable = ['nome', 'email'];

    public function pedidos(): HasMany
    {
        return $this->hasMany(Pedido::class);
    }
}
```

Abra o Model `app/Models/Pedido.php` e adicione o método `cliente()`:
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

---

## 4. A Execução no Tinker (A sua PoC 1)

Abra o Laravel Tinker no terminal:
```bash
php artisan tinker
```

E execute os seguintes comandos PHP para testar a gravação e a consulta dos dados:

```php
// 1. Criar um cliente fictício
$cliente = App\Models\Cliente::create(['nome' => 'Gustavo Silva', 'email' => 'gustavo@email.com']);

// 2. Criar pedidos diretamente relacionados a esse cliente
$cliente->pedidos()->create(['valor' => 150.50]);
$cliente->pedidos()->create(['valor' => 89.90]);

// 3. Consultar todos os pedidos do cliente de forma reativa
$todosPedidos = $cliente->pedidos;
// O console irá imprimir uma coleção contendo os 2 pedidos criados!

// 4. Fazer o caminho inverso: buscar de quem é o pedido de ID 1
$pedido = App\Models\Pedido::find(1);
$donoDoPedido = $pedido->cliente;
// Retornará o objeto do cliente 'Gustavo Silva'
```