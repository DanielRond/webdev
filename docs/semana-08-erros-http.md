# Semana 8: Padronização Global de Erros HTTP

Nesta semana, você aprenderá a criar uma API robusta de nível de produção, capturando falhas comuns e exceções silenciosas de sistema para transformá-las em retornos JSON amigáveis e estruturados.

---

## 1. A Analogia Ilustrada
Imagine que você entra em uma grande loja de móveis e pergunta para um atendente de forma amigável: "Vocês têm o sofá modelo X-999 no estoque?".
* **Sem tratamento de erros (Ruim):** O atendente trava, arregala os olhos, cai no chão desmaiado e a loja inteira começa a tocar um alarme ensurdecedor. É isso que acontece quando sua aplicação mostra aquela tela vermelha clássica de erro do PHP (Stacktrace) para o cliente.
* **Com tratamento global (Profissional):** O atendente calmamente consulta o tablet e responde de forma educada: "Desculpe, o sofá X-999 não foi encontrado no nosso sistema.". Isso é o que faremos hoje.

---

## 2. A "Tradução" de Erros de Sistema ➔ JSON Tratado

### O Erro Bruto do Banco (Sem tratamento):
Ao buscar um ID de produto que não existe com `findOrFail($id)`, se não houver tratamento, a API quebra exibindo uma exceção complexa interna:
`Illuminate\Database\Eloquent\ModelNotFoundException: No query results for model [App\Models\Produto] 99999`
O cliente recebe status 500 (Erro de servidor), expondo caminhos de pastas internas da sua máquina (falha grave de segurança).

### O Erro Tratado (Com padronização):
O Laravel captura a falha de banco silenciosamente e responde um JSON elegante de erro padronizado:
```json
{
  "status": "error",
  "message": "O registro solicitado não foi encontrado em nossa base de dados."
}
```
E retorna o código de status HTTP correto: **404 Not Found**.

---

## 3. O Guia de Código Passo a Passo

No Laravel 11, o tratamento global de erros é centralizado diretamente no arquivo de configuração do fluxo de inicialização da aplicação: `bootstrap/app.php`.

### Passo 1: Configurar a captura global no bootstrap/app.php
Abra o arquivo `bootstrap/app.php` no seu VS Code. Vamos usar o método `withExceptions` para interceptar as falhas de modelo não encontrado (`ModelNotFoundException` ou `NotFoundHttpException` que ocorrem em buscas de banco de dados):

```php
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basename: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        //
    })
    ->withExceptions(function (Exceptions $exceptions) {
        
        // Intercepta erros quando um registro não for encontrado no banco de dados
        $exceptions->render(function (NotFoundHttpException $e, $request) {
            if ($request->is('api/*')) { // Garante que só afeta retornos de API
                return response()->json([
                    'status' => 'error',
                    'message' => 'O recurso solicitado não existe ou não foi encontrado.'
                ], 404);
            }
        });
        
    })->create();
```

---

## 4. Testando a sua PoC 8

Para validar essa proteção, faça uma requisição buscando um ID absurdo que você sabe que não existe na tabela de produtos:

```bash
curl -X GET http://localhost:8000/api/produtos/99999      -H "Accept: application/json"
```

**Resultado Esperado:** O Laravel não vai "quebrar" nem enviar uma tela HTML gigante de erro. Ele responderá exatamente com o status **HTTP 404 Not Found** e o corpo:
```json
{
  "status": "error",
  "message": "O recurso solicitado não existe ou não foi encontrado."
}
```
