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
                  class="tw-mb-4"
                  filled
                  v-model="form.pagamento"
                  label="Tipo de Pagamento"
                  :options="['Dinheiro', 'Cartão', 'Pix', 'Abastecimento']" />
                  <span v-if="form.pagamento === 'Abastecimento'" class="text-green text-bold">Estacionamento gratuito</span>
                  
                <q-input
                  class="tw-mb-4"
                  filled
                  v-model.number="form.quantidadeTickets"
                  type="number"
                  label="Quantidade de Tickets"
                  :min="1"
                  :max="50"
                  :disable="form.pagamento === 'Abastecimento'"
                  hint="Quantidade de tickets a serem comprados"
                />
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
              <div class="tw-mt-2 tw-text-sm">
                <!-- Informações de preço em tempo real -->
                <div class="tw-bg-gray-100 tw-border tw-border-gray-300 tw-px-3 tw-py-2 tw-rounded tw-mb-2">
                  <div class="tw-flex tw-justify-between tw-items-center tw-mb-1">
                    <span class="tw-font-medium">💰 Valor por ticket:</span>
                    <span class="tw-font-bold tw-text-lg">R$ {{ form.valorPagamento.toFixed(2).replace('.', ',') }}</span>
                  </div>
                  <div class="tw-flex tw-justify-between tw-items-center tw-text-xs tw-text-gray-600">
                    <span>Quantidade: {{ form.quantidadeTickets || 1 }} ticket(s)</span>
                    <span>Subtotal: R$ {{ ((form.valorPagamento || 0) * (form.quantidadeTickets || 1)).toFixed(2).replace('.', ',') }}</span>
                  </div>
                </div>

                
                <div v-if="motoristaSelecionado && infoCompra.ticketsGratuitos > 0" 
                     class="tw-bg-green-100 tw-border tw-border-green-400 tw-text-green-700 tw-px-3 tw-py-2 tw-rounded tw-mb-2">
                  🎉 <strong>{{ infoCompra.ticketsGratuitos }} TICKET(S) GRATUITO(S)!</strong> 🎉<br>
                  <span class="tw-text-xs">Parabéns! Você receberá ticket(s) gratuito(s) nesta compra!</span>
                </div>
                <div v-else-if="motoristaSelecionado && motoristaSelecionado.proxima_gratuidade <= 3 && motoristaSelecionado.proxima_gratuidade > 0"
                     class="tw-bg-yellow-100 tw-border tw-border-yellow-400 tw-text-yellow-700 tw-px-3 tw-py-2 tw-rounded tw-mb-2 tw-text-xs">
                  Faltam apenas {{ motoristaSelecionado.proxima_gratuidade }} entrada(s) para ganhar uma gratuita!
                </div>
                
                <!-- Valor Total Final -->
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

  const $q = useQuasar()

  const props = defineProps({
    title: String,
    motoristas: Array
  })

  const form = ref({
    motorista: null,
    pagamento: null,
    tipoVeiculo: 'truck_ls',
    valorPagamento: 10,
    quantidadeTickets: 1
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

  // Watcher para atualizar o valor conforme o tipo de veículo e quantidade
  watch([() => form.value.tipoVeiculo, () => form.value.quantidadeTickets], ([novoTipo, novaQuantidade]) => {
    if (novoTipo === 'truck_ls') {
      form.value.valorPagamento = 10
    } else if (novoTipo === 'bitrem') {
      form.value.valorPagamento = 20
    }
  })

  // Watcher para resetar quantidade quando for abastecimento
  watch(() => form.value.pagamento, (novoPagamento) => {
    if (novoPagamento === 'Abastecimento') {
      form.value.quantidadeTickets = 1
    }
  })

  // Watcher para resetar quantidade quando for abastecimento
  watch(() => form.value.pagamento, (novoPagamento) => {
    if (novoPagamento === 'Abastecimento') {
      form.value.quantidadeTickets = 1
    }
  })

  // Estado para controlar a lista filtrada de motoristas
  const motoristasFiltered = ref([...props.motoristas])

  // Computed para obter o motorista selecionado com suas informações
  const motoristaSelecionado = computed(() => {
    if (!form.value.motorista) return null
    return props.motoristas.find(m => m.id === form.value.motorista)
  })

  // Computed para calcular informações de compra
  const infoCompra = computed(() => {
    if (!motoristaSelecionado.value || form.value.pagamento === 'Abastecimento') {
      return {
        ticketsPagos: 0,
        ticketsGratuitos: 0,
        ticketsTotal: form.value.quantidadeTickets || 1,
        valorTotal: 0
      }
    }

    const motoristaContador = motoristaSelecionado.value.contador_entradas
    const quantidadeDesejada = form.value.quantidadeTickets || 1
    const valorUnitario = form.value.valorPagamento
    
    let ticketsPagos = 0
    let ticketsGratuitos = 0
    let contadorSimulado = motoristaContador

    // Simular a compra ticket por ticket para calcular quantos serão gratuitos
    for (let i = 0; i < quantidadeDesejada; i++) {
      if (contadorSimulado === 10) {
        ticketsGratuitos++
        contadorSimulado = 1 // Reinicia o contador após o gratuito
      } else {
        ticketsPagos++
        contadorSimulado++
      }
    }

    return {
      ticketsPagos,
      ticketsGratuitos,
      ticketsTotal: quantidadeDesejada,
      valorTotal: ticketsPagos * valorUnitario
    }
  })

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

    if (!form.value.motorista || !form.value.pagamento || !form.value.tipoVeiculo || !form.value.quantidadeTickets || form.value.quantidadeTickets < 1) {
      alert('Por favor, preencha todos os campos.')
      return
    }

    if (props.motoristas.length === 0) {
      alert('Não há motoristas disponíveis para registro.')
      return
    }

    if (form.value.quantidadeTickets > 50) {
      alert('Quantidade máxima de tickets é 50.')
      return
    }

    form.value.motorista = form.value.motorista.id
    if(form.value.pagamento.toLowerCase() === 'abastecimento') {
      form.value.valorPagamento = 0
      form.value.quantidadeTickets = 1
    } else {
      form.value.valorPagamento = infoCompra.value.valorTotal
    }
    
    router.post(route('estacionamento.cadastrar'), form.value)
    
  }

    function confirmarEntrada() {

    router.put(route('estacionamento.saida', form.value.motorista), {}, {
      onSuccess: (page) => {
        // Sucesso - fechar modal e atualizar tabela
        const id = registroSelecionado.value
        tableRef.value.requestServerInteraction()
        // Mostrar notificação de sucesso (usando Quasar Notify)
        $q.notify({
          type: 'positive',
          message: `Entrada registrada com sucesso!`,
          position: 'top'
        })
        setTimeout(() => {
          gerarCupom(id)
        }, 1000)
      },
      onError: (errors) => {
        // Erro - mostrar notificação de erro
        
        $q.notify({
          type: 'negative',
          message: 'Erro ao registrar saída. Tente novamente.',
          icon: 'report_problem',
          position: 'top'
        })
        
        console.error('Erro ao registrar saída:', errors)
      },
      onFinish: () => {
        // Sempre executado no final
        router.post(route('estacionamento.cadastrar'), form.value)
      }
    })
  }

</script>
