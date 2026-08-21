# Semana 12: Integração de Formulário com Tratamento de Erros

Na última semana do seu roteiro, faremos a **integração de ponta a ponta**. Você criará um formulário de cadastro de produtos no Vue 3, enviará os dados para o Laravel via POST e mapeará os erros de validação (regras da Semana 5) na tela abaixo de cada campo.

---

## 1. A Analogia Ilustrada
Imagine que você está preenchendo um papel de cadastro no balcão de um banco.
1. Você digita as informações correndo e entrega para o gerente (Axios envia os dados para a API do Laravel).
2. O gerente confere os dados usando a folha de regras (Form Request da Semana 5). Ele percebe que você deixou o preço negativo e o nome em branco.
3. Ele não joga sua folha no lixo; ele devolve o papel para você com uma caneta vermelha circulando exatamente as duas linhas onde você errou e escreve do lado: "O preço não pode ser negativo!". Você lê essas mensagens, corrige e entrega de volta.

---

## 2. O Mapeamento do Erro HTTP 422 para a Tela

Quando a validação do Laravel falha, ele responde com o status HTTP **422 Unprocessable Entity** contendo um objeto estruturado de erros. O Axios captura isso e nós jogamos esse objeto em uma variável reativa para exibir na tela:

```json
{
  "errors": {
    "nome": ["O nome do produto deve ter pelo menos 3 caracteres."],
    "preco": ["O preço não pode ser menor que zero."]
  }
}
```

No Vue, mapeamos a exibição embaixo do input de nome:
```html
<p v-if="erros.nome" class="erro-vermelho">{{ erros.nome[0] }}</p>
```

---

## 3. O Guia de Código Passo a Passo

### Passo 1: Criar o Componente de Formulário
Crie o arquivo `src/components/FormularioProduto.vue` no seu projeto Vue:

```html
<script setup>
import { ref } from 'vue';
import axios from 'axios';

// Dados do formulário (v-model fará o vínculo de mão dupla com as inputs)
const produto = ref({
  nome: '',
  preco: ''
});

// Variável para armazenar os erros retornados pelo Laravel
const erros = ref({});
const mensagemSucesso = ref('');

async function cadastrarProduto() {
  // Limpa os alertas anteriores
  erros.value = {};
  mensagemSucesso.value = '';

  try {
    // Faz o disparo POST para a API Laravel (Certifique-se que o Laravel esteja rodando!)
    const response = await axios.post('http://localhost:8000/api/produtos', produto.value);
    
    mensagemSucesso.value = `🎉 Produto "${response.data.nome}" cadastrado com sucesso!`;
    
    // Limpa o formulário
    produto.value.nome = '';
    produto.value.preco = '';
  } catch (error) {
    // Se o código do erro for 422, o Laravel recusou a validação do Form Request!
    if (error.response && error.response.status === 422) {
      erros.value = error.response.data.errors; // Salva o objeto de erros do Laravel
    } else {
      alert("Ocorreu um erro inesperado na conexão.");
    }
  }
}
</script>

<template>
  <div class="form-container">
    <h2>➕ Cadastrar Novo Produto</h2>

    <p v-if="mensagemSucesso" class="sucesso">{{ mensagemSucesso }}</p>

    <!-- @submit.prevent impede o navegador de recarregar a página ao enviar o formulário -->
    <form @submit.prevent="cadastrarProduto" class="formulario">
      <div class="campo">
        <label>Nome do Produto:</label>
        <input 
          type="text" 
          v-model="produto.nome" 
          placeholder="Ex: Teclado Mecânico"
          :class="{ 'input-erro': erros.nome }"
        />
        <!-- Se houver erro de nome vindo do Laravel, exibe em vermelho -->
        <span v-if="erros.nome" class="erro-mensagem">{{ erros.nome[0] }}</span>
      </div>

      <div class="campo">
        <label>Preço (R$):</label>
        <input 
          type="text" 
          v-model="produto.preco" 
          placeholder="Ex: 249.90"
          :class="{ 'input-erro': erros.preco }"
        />
        <!-- Se houver erro de preço vindo do Laravel, exibe em vermelho -->
        <span v-if="erros.preco" class="erro-mensagem">{{ erros.preco[0] }}</span>
      </div>

      <button type="submit" class="btn-enviar">Gravar no Banco de Dados</button>
    </form>
  </div>
</template>

<style scoped>
.form-container {
  max-width: 400px;
  margin: 40px auto;
  padding: 20px;
  border: 1px solid #ddd;
  border-radius: 8px;
  font-family: sans-serif;
}
.formulario {
  display: flex;
  flex-direction: column;
  gap: 15px;
}
.campo {
  display: flex;
  flex-direction: column;
  gap: 5px;
}
label {
  font-weight: bold;
  font-size: 14px;
}
input {
  padding: 8px;
  border: 1px solid #ccc;
  border-radius: 4px;
  font-size: 16px;
}
.input-erro {
  border-color: #ef4444;
  background-color: #fef2f2;
}
.erro-mensagem {
  color: #ef4444;
  font-size: 12px;
  font-weight: 500;
}
.sucesso {
  background-color: #ecfdf5;
  color: #047857;
  padding: 10px;
  border-radius: 4px;
  margin-bottom: 15px;
  font-weight: bold;
}
.btn-enviar {
  background-color: #10b981;
  color: white;
  border: none;
  padding: 12px;
  border-radius: 4px;
  cursor: pointer;
  font-size: 16px;
  font-weight: bold;
}
.btn-enviar:hover {
  background-color: #059669;
}
</style>
```

---

## 4. Testando a sua PoC 12 (A Entrega de Integração Completa!)

Para testar o fluxo de ponta a ponta:
1. Deixe o seu servidor Laravel rodando (`php artisan serve` na porta 8000).
2. Deixe o seu servidor Vue rodando (`npm run dev`).
3. Abra a tela no navegador.
4. Tente enviar o formulário **com os campos vazios**.

**Resultado esperado:** O Laravel interceptará os campos na Semana 5, retornará status 422 e o seu componente Vue imprimirá as mensagens em português ("O campo nome é obrigatório") em vermelho exatamente embaixo de cada input correspondente!
5. Preencha os dados corretamente e envie.
**Resultado esperado:** Você receberá a mensagem de sucesso verde e o registro será salvo fisicamente no seu banco de dados PostgreSQL!
