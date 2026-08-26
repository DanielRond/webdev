# Tutorial Semana 3: Automatizando a Criação de Dados (Factories & Seeds)

Nesta semana, você aprenderá a versionar o banco de dados da sua aplicação e a criar scripts automáticos para preencher o banco com centenas de dados de teste realistas em apenas um segundo [cite: 5]. Diga adeus aos testes manuais preenchendo formulários um por um!

---

## 1. O Conceito: A Analogia do Supermercado
Imagine que você é o arquiteto encarregado de inaugurar uma nova filial de um supermercado [cite: 5].
* **Migrations:** São a **planta baixa** do prédio [cite: 5]. Ela descreve onde ficam as prateleiras, as portas e as tomadas [cite: 5]. Se você enviar essa planta para outra cidade, construtores criarão um prédio idêntico [cite: 5].
* **Factories & Faker:** Representam a equipe de teste [cite: 5]. Em vez de contratar clientes reais para testar as filas antes de abrir, você coloca bonecos de teste infláveis com nomes fictícios nas prateleiras e carrinhos [cite: 5].
* **Seeds:** É o botão que infla todos esses bonecos de uma vez, deixando o mercado pronto para simular o dia de inauguração [cite: 5].

### A "Tradução" SQL
No PostgreSQL ou MySQL, para criar e preencher uma tabela de testes com dados manuais, você usaria comandos trabalhosos como:
```sql
CREATE TABLE produtos (id SERIAL PRIMARY KEY, nome VARCHAR(100));
INSERT INTO produtos (nome) VALUES ('Produto Falso 1'), ('Produto Falso 2');
```
No Laravel, as tabelas são declaradas em arquivos PHP orientados a objetos, e geramos dados falsos realistas através da biblioteca **Faker** [cite: 5]. O Faker gera dados que parecem reais (emails válidos, nomes, telefones, etc) [cite: 5]:
```php
$nomeFalso = $this->faker->name(); // Retorna ex: "Mariana Alencar" [cite: 5]
$emailFalso = $this->faker->unique()->safeEmail(); // Retorna ex: "mariana.alencar@example.net" [cite: 5]
```

---

## 2. Passo a Passo Detalhado da Implementação

Para manter a consistência com nossas semanas anteriores, vamos aplicar este conceito ao nosso sistema de **Autores e Livros**.

### Passo 1: Criando a Factory
No terminal, gere um arquivo de "fábrica" para estruturar como um Autor fictício deve ser gerado [cite: 5]:
```bash
php artisan make:factory AutorFactory --model=Autor
```
*Por que usar o `--model`?* Isso avisa o Laravel para já importar a classe correta do model dentro do arquivo gerado, poupando o seu tempo.

### Passo 2: Definindo o Molde (Faker)
Abra o arquivo gerado em `database/factories/AutorFactory.php` e ensine a fábrica a criar um autor usando os métodos do Faker:
```php
namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class AutorFactory extends Factory
{
    public function definition(): array
    {
        return [
            'nome' => $this->faker->name(),
            'nacionalidade' => $this->faker->country(), // Gera um país aleatório
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
```

### Passo 3: Configurando o Gatilho (O Seeder Principal)
A Factory ensina *como* fazer. O Seeder diz *quantos* fazer. 
Abra o arquivo `database/seeders/DatabaseSeeder.php` e use a sua factory recém-criada para pedir a geração de dados [cite: 5]:
```php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Autor;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Instancia a fábrica de Autores e cria 50 registros no banco
        Autor::factory()->count(50)->create();
    }
}
```

---

## 3. A Tarefa Prática (Sua PoC 3)

Agora é a sua vez de popular o seu banco de dados com informações em massa!

**Sua Missão:**
Além dos 50 autores gerados acima, crie também uma Factory para os **Livros** (`php artisan make:factory LivroFactory --model=Livro`).
1. Ensine a fábrica de livros a gerar títulos falsos (dica: use `$this->faker->sentence(3)` para uma frase curta) e anos de publicação aleatórios (`$this->faker->year()`).
2. Atribua um `autor_id` aleatório para cada livro. (Dica de ouro: você pode usar `Autor::inRandomOrder()->first()->id` para pegar um autor existente!).
3. Modifique o `DatabaseSeeder` para gerar 150 Livros falsos logo após a criação dos Autores.

**O Teste de Fogo (Execução no Terminal):**
No terminal, fora do Tinker, rode o comando mágico de reconfiguração de banco [cite: 5]:
```bash
php artisan migrate:fresh --seed
```

**O que esse comando faz por baixo dos panos?**
1. **migrate:fresh:** Deleta todas as tabelas existentes no seu banco (limpa a sujeira) [cite: 5].
2. **migrate:** Executa novamente todas as suas Migrations do zero, recriando as tabelas limpinhas [cite: 5].
3. **--seed:** Dispara o `DatabaseSeeder`, preenchendo o banco recém-criado com os registros falsos gerados pelo Faker em segundos [cite: 5].

**Critério de Sucesso:**
Abra o Tinker (`php artisan tinker`) e execute as contagens para provar que funcionou [cite: 5]:
```php
App\Models\Autor::count(); // Deve retornar 50
App\Models\Livro::count(); // Deve retornar 150
```
Se os números baterem, sua PoC da Semana 3 está concluída!
