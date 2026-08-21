# Semana 6: Formatação de Respostas com API Resources

Nesta semana, você aprenderá a desacoplar a estrutura das suas tabelas de banco de dados do formato de entrega das suas APIs, utilizando a camada de transformação **API Resources**.

---

## 1. A Analogia Ilustrada
Imagine que você trabalha na alfândega ou em um aeroporto.
* **O Banco de Dados (Tabela):** É a bagagem bruta e cheia de itens pessoais (senhas criptografadas, chaves de rastreamento, datas do sistema `created_at`).
* **API Resource:** É o scanner de raio-X que descompacta a mala e seleciona apenas o que é seguro e relevante mostrar para o passageiro ou fiscal no painel. Ele filtra e reorganiza as informações de maneira apresentável e limpa.

---

## 2. A "Tradução" JSON Direto do Banco ➔ API Resource

### Resposta Padrão do Banco (Sem Resource):
Ao converter o objeto do banco direto para JSON, você vaza dados desnecessários ou brutos do PostgreSQL:
```json
{
  "id": 1,
  "nome": "TECLADO MECANICO",
  "preco": "350.00",
  "created_at": "2026-08-20T14:42:39.000000Z",
  "updated_at": "2026-08-20T14:42:39.000000Z"
}
```

### Resposta Formatada (Com API Resource):
Você formata os dados deixando-os no padrão ideal para o frontend (Vue.js) consumir, ocultando datas brutas e formatando valores monetários:
```json
{
  "id": 1,
  "titulo_produto": "TECLADO MECANICO",
  "preco_formatado": "R$ 350,00",
  "data_cadastro": "20/08/2026"
}
```

---

## 3. O Guia de Código Passo a Passo

### Passo 1: Criar a classe API Resource
No terminal da sua aplicação, rode o comando:
```bash
php artisan make:resource ProdutoResource
```

### Passo 2: Definir a máscara de dados que será entregue ao cliente
Abra o arquivo `app/Http/Resources/ProdutoResource.php` e mude o método `toArray`:
```php
namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProdutoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        // Mapeamos as chaves que o frontend receberá com os valores do banco
        return [
            'id' => $this->id,
            'titulo' => strtoupper($this->nome), // Força letras maiúsculas
            'preco_bruto' => (float) $this->preco,
            'preco_formatado' => 'R$ ' . number_format($this->preco, 2, ',', '.'),
            'cadastrado_em' => $this->created_at->format('d/m/Y H:i'),
        ];
    }
}
```

### Passo 3: Utilizar o Resource no Controller
Abra o controller `app/Http/Controllers/Api/ProdutoController.php` e adicione o método `index` (para listar) e `show` (para exibir um):
```php
use App\Http\Resources\ProdutoResource;
use App\Models\Produto;

// Listar todos com Resource
public function index()
{
    $produtos = Produto::all();
    return ProdutoResource::collection($produtos);
}

// Exibir um específico
public function show($id)
{
    $produto = Produto::findOrFail($id);
    return new ProdutoResource($produto);
}
```

Adicione as rotas correspondentes em `routes/api.php`:
```php
Route::get('/produtos', [ProdutoController::class, 'index']);
Route::get('/produtos/{id}', [ProdutoController::class, 'show']);
```

---

## 4. Testando a sua PoC 6

Crie um produto no Tinker ou no banco e faça uma requisição GET no terminal:

```bash
curl -X GET http://localhost:8000/api/produtos
```

**Resultado Esperado:** O retorno JSON virá formatado exatamente conforme as regras declaradas no seu `ProdutoResource`, garantindo que o seu frontend receba chaves limpas (`titulo` em vez de `nome`, preço formatado com vírgula) prontinho para exibição na tela!
