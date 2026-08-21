# Semana 9: Reatividade com Vue.js (Composition API)

Damos as boas-vindas à **Fase 3 (Frontend Reativo)**. Como o frontend é novo para você, começaremos desmistificando o conceito de **Reatividade** no Vue 3 usando a sintaxe moderna `<script setup>`.

---

## 1. A Analogia Ilustrada
Pense em uma planilha do Excel.
* Na célula `A1`, você digita o valor de um produto: `150`.
* Na célula `B1`, você tem uma fórmula simples: `=A1 * 2` (que resulta em `300`).

Se você mudar o valor de `A1` para `200`, o valor de `B1` muda automaticamente para `400` bem na sua frente, sem você precisar clicar em nenhum botão de "recalcular".
No desenvolvimento web tradicional, para mudar um texto na tela do navegador, você precisa usar comandos complexos em JavaScript para remover o texto antigo e desenhar o novo. O Vue.js funciona igual ao Excel: você altera o valor de uma variável no seu código PHP/JS e o navegador atualiza o elemento na tela instantaneamente. Isso é a **Reatividade**.

---

## 2. A "Tradução" de JavaScript Puro ➔ Vue.js Reativo

Em JavaScript comum para navegador (Vanilla JS), para alternar um spinner na tela, você faria:
```javascript
let spinner = document.getElementById('meu-spinner');
spinner.style.display = 'block'; // Mostra manualmente
// Se mudar o estado da variavel, precisa lembrar de mudar o display de novo
```

No Vue.js, você vincula a visibilidade do elemento a uma variável reativa de forma declarativa:
```html
<script setup>
import { ref } from 'vue';
const isLoading = ref(false); // Variável reativa!
</script>

<template>
  <!-- O Vue esconde ou mostra o elemento na tela dependendo do valor da variável -->
  <div v-if="isLoading">Carregando...</div>
</template>
```

---

## 3. O Guia de Código Passo a Passo

### Passo 1: Criar um componente Vue básico
Na pasta do seu projeto frontend Vue.js, abra ou crie o arquivo `src/components/PoCReatividade.vue`.

### Passo 2: Escrever o código do Componente
Cole o seguinte código no arquivo, que declara uma variável booleana reativa e a altera no clique de um botão:

```html
<script setup>
// Importamos a função 'ref' que diz ao Vue para observar alterações nessa variável
import { ref } from 'vue';

// Criamos uma variável reativa de valor inicial false
const carregando = ref(false);

// Função para inverter o valor atual de true para false e vice-versa
function alternarCarregamento() {
  carregando.value = !carregando.value; // No script, sempre acessamos com .value
}
</script>

<template>
  <div class="card">
    <h2>Semana 9: Teste de Reatividade do Vue 3</h2>
    
    <div class="status-container">
      <!-- v-if faz o bloco aparecer se carregando for true, v-else se for false -->
      <div v-if="carregando" class="spinner">
        <span class="loader"></span> Aguarde, buscando dados...
      </div>
      <div v-else class="pronto">
        ✅ Sistema ocioso e pronto para agir.
      </div>
    </div>

    <!-- @click é o ouvinte de eventos do Vue que chama nossa função no clique -->
    <button @click="alternarCarregamento" class="btn">
      {{ carregando ? 'Parar Carregamento' : 'Iniciar Carregamento' }}
    </button>
  </div>
</template>

<style scoped>
/* Um CSS básico para ilustrar o teste */
.card {
  padding: 20px;
  border: 1px solid #ccc;
  border-radius: 8px;
  max-width: 350px;
  margin: 20px auto;
  text-align: center;
  font-family: sans-serif;
}
.status-container {
  height: 60px;
  margin: 20px 0;
  display: flex;
  align-items: center;
  justify-content: center;
}
.btn {
  background-color: #4f46e5;
  color: white;
  border: none;
  padding: 10px 15px;
  border-radius: 4px;
  cursor: pointer;
  font-weight: bold;
}
.btn:hover {
  background-color: #4338ca;
}
.loader {
  border: 3px solid #f3f3f3;
  border-top: 3px solid #4f46e5;
  border-radius: 50%;
  width: 16px;
  height: 16px;
  animation: spin 1s linear infinite;
  display: inline-block;
  margin-right: 8px;
}
@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}
</style>
```

---

## 4. Testando a sua PoC 9

Para visualizar e validar:
1. Monte esse componente no seu arquivo principal `App.vue`.
2. Rode o servidor de desenvolvimento do Vue no terminal: `npm run dev`.
3. Abra a URL informada no seu navegador.
4. Clique no botão "Iniciar Carregamento".

**O que deve acontecer:** O texto de sucesso desaparece imediatamente e o spinner animado em CSS começa a girar. Ao clicar novamente, ele desliga instantaneamente. Você controlou o comportamento da tela alterando apenas uma variável lógica em JavaScript!
