# Semana 11: Comunicação de Componentes (Props & Emits)

Nesta semana, você aprenderá a dividir telas complexas do Vue em pedaços menores e reutilizáveis (Componentes) e fará esses pedaços conversarem de forma padronizada usando **Props** e **Emits**.

---

## 1. A Analogia Ilustrada
Imagine uma conversa entre um **Pai** e um **Filho** que estão jogando futebol no quintal:
* **Props (De cima para baixo):** O Pai joga a bola para o Filho e diz: "Recebe essa bola azul!" (**azul** é o dado/parâmetro que o Pai está passando para customizar a brincadeira do Filho).
* **Emits (De baixo para cima):** O Filho pega a bola, chuta de volta e grita: "Chutei!". O Pai ouve o grito (evento) e reage batendo palmas no seu próprio código.

Componentes filhos nunca alteram dados do pai diretamente; eles apenas "gritam" (emitem um evento) avisando que uma ação aconteceu.

---

## 2. A Estrutura de Código Props vs. Emits

```html
<!-- Componente Pai (App.vue ou Tela principal) -->
<template>
  <!-- Passa o texto como Prop (:mensagem) e escuta o grito (@confirmado) -->
  <ModalConfirmacao 
    mensagem="Deseja mesmo excluir o produto?" 
    @confirmado="excluirDoBanco"
  />
</template>
```

---

## 3. O Guia de Código Passo a Passo

### Passo 1: Criar o componente Filho (O Modal de Confirmação)
Crie o arquivo `src/components/ModalConfirmacao.vue`:

```html
<script setup>
// defineProps declara quais dados este componente espera receber do pai
defineProps({
  mensagem: {
    type: String,
    required: true
  }
});

// defineEmits declara quais eventos (gritos) este componente pode disparar para o pai
const emit = defineEmits(['confirmado', 'cancelado']);
</script>

<template>
  <div class="backdrop">
    <div class="modal">
      <h3>⚠️ Atenção!</h3>
      <p>{{ mensagem }}</p>
      
      <div class="botoes">
        <!-- Dispara o evento 'confirmado' para cima -->
        <button @click="emit('confirmado')" class="btn-sim">Confirmar</button>
        <!-- Dispara o evento 'cancelado' para cima -->
        <button @click="emit('cancelado')" class="btn-nao">Cancelar</button>
      </div>
    </div>
  </div>
</template>

<style scoped>
.backdrop {
  position: fixed;
  top: 0; left: 0; width: 100%; height: 100%;
  background: rgba(0,0,0,0.5);
  display: flex; align-items: center; justify-content: center;
}
.modal {
  background: white; padding: 20px; border-radius: 8px;
  max-width: 300px; text-align: center; font-family: sans-serif;
}
.botoes {
  margin-top: 15px; display: flex; justify-content: space-around;
}
button {
  padding: 8px 15px; border-radius: 4px; border: none; cursor: pointer; font-weight: bold;
}
.btn-sim { background-color: #ef4444; color: white; }
.btn-nao { background-color: #e5e7eb; }
</style>
```

### Passo 2: Consumir e reagir no componente Pai (App.vue)
Abra seu arquivo principal `src/App.vue` e consuma o componente filho:

```html
<script setup>
import { ref } from 'vue';
import ModalConfirmacao from './components/ModalConfirmacao.vue';

const exibirModal = ref(false);
const statusAcao = ref("Aguardando ação...");

function realizarAcaoConfirmada() {
  statusAcao.value = "🔥 Ação confirmada e executada com sucesso!";
  exibirModal.value = false;
}

function fecharModalSemSalvar() {
  statusAcao.value = "❌ Ação foi cancelada pelo usuário.";
  exibirModal.value = false;
}
</script>

<template>
  <div class="app-container">
    <h2>Semana 11: Props e Emits</h2>
    <p>Status atual: <strong>{{ statusAcao }}</strong></p>

    <button @click="exibirModal = true" class="btn-principal">
      Excluir Registro
    </button>

    <!-- Exibe o modal apenas se exibirModal for true -->
    <ModalConfirmacao 
      v-if="exibirModal"
      mensagem="Você tem certeza absoluta que deseja deletar este produto?"
      @confirmado="realizarAcaoConfirmada"
      @cancelado="fecharModalSemSalvar"
    />
  </div>
</template>

<style scoped>
.app-container {
  max-width: 400px; margin: 50px auto; text-align: center; font-family: sans-serif;
}
.btn-principal {
  background-color: #ef4444; color: white; border: none;
  padding: 12px 20px; border-radius: 6px; cursor: pointer; font-weight: bold;
}
</style>
```

---

## 4. Testando a sua PoC 11

Abra o navegador com o projeto rodando.
1. Clique no botão vermelho "Excluir Registro".
2. O modal aparecerá cobrindo a tela (Backdrop cinza) e exibirá a mensagem que você customizou via **Props**.
3. Clique em "Confirmar" ou "Cancelar".

**O que vai acontecer:** O modal desaparecerá e a tela pai reagirá atualizando o texto do "Status atual". Isso prova que o componente filho emitiu o evento (**Emit**) com sucesso e o pai escutou e tomou a decisão final!
