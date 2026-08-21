# Semana 7: Autenticação e Proteção de Rotas (Sanctum)

Nesta semana, você aprenderá sobre segurança em APIs modernas. Configuraremos o pacote nativo **Laravel Sanctum** para proteger rotas privadas, exigindo que o cliente envie um Token Bearer válido para acessar os dados.

---

## 1. A Analogia Ilustrada
Pense na segurança de um camarote exclusivo de uma festa:
1. **Login:** Você vai à bilheteria, mostra sua identidade (e-mail/senha) e paga. O atendente te entrega uma **pulseira VIP** (Token de Acesso).
2. **Navegação (Requisições protegidas):** Você não precisa mostrar sua identidade toda vez que pedir uma bebida no bar do camarote. Você apenas mostra a pulseira que está no seu pulso (**Bearer Token**).
3. **Bloqueio:** Se alguém tentar entrar no camarote sem a pulseira, os seguranças barram na hora (**HTTP 401 Unauthorized**).

---

## 2. A "Tradução" de Sessão Tradicional ➔ API Token (Sanctum)

Na web tradicional (PHP clássico), o servidor salva seu estado em uma sessão (`$_SESSION`) usando Cookies no navegador.
Em APIs REST, a comunicação é **stateless** (sem estado). O servidor não sabe quem você é entre uma requisição e outra. Portanto, você deve enviar o Token no cabeçalho de cada requisição HTTP:

```http
Authorization: Bearer 1|qY8tF0O9H9Z2kHl7pNx...
```

---

## 3. O Guia de Código Passo a Passo

### Passo 1: Preparar as rotas da API com Middleware
Abra `routes/api.php` e proteja a rota de criação de produtos criada na Semana 5 envolvendo-a no grupo de middleware do Sanctum:

```php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ProdutoController;

// Rota pública (qualquer um pode ver)
Route::get('/produtos', [ProdutoController::class, 'index']);

// Rotas privadas (só quem tem o Token Bearer pode acessar)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/produtos', [ProdutoController::class, 'store']);
});
```

---

## 4. A Execução e Teste (A sua PoC 7)

Para testar este fluxo de ponta a ponta, usaremos o Tinker para simular a criação de um usuário e geração do token, e em seguida usaremos o `curl` para testar os acessos.

### Passo 1: Criar um Usuário e Gerar seu Token no Tinker
Abra o Laravel Tinker:
```bash
php artisan tinker
```
Execute os comandos para cadastrar um usuário e emitir o Token:
```php
// Criar o usuário de teste
$user = App\Models\User::create([
    'name' => 'Desenvolvedor Junior',
    'email' => 'dev@junior.com',
    'password' => Hash::make('senha123')
]);

// Gerar o Token de acesso via Sanctum
$tokenResult = $user->createToken('token-teste');

// Imprimir o texto plano do token no terminal para copiarmos
echo $tokenResult->plainTextToken;
// Exemplo de saída: "1|k9v7J2H73K..."
```
Copie esse token gerado!

### Teste 2: Fazer requisição SEM o Token (Bloqueado)
No terminal do seu computador, tente cadastrar um produto sem enviar credenciais:
```bash
curl -X POST http://localhost:8000/api/produtos      -H "Accept: application/json"      -H "Content-Type: application/json"      -d '{"nome": "Mouse Gamer", "preco": 120.00}'
```
**Resultado esperado:** Você receberá o código **HTTP 401 Unauthorized** com a mensagem `"message": "Unauthenticated"`. A rota está protegida!

### Teste 3: Fazer requisição COM o Token Bearer correto (Sucesso)
Agora, envie o token copiado anteriormente no cabeçalho `Authorization`:
```bash
curl -X POST http://localhost:8000/api/produtos      -H "Accept: application/json"      -H "Content-Type: application/json"      -H "Authorization: Bearer COLOQUE_O_TOKEN_AQUI"      -d '{"nome": "Mouse Gamer", "preco": 120.00}'
```
**Resultado esperado:** Você receberá o código **HTTP 201 Created** e os dados do produto salvo no banco! Você acabou de proteger seu backend de forma profissional.
