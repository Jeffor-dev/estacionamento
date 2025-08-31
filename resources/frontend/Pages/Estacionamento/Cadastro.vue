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
                :options="motoristasFiltered"
                option-label="label"
                option-value="id"
                behavior="dialog"
                use-input
                input-debounce="0"
                @filter="filtrarMotoristas"
                :error="!!$page.props.errors.motorista"
                :error-message="$page.props.errors.motorista"
              >
                <template v-slot:no-option>
                  <q-item>
                    <q-item-section class="text-grey">
                      Nenhum motorista encontrado
                    </q-item-section>
                  </q-item>
                </template>
              </q-select>
              <div v-if="props.motoristas.length === 0" class="tw-text-center tw-text-gray-500 tw-mt-4">
                <p>Nenhum motorista disponível para registro.</p>
                <p class="tw-text-sm">Todos os motoristas cadastrados já estão atualmente no estacionamento.</p>
              </div>
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
              <div class="tw-mb-2">
                <label class="tw-text-sm tw-font-medium tw-text-gray-700">Tipo de Veículo</label>
              </div>
              <q-option-group
                v-model="form.tipoVeiculo"
                :options="tiposVeiculo"
                color="secondary"
                inline
              />
              <div class="tw-mt-2 tw-text-sm tw-text-gray-600">
                Valor: <strong>R$ {{ form.valorPagamento.toFixed(2).replace('.', ',') }}</strong>
              </div>
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
    tipoVeiculo: 'truck_ls',
    valorPagamento: 10
  })

  // Opções de tipo de veículo com valores
  const tiposVeiculo = [
    {
      label: 'Truck/LS',
      value: 'truck_ls'
    },
    {
      label: 'Bitrem',
      value: 'bitrem'
    }
  ]

  // Watcher para atualizar o valor conforme o tipo de veículo selecionado
  watch(() => form.value.tipoVeiculo, (novoTipo) => {
    if (novoTipo === 'truck_ls') {
      form.value.valorPagamento = 10
    } else if (novoTipo === 'bitrem') {
      form.value.valorPagamento = 20
    }
  })

  // Estado para controlar a lista filtrada de motoristas
  const motoristasFiltered = ref([...props.motoristas])

  // Função para filtrar motoristas baseado na busca
  const filtrarMotoristas = (val, update) => {
    if (!val) {
      update(() => {
        motoristasFiltered.value = [...props.motoristas]
      })
      return
    }
    
    update(() => {
      const needle = val.toLowerCase()
      motoristasFiltered.value = props.motoristas.filter(motorista => 
        motorista.label.toLowerCase().includes(needle) ||
        motorista.nome.toLowerCase().includes(needle) ||
        motorista.cpf.includes(needle) ||
        motorista.placa.toLowerCase().includes(needle) ||
        motorista.modelo.toLowerCase().includes(needle) ||
        motorista.cor.toLowerCase().includes(needle)
      )
    })
  }

  const cadastrar = () => {

    if (!form.value.motorista || !form.value.pagamento || !form.value.tipoVeiculo) {
      alert('Por favor, preencha todos os campos.')
      return
    }

    if (props.motoristas.length === 0) {
      alert('Não há motoristas disponíveis para registro.')
      return
    }

    form.value.motorista = form.value.motorista.id
    router.post(route('estacionamento.cadastrar'), form.value)
  }

</script>
