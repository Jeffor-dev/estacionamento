<template layout="AppShell,Authenticated">
  <div class="tw-p-6">
    <Head :title="title" />

    <div class="q-pa-md flex justify-center" style="flex-direction: column; width: 70vw;">
      <q-card style="width: 70vw; min-height: 50vh;">
        <q-card-section class="bg-secondary text-white">
          <span class="tw-text-lg text-bold">Dados do Motorista</span>
        </q-card-section>
        <q-card-section>
          <div class="row flex tw-gap-5 tw-my-3 ">
            <div class="col-6">
              <q-input filled v-model="motorista.nome" label="Nome"/>
            </div>
            <div class="col">
              <q-input filled v-model="motorista.cpf" label="CPF" mask="###.###.###-##"/>
            </div>
          </div>
          <q-input class="tw-my-5" filled v-model="motorista.telefone" label="Telefone" mask="(##) #####-####"/>
          <q-input class="tw-my-5" filled v-model="motorista.observacao" label="Observação"/>
        </q-card-section>

        <q-card-section class="bg-secondary text-white">
          <span class="tw-text-lg text-bold">Informação do Veículo</span>
        </q-card-section>
        <q-card-section>
          <div class="row tw-gap-3 flex tw-my-5">
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
          <q-card-actions class="justify-end" >
            <q-btn label="Voltar" color="warning" :href="route('motorista.index')" />
            <q-btn label="Cadastrar" color="secondary" @click="salvarMotorista" />
          </q-card-actions>
        </q-card-section>
        

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
