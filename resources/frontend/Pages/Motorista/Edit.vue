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
          <q-input class="tw-my-5" filled v-model="motorista.empresa" label="Empresa"/>
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
              <q-select
                filled
                v-model="motorista.modelo"
                label="Modelo"
                :options="modelos"
                use-input
                input-debounce="0"
                behavior="dialog"
                @filter="filtrarModelos"
              />
            </div>
            <div class="col">
              <q-select
                filled
                v-model="motorista.cor"
                label="Cor"
                :options="cores"
                use-input
                input-debounce="0"
                behavior="dialog"
                @filter="filtrarCores"
              />
            </div>
          </div>
          <q-card-actions class="justify-end" >
            <q-btn label="Voltar" color="warning" :href="route('motorista.index')" />
            <q-btn label="Salvar" color="secondary" @click="salvarMotorista" />
          </q-card-actions>
        </q-card-section>
      </q-card>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'

const props = defineProps({
  motorista: Object,
  title: String,
})

const motorista = ref({ ...props.motorista })

const coresOriginais = [
  'AMARELO',
  'AZUL',
  'BRANCO',
  'CINZA',
  'LARANJA',
  'MARROM',
  'PRETO',
  'PRATA',
  'ROSA',
  'ROXO',
  'VERDE',
  'VERMELHO',
]
const cores = ref([...coresOriginais])

function filtrarCores(val, update) {
  if (!val) {
    update(() => {
      cores.value = [...coresOriginais]
    })
    return
  }
  update(() => {
    cores.value = coresOriginais.filter(c => c.toLowerCase().includes(val.toLowerCase()))
  })
}

const modelosOriginais = [
  'VOLVO',
  'SCANIA',
  'MERCEDES',
  'VOLKSWAGEN',
  'IVECO',
  'FORD',
  'DAF',
  'MAN',
  'INTERNATIONAL',
  'RENAULT',
  'HYUNDAI'
]
const modelos = ref([...modelosOriginais])

function filtrarModelos(val, update) {
  if (!val) {
    update(() => {
      modelos.value = [...modelosOriginais]
    })
    return
  }
  update(() => {
    modelos.value = modelosOriginais.filter(m => m.toLowerCase().includes(val.toLowerCase()))
  })
}

const salvarMotorista = () => {
    router.put(route('motorista.atualizar', motorista.value.id), motorista.value)
}
</script>
