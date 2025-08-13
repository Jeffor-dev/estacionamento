<template layout="AppShell,Authenticated">
  <div class="tw-p-6">
    <Head :title="title" />

    <div class="q-pa-md">
      <div class="q-gutter-md row bg-grey-2 q-pa-md">
         <q-btn color="primary" label="Adicionar" :href="route('motorista.cadastro')"/>
      </div>
      <div class="q-gutter-md row bg-grey-2 q-pa-md">
        <q-card v-for="motorista in motoristas" :key="motorista.id" class="my-card">
          <q-card-section>
            <div class="text-h6">Nome: {{ motorista.nome }}</div>
            <div class="text-subtitle2">CPF: {{ motorista.cpf }}</div>
            <div class="text-subtitle2">Telefone: {{ motorista.telefone }}</div>
          </q-card-section>
        </q-card>
      </div>
    </div>
  </div>
</template>

<script setup>

  defineProps({
    title: String,
    motoristas: Array,
  })


</script>
