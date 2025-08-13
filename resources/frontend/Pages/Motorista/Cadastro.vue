<template layout="AppShell,Authenticated">
  <div class="tw-p-6">
    <Head :title="title" />

    <div class="q-pa-md flex justify-center">
      <q-card style="width: 1000px;">
        <q-card-section class="bg-secondary text-white">
          <span class="tw-text-lg text-bold">Dados do Motorista</span>
        </q-card-section>
        <q-card-section>
          <div class="row flex tw-gap-3 tw-mb-3">
            <div class="col-6">
              <q-input filled v-model="motorista.nome" label="Nome"/>
            </div>
            <div class="col">
              <q-input filled v-model="motorista.cpf" label="CPF" mask="###.###.###-##"/>
            </div>
          </div>
          <q-input filled v-model="motorista.telefone" label="Telefone" mask="(##) #####-####"/>
        </q-card-section>

        <q-card-section class="bg-secondary text-white">
          <span class="tw-text-lg text-bold">Informação do Veículo</span>
        </q-card-section>
        <q-card-section>
          <div class="row tw-gap-3 flex">
            <div class="col">
                <q-input filled v-model="motorista.placa" label="Placa" mask="AAA-#A##" />
            </div>
            <div class="col">
              <q-select filled v-model="motorista.modelo" label="Modelo" :options="modelos" />
            </div>
            <div class="col">
              <q-select filled v-model="motorista.cor" label="Cor" :options="cores"/>
            </div>
          </div>
        </q-card-section>
        
        <q-card-actions class="justify-end tw-bg-gray-100">
          <q-btn label="Voltar" color="warning" :href="route('motorista.index')" />
          <q-btn label="Cadastrar" color="secondary" @click="salvarMotorista" />
        </q-card-actions>
      </q-card>

    </div>
  </div>
</template>

<script setup>
  import { router } from '@inertiajs/vue3'

  defineProps({
    title: String,
  })

  const motorista = ref({
    nome: '',
    cpf: '',
    telefone: '',
    placa: '',
    cor: '',
    modelo: ''
  })

  const cores = [
    'Amarelo',
    'Azul',
    'Branco',
    'Cinza',
    'Laranja',
    'Marrom',
    'Preto',
    'Prata',
    'Rosa',
    'Roxo',
    'Verde',
    'Vermelho',
  ]

  const modelos = [
    'Volvo',
    'Scania',
    'Mercedes',
    'Volkswagen',
    'Iveco',
    'Ford',
    'DAF',
    'MAN',
    'International',
    'Renault',
    'Hyundai'
  ]

  const salvarMotorista = () => {
    router.post(route('motorista.cadastrar'), motorista.value)
  }

</script>
