<template layout="AppShell,Authenticated">
  <div class="tw-p-6">
    <Head :title="title" />

    <div class="q-pa-md">
      <div class="q-gutter-md row">
         <q-btn color="primary" label="Offset Menu" />
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  title: String
})
</script>
