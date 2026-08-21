# Semana 10: Consumo de APIs do Laravel com Axios

Nesta semana, conectaremos as duas pontas do seu sistema! Você aprenderá a disparar requisições HTTP assíncronas do Vue 3 para o Laravel usando a biblioteca **Axios** no ciclo de vida de montagem da tela.

---

## 1. A Analogia Ilustrada
Imagine que você vai a uma lanchonete moderna.
* **onMounted (Montagem da tela):** É o instante em que você senta na mesa e abre o cardápio.
* **Axios (O Garçom):** O garçom vem até a sua mesa, anota seu pedido e vai correndo até a cozinha (a API Laravel) buscar a comida.
* **A Resposta da API:** A cozinha entrega o prato ao garçom, que o traz até a sua mesa. Você coloca a comida no prato (salva os dados na sua variável reativa) e ela aparece na sua frente na mesa (renderização na tela).

---

## 2. A "Tradução" de Dados Fixos ➔ Requisição Dinâmica

Em vez de declarar os produtos de forma fixa (hardcoded) no seu código frontend:
```javascript
const produtos = [{ nome: 'Fixo' }];
```

Você inicia a tela com uma lista vazia, faz o disparo com o Axios e popula a lista de volta, forçando o Vue a redesenhar a tela:
```javascript
const produtos = ref([]); // Começa vazio
axios.get('http://localhost:8000/api/produtos').then(res => {
    produtos.value = res.data.data; // Altera a variável, a lista aparece na tela!
});
```

---

## 3. O Guia de Código Passo a Passo

### Passo 1: Instalar o Axios no seu projeto Vue
No terminal da pasta do seu frontend, instale o pacote:
```bash
npm install axios
```

### Passo 2: Criar o Componente de Listagem
Crie o arquivo `src/components/ListaProdutos.vue` no seu editor:

```html
<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

// Variável reativa que começará com uma lista vazia
const produtos = ref([]);
const carregando = ref(true);

// onMounted executa este bloco automaticamente assim que a página é desenhada na tela
onMounted(async () => {
  try {
    // Busca os dados da API Laravel (Certifique-se que o Laravel esteja rodando em localhost:8000)
    const response = await axios.get('http://localhost:8000/api/produtos');
    
    // Como usamos o API Resource na Semana 6, os dados reais vêm envelopados na chave 'data'
    produtos.value = response.data.data; 
  } catch (error) {
    console.error("Erro ao carregar os produtos:", error);
  } finally {
    carregando.value = false;
  }
});
</script>

<template>
  <div class="lista-container">
    <h2>🛍️ Produtos Disponíveis (Banco de Dados)</h2>

    <div v-if="carregando" class="loading">
      Carregando produtos da API...
    </div>

    <!-- v-else-if verifica se a lista está vazia -->
    <div v-else-if="produtos.length === 0" class="empty">
      Nenhum produto cadastrado no banco de dados.
    </div>

    <!-- v-for é o loop do Vue que renderiza uma linha para cada elemento da lista -->
    <ul v-else class="lista">
      <li v-for="produto in produtos" :key="produto.id" class="item">
        <span class="nome">{{ produto.titulo }}</span>
        <span class="preco">{{ produto.preco_formatado }}</span>
      </li>
    </ul>
  </div>
</template>

<style scoped>
.lista-container {
  max-width: 450px;
  margin: 30px auto;
  font-family: sans-serif;
}
.loading, .empty {
  text-align: center;
  color: #666;
  padding: 20px;
}
.lista {
  list-style: none;
  padding: 0;
}
.item {
  display: flex;
  justify-content: space-between;
  padding: 12px;
  border-bottom: 1px solid #eee;
}
.nome {
  font-weight: 500;
}
.preco {
  color: #10b981;
  font-weight: bold;
}
</style>
```

---

## 4. Testando a sua PoC 10

### IMPORTANTE: Ativar o CORS no Laravel
Como seu frontend Vue (ex: `localhost:5173`) e seu Laravel (`localhost:8000`) rodam em portas diferentes, o navegador bloqueará o acesso por segurança (erro de CORS).
Para liberar, garanta que no arquivo `.env` do seu projeto Laravel, a variável de origens permitidas esteja configurada ou que o middleware de CORS esteja ativo. No Laravel 11, o CORS já vem liberado por padrão nas configurações de API.

Abra a página do Vue no navegador:
**Resultado esperado:** Você verá a mensagem "Carregando produtos...", o Axios fará a chamada assíncrona, capturará os produtos do banco PostgreSQL do Laravel e os desenhará em forma de lista reativa na sua tela com os preços formatados!
