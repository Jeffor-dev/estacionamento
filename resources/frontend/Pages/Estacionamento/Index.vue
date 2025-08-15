<template layout="AppShell,Authenticated">
  <div class="tw-p-6">
    <Head :title="title" />

    <div class="q-pa-md">
      <div class="q-gutter-md row bg-grey-2 tw-pb-4 tw-mb-5">
           <q-btn color="secondary" label="Registrar Entrada" :href="route('motorista.cadastro')"/>
           <q-btn color="warning" label="Exportar PDF" :href="route('motorista.cadastro')"/>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  title: String
})
</script>
