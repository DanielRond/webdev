# Tutorial Semana 5: Validação Isolada com Form Requests

Nesta semana, daremos início à **Fase 2 (APIs REST)**. Você aprenderá a aplicar o princípio de responsabilidade única criando classes dedicadas exclusivamente a proteger a entrada de dados da sua aplicação.

---

## 1. O Conceito: A Analogia do Segurança de Balada
Imagine que você é o organizador de uma balada VIP com um show exclusivo.
* **Controller:** É o palco do show onde o cantor se apresenta. O cantor deve se preocupar apenas em cantar, e não em vigiar a fila.
* **Form Request:** É o **segurança na porta**. Ele confere a identidade dos clientes na fila, barra os menores de idade, confere os ingressos e rejeita quem estiver irregular ali mesmo, impedindo que entrem na festa.

Sem o Form Request (segurança), o cantor teria que parar o show a cada 2 minutos para conferir identidades no palco.

### A "Tradução" para o Código
Se você fizesse a validação manualmente dentro do seu Controller em PHP clássico, a lógica ficaria suja e poluída:
```php
public function store(Request $request) {
    if (!isset($request->nome) || strlen($request->nome) < 3) {
        return response()->json(['erro' => 'Nome inválido'], 422);
    }
    if (!is_numeric($request->preco) || $request->preco < 0) {
        return response()->json(['erro' => 'Preço inválido'], 422);
    }
    // ... Código real de inserção ficaria escondido lá embaixo
}
```

No Laravel, o seu Controller **nunca vê dados inválidos**. Ele só é executado se a requisição já tiver sido aprovada pelo **Form Request**. Se os dados forem inválidos, o próprio Form Request barra a entrada, retorna o erro 422 automaticamente e o Controller nem fica sabendo do que aconteceu:
```php
// No controller, os dados já chegam 100% limpos, seguros e validados!
public function store(LivroRequest $request) {
    Livro::create($request->validated());
    return response()->json(['message' => 'Livro criado com sucesso!'], 201);
}
```

---

## 2. Passo a Passo Detalhado da Implementação

Para a sua PoC, vamos voltar ao nosso mini-sistema de **Autores e Livros**. A nossa meta é criar uma rota de API (um endpoint) para cadastrar novos livros, blindando essa rota com um Form Request.

### Passo 1: Criando a Classe de Validação (O Segurança)
Saia do Tinker. No terminal normal do seu projeto, digite:
```bash
php artisan make:request LivroRequest
```

### Passo 2: Configurando as Regras e Mensagens
Vá até a pasta `app/Http/Requests/` e abra o arquivo `LivroRequest.php`.
Vamos definir três coisas importantes: 
1. **Autorização:** Permitir que o Form Request funcione.
2. **Regras:** Definir *o que* deve ser validado.
3. **Mensagens:** Definir a tradução dos erros (para não expor regras em inglês para os usuários da API).

```php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LivroRequest extends FormRequest
{
    // 1. Libera o acesso para qualquer pessoa (pois ainda não temos login)
    public function authorize(): bool
    {
        return true; 
    }

    // 2. Define as regras rigorosas
    public function rules(): array
    {
        return [
            'titulo' => 'required|string|min:3|max:100',
            'ano_publicacao' => 'required|integer|min:1500|max:2026',
            'autor_id' => 'required|exists:autores,id', // O Laravel vai no banco verificar se esse ID existe!
        ];
    }
    
    // 3. (Opcional) Mensagens amigáveis para quem consome a API
    public function messages(): array
    {
        return [
            'titulo.required' => 'Todo livro precisa de um título.',
            'titulo.min' => 'O título deve ter pelo menos 3 caracteres.',
            'ano_publicacao.min' => 'Não aceitamos livros publicados antes do ano de 1500.',
            'autor_id.exists' => 'Você tentou vincular um autor que não existe no nosso banco de dados.',
        ];
    }
}
```

### Passo 3: O Palco (O Controller)
Agora precisamos criar o Controller para receber os dados limpos e salvar no banco.
```bash
php artisan make:controller Api/LivroController
```

Abra `app/Http/Controllers/Api/LivroController.php` e injete o seu segurança (`LivroRequest`) na função:
```php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LivroRequest; // Importando o segurança!
use App\Models\Livro;
use Illuminate\Http\JsonResponse;

class LivroController extends Controller
{
    // Repare na tipagem! Não usamos o "Request" genérico, usamos o nosso "LivroRequest"
    public function store(LivroRequest $request): JsonResponse
    {
        // $request->validated() pega APENAS os campos aprovados na rules()
        $livro = Livro::create($request->validated());
        
        return response()->json([
            'mensagem' => 'Livro cadastrado com sucesso!',
            'dados' => $livro
        ], 201);
    }
}
```

### Passo 4: Expondo a Porta (As Rotas)
Abra o arquivo `routes/api.php` e registre a URL por onde a requisição vai chegar:
```php
use App\Http\Controllers\Api\LivroController;
use Illuminate\Support\Facades\Route;

Route::post('/livros', [LivroController::class, 'store']);
```

*(Se o arquivo `routes/api.php` não existir na sua versão do Laravel 11, ative-o com o comando `php artisan install:api`)*.

---

## 3. A Tarefa Prática (Sua PoC 5)

Como estamos construindo uma API, vamos usar o aplicativo de linha de comando `curl` para tentar invadir o nosso sistema com dados ruins e verificar se o nosso Form Request barra a entrada e cospe as mensagens que escrevemos.

**O Teste de Fogo (Requisição Inválida):**
Primeiro, verifique se o seu servidor do Laravel está rodando (`php artisan serve`). Depois, abra outra aba do terminal e rode este comando (ele tenta cadastrar um título muito curto e um autor ID absurdo):

```bash
curl -X POST http://localhost:8000/api/livros \
     -H "Accept: application/json" \
     -H "Content-Type: application/json" \
     -d '{"titulo": "A", "ano_publicacao": 1990, "autor_id": 99999}'
```

**Critério de Sucesso:**
Se a sua PoC estiver configurada corretamente, o banco de dados NÃO salvará nada. O terminal imprimirá a resposta HTTP do Laravel bloqueando a ação, devolvendo algo parecido com isto (note o status 422 e as mensagens personalizadas!):
```json
{
  "message": "Todo livro precisa de um título. (and 1 more error)",
  "errors": {
    "titulo": ["O título deve ter pelo menos 3 caracteres."],
    "autor_id": ["Você tentou vincular um autor que não existe no nosso banco de dados."]
  }
}
```
