<template layout="AppShell,Authenticated">
  <div class="tw-p-6">
    <Head :title="title" />

    <div class="q-pa-md flex justify-center" style="flex-direction: column; width: 70vw;">
      <q-card style="width: 70vw; min-height: 40vh;">
        <q-card-section class="bg-secondary text-white">
          <span class="tw-text-lg text-bold">Selecionar Motorista</span>
        </q-card-section>
        <q-card-section>
            <div class="col tw-gap-5 tw-my-3 ">
              <q-select
                filled
                v-model="form.motorista"
                label="Motorista"
                :options="props.motoristas"
                option-label="label"
                option-value="id"
                behavior="dialog"
              />
            </div>
        </q-card-section>

        <q-card-section class="bg-secondary text-white">
          <span class="tw-text-lg text-bold">Informação de Pagamento</span>
        </q-card-section>
        <q-card-section>
          <div class="row tw-gap-3 flex tw-my-5">
            <div class="col">
                <q-select
                  filled
                  v-model="form.pagamento"
                  label="Tipo de Pagamento"
                  :options="['Dinheiro', 'Cartão', 'Pix']" />
            </div>
            <div class="col">
                <q-input filled v-model="form.valorPagamento" label="Valor" mask="##########"/>
            </div>

          </div>

        </q-card-section>
        <q-card-actions class="justify-end" >
          <q-btn label="Voltar" color="warning" :href="route('estacionamento.index')" />
          <q-btn label="Registrar" color="secondary" @click="cadastrar" />
        </q-card-actions>
      </q-card>

    </div>
  </div>
</template>

<script setup>
  import { router } from '@inertiajs/vue3'

  const props = defineProps({
    title: String,
    motoristas: Array
  })

  const form = ref({
    motorista: null,
    pagamento: null,
    valorPagamento: 20
  })

  const cadastrar = () => {

    if (!form.value.motorista || !form.value.pagamento || !form.value.valorPagamento) {
      alert('Por favor, preencha todos os campos.')
      return
    }

    form.value.motorista = form.value.motorista.id
    router.post(route('estacionamento.cadastrar'), form.value)
  }

</script>
