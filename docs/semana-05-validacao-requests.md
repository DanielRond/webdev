# Semana 5: Validação Isolada com Form Requests

Nesta semana, daremos início à **Fase 2 (APIs REST)**. Você aprenderá a aplicar o princípio de responsabilidade única criando classes dedicadas exclusivamente a proteger a entrada de dados da sua aplicação.

---

## 1. A Analogia Ilustrada
Imagine que você é o organizador de uma balada VIP com um show exclusivo.
* **Controller:** É o palco do show onde o cantor se apresenta. O cantor deve se preocupar apenas em cantar, e não em vigiar a fila.
* **Form Request:** É o **segurança na porta**. Ele confere a identidade dos clientes na fila, barra os menores de idade, confere os ingressos e rejeita quem estiver irregular ali mesmo, impedindo que entrem na festa.

Sem o Form Request (segurança), o cantor teria que parar o show a cada 2 minutos para conferir identidades no palco.

---

## 2. A "Tradução" de Validação Manual ➔ Form Request

Se você fizesse a validação manualmente dentro do seu Controller em PHP clássico:
```php
public function store(Request $request) {
    if (!isset($request->nome) || strlen($request->nome) < 3) {
        return response()->json(['erro' => 'Nome inválido'], 422);
    }
    if (!is_numeric($request->preco) || $request->preco < 0) {
        return response()->json(['erro' => 'Preço inválido'], 422);
    }
    // ... Código real de inserção
}
```

No Laravel, o seu Controller não conhece essas regras. Ele só é executado se a requisição já tiver sido aprovada pelo **Form Request**:
```php
// No controller, os dados já chegam 100% limpos e validados!
public function store(ProdutoRequest $request) {
    Produto::create($request->validated());
    return response()->json(['message' => 'Criado!'], 201);
}
```

---

## 3. O Guia de Código Passo a Passo

### Passo 1: Criar o Model e Migration do Produto
No terminal:
```bash
php artisan make:model Produto -m
```

Abra a migração (`database/migrations/..._create_produtos_table.php`):
```php
Schema::create('produtos', function (Blueprint $table) {
    $table->id();
    $table->string('nome');
    $table->decimal('preco', 8, 2);
    $table->timestamps();
});
```
Rode a migração:
```bash
php artisan migrate
```

Configure a propriedade fillable em `app/Models/Produto.php`:
```php
protected $fillable = ['nome', 'preco'];
```

### Passo 2: Criar a classe Form Request dedicada
No terminal:
```bash
php artisan make:request ProdutoRequest
```

### Passo 3: Configurar as regras de validação da Request
Abra `app/Http/Requests/ProdutoRequest.php`.
1. Mude o método `authorize()` para retornar `true` (permitindo que qualquer um envie os dados).
2. Configure as regras no método `rules()`:

```php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProdutoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Habilita o acesso a essa validação
    }

    public function rules(): array
    {
        return [
            'nome' => 'required|string|min:3',
            'preco' => 'required|numeric|min:0',
        ];
    }
    
    // Opcional: Mensagens customizadas em português!
    public function messages(): array
    {
        return [
            'nome.required' => 'O campo nome é obrigatório.',
            'nome.min' => 'O nome do produto deve ter pelo menos 3 caracteres.',
            'preco.required' => 'O preço é obrigatório.',
            'preco.numeric' => 'O preço precisa ser um valor numérico.',
            'preco.min' => 'O preço não pode ser menor que zero.',
        ];
    }
}
```

### Passo 4: Vincular a Request no Controller da API
Crie um controller de API para o produto:
```bash
php artisan make:controller Api/ProdutoController
```

Abra o controller `app/Http/Controllers/Api/ProdutoController.php` e adicione o método `store`:
```php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProdutoRequest;
use App\Models\Produto;
use Illuminate\Http\JsonResponse;

class ProdutoController extends Controller
{
    public function store(ProdutoRequest $request): JsonResponse
    {
        // Se o fluxo chegou aqui, os dados são válidos!
        $produto = Produto::create($request->validated());
        return response()->json($produto, 201);
    }
}
```

Configure a rota no arquivo `routes/api.php`:
```php
use App\Http\Controllers\Api\ProdutoController;

Route::post('/produtos', [ProdutoController::class, 'store']);
```

---

## 4. Testando a sua PoC 5

Desta vez, como estamos criando uma API REST, você pode testar simulando uma requisição HTTP via terminal usando o utilitário `curl` (que já vem nativo no seu sistema):

### Teste 1: Enviar dados inválidos para testar a rejeição automática
Rode este comando no seu terminal do computador:
```bash
curl -X POST http://localhost:8000/api/produtos      -H "Accept: application/json"      -H "Content-Type: application/json"      -d '{"nome": "Ab", "preco": -5.00}'
```

**Resultado Esperado:** O Laravel rejeitará a requisição antes mesmo de salvar no banco de dados e responderá com o status HTTP **422 Unprocessable Entity**, mostrando suas mensagens personalizadas:
```json
{
  "message": "O nome do produto deve ter pelo menos 3 caracteres. (and 1 more error)",
  "errors": {
    "nome": ["O nome do produto deve ter pelo menos 3 caracteres."],
    "preco": ["O preço não pode ser menor que zero."]
  }
}
```
