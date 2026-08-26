# Tutorial Semana 4: Transações Seguras de Banco (Integridade)

Nesta semana, você aprenderá a blindar o seu banco de dados contra falhas de sistema usando transações, garantindo que operações complexas só sejam salvas se tudo der 100% certo [cite: 8].

---

## 1. O Conceito: A Analogia da Transferência Bancária

Para entender a importância das transações, pense em uma transferência bancária entre duas pessoas [cite: 8]:
1. O sistema retira R$ 50,00 da conta do **Carlos** [cite: 8].
2. O sistema tenta adicionar R$ 50,00 na conta da **Ana** [cite: 8].

Imagine o desastre se o sistema do banco cai exatamente após o Passo 1: o dinheiro do Carlos sumiu e a Ana não recebeu nada [cite: 8]! 

Uma **Transação de Banco de Dados** cria uma cápsula de segurança inquebrável ao redor dessas etapas [cite: 8]. Se houver qualquer erro no Passo 2, a transação executa um **Rollback** (desfaz a retirada de dinheiro do Carlos, voltando ao estado original) [cite: 8]. O dinheiro só é gravado de verdade no banco (um **Commit**) se os dois passos forem concluídos com sucesso absoluto [cite: 8].

### A "Tradução" SQL
No banco de dados (MySQL ou PostgreSQL), para blindar essas alterações, usamos três comandos principais [cite: 8]:
```sql
BEGIN; -- Inicia a cápsula de segurança
UPDATE contas SET saldo = saldo - 50 WHERE id = 1;

-- Se ocorrer erro, executamos:
ROLLBACK; -- Desfaz tudo desde o 'BEGIN'

-- Se der tudo certo, executamos:
COMMIT; -- Salva as alterações de forma permanente
```

### O Eloquent (Laravel)
No Laravel, não precisamos escrever `BEGIN`, `COMMIT` ou `ROLLBACK` manualmente. O Eloquent simplifica isso através do método `DB::transaction()` [cite: 8]. Ele recebe uma *closure* (uma função anônima) e gerencia o início, o commit e o rollback automaticamente caso qualquer falha (Exception) ocorra dentro daquele bloco de código [cite: 8].

```php
use Illuminate\Support\Facades\DB;

DB::transaction(function () {
    // Se qualquer uma destas linhas disparar um erro, o Laravel limpa o banco automaticamente! [cite: 8]
    $carlos->sacar(50); [cite: 8]
    $ana->depositar(50); [cite: 8]
});
```

---

## 2. A Tarefa Prática (Sua PoC 4 Melhorada)

Para a sua PoC, vamos continuar usando o nosso sistema de **Autores e Livros**, mas com um upgrade fantástico: vamos escutar o banco de dados para **ver** o `ROLLBACK` e o `COMMIT` acontecendo ao vivo!

O cenário é o seguinte: Uma editora só cadastra um novo Autor no sistema se já tiver um Livro de estreia pronto. Se a criação do livro falhar, o Autor não pode ficar salvo sozinho no banco (precisa sofrer Rollback).

### Passo 1: Preparando o Terreno
Abra o Laravel Tinker no terminal [cite: 8]:
```bash
php artisan tinker
```

Ligue o nosso "espião" de Queries (como fizemos na Semana 2) para vermos os comandos de transação:
```php
use Illuminate\Support\Facades\DB; // Necessário para os próximos passos
DB::listen(function ($query) { dump("SQL EXECUTADO: " . $query->sql); });
```

### Passo 2: O Desastre Simulado (Testando o Rollback)
Copie e cole o bloco abaixo. Nós vamos criar um autor, mas forçar um erro de código (`throw new \Exception`) logo antes de criar o livro [cite: 8].

```php
use App\Models\Autor;
use App\Models\Livro;

$totalAntes = Autor::count(); // Anota quantos autores existem [cite: 8]

try {
    DB::transaction(function () {
        // Passo 1: Cria um novo autor
        $autor = Autor::create([
            'nome' => 'George R. R. Martin',
            'nacionalidade' => 'Americano'
        ]);

        // Passo 2: Forçamos um erro catastrófico simulado (ex: API da editora caiu) [cite: 8]
        throw new \Exception("Falha na API da Editora. Não foi possível registrar o livro!"); [cite: 8]

        // Passo 3: Cria o pedido (Este passo NUNCA será alcançado) [cite: 8]
        $autor->livros()->create(['titulo' => 'Os Ventos de Inverno', 'ano_publicacao' => 2026]);
    });
} catch (\Exception $e) {
    dump("ERRO CAPTURADO: " . $e->getMessage()); [cite: 8]
}

$totalDepois = Autor::count(); // Verifica quantos autores existem agora [cite: 8]
```

🚨 **Analise a sua tela:**
1. Você verá `SQL EXECUTADO: "start transaction"` (o equivalente ao `BEGIN`).
2. Você verá o `insert` do Autor George R. R. Martin.
3. Você verá o Erro sendo impresso.
4. E a mágica: Você verá `SQL EXECUTADO: "rollback"`.
5. Se você conferir `$totalAntes` e `$totalDepois`, eles **serão exatamente iguais**. O autor foi apagado instantaneamente para proteger a integridade do sistema!

### Passo 3: O Sucesso (Testando o Commit)
Agora, vamos fazer dar certo. Cole o bloco abaixo (sem o erro intencional):

```php
DB::transaction(function () {
    $autor = Autor::create([
        'nome' => 'J. K. Rowling',
        'nacionalidade' => 'Britânica'
    ]);

    $autor->livros()->create(['titulo' => 'Harry Potter e a Pedra Filosofal', 'ano_publicacao' => 1997]);
});
```

✅ **Critério de Sucesso:**
Observe as queries geradas no terminal. Você verá `start transaction`, os dois `inserts` (um para a tabela autores, outro para livros), e no final, um triunfante `SQL EXECUTADO: "commit"`. O sistema garantiu que ambos foram salvos juntos! Se você conseguir ver o `commit` e o `rollback` em ação no seu terminal, sua PoC 4 está finalizada com sucesso!
