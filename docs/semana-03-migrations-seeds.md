# Semana 3: Migrations, Factories & Seeds (Popular Dados)

Nesta semana, você aprenderá a versionar o banco de dados da sua aplicação e a criar scripts automáticos para preencher o banco com centenas de dados de teste realistas em apenas um segundo.

---

## 1. A Analogia Ilustrada
Imagine que você é o arquiteto de uma nova filial de supermercado.
* **Migrations:** São a **planta baixa** do prédio. Ela descreve onde ficam as prateleiras, as portas e as tomadas. Se você enviar essa planta para outra cidade, construtores criarão um prédio idêntico.
* **Factories & Faker:** É a equipe de teste. Em vez de contratar clientes reais para testar as filas antes de abrir, você coloca bonecos de teste infláveis com nomes fictícios nas prateleiras e carrinhos.
* **Seeds:** É o botão que infla todos esses bonecos de uma vez, deixando o mercado pronto para simular o dia de inauguração.

---

## 2. A "Tradução" SQL (PostgreSQL) ➔ Eloquent (Laravel)

No PostgreSQL, para criar e preencher uma tabela de testes com dados manuais, você usaria comandos como:
```sql
CREATE TABLE produtos (id SERIAL PRIMARY KEY, nome VARCHAR(100));
INSERT INTO produtos (nome) VALUES ('Produto Falso 1'), ('Produto Falso 2');
```

No Laravel, as tabelas são declaradas em arquivos PHP orientados a objetos, e geramos dados falsos realistas através da biblioteca **Faker**:
```php
// Faker gera dados que parecem reais (emails válidos, nomes, telefones, etc)
$nomeFalso = $this->faker->name(); // Retorna ex: "Mariana Alencar"
$emailFalso = $this->faker->unique()->safeEmail(); // Retorna ex: "mariana.alencar@example.net"
```

---

## 3. O Guia de Código Passo a Passo

### Passo 1: Criar a Factory para o Cliente
No terminal, gere um arquivo de "fábrica" para estruturar como um Cliente fictício deve ser gerado:
```bash
php artisan make:factory ClienteFactory --model=Cliente
```

### Passo 2: Definir as regras de geração de dados na Factory
Abra o arquivo `database/factories/ClienteFactory.php` e adicione o uso do Faker no método `definition()`:
```php
namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ClienteFactory extends Factory
{
    public function definition(): array
    {
        return [
            'nome' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
```

### Passo 3: Configurar o Seeder Principal
Abra o arquivo `database/seeders/DatabaseSeeder.php` e use a sua factory recém-criada para pedir a geração de 200 clientes falsos:
```php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Cliente;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Instancia a fábrica de Clientes e cria 200 registros no Postgres
        Cliente::factory()->count(200)->create();
    }
}
```

---

## 4. A Execução no Terminal (A sua PoC 3)

No terminal, fora do Tinker, rode o comando mágico de reconfiguração de banco:
```bash
php artisan migrate:fresh --seed
```

### O que esse comando faz por baixo dos panos?
1. **migrate:fresh:** Deleta todas as tabelas existentes no seu banco PostgreSQL (limpa a sujeira).
2. **migrate:** Executa novamente todas as suas Migrations do zero, recriando as tabelas limpinhas.
3. **--seed:** Dispara o `DatabaseSeeder`, preenchendo o banco recém-criado com os 200 clientes falsos gerados pelo Faker em segundos.

Você pode entrar no Tinker (`php artisan tinker`) e contar os registros para provar que funcionou:
```php
App\Models\Cliente::count(); // Retornará exatamente 200!
```
