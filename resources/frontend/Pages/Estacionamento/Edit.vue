<template layout="AppShell,Authenticated">
  <div class="tw-p-6">
    <Head :title="title" />

    <div class="q-pa-md">
      <div class="q-gutter-md row bg-grey-2 tw-pb-4 tw-mb-5">
        <q-btn 
          color="secondary" 
          label="Voltar" 
          :href="route('estacionamento.index')"
        />
        <q-btn 
          color="positive" 
          label="Salvar Alterações" 
          @click="salvar"
          :loading="salvando"
          :disable="!formularioValido"
        />
      </div>

      <q-card>
        <q-card-section>
          <div class="text-h6 tw-mb-4">Editar Registro de Estacionamento</div>
          
          <!-- Informações do Motorista (Somente Leitura) -->
          <q-card flat bordered class="q-mb-md">
            <q-card-section>
              <div class="text-subtitle1 q-mb-md">Informações do Motorista</div>
              <div class="row q-gutter-md">
                <div class="col-md-3 col-sm-6 col-xs-12">
                  <q-input
                    filled
                    :model-value="estacionamento?.motorista?.nome"
                    label="Nome do Motorista"
                    readonly
                  />
                </div>
                <div class="col-md-3 col-sm-6 col-xs-12">
                  <q-input
                    filled
                    :model-value="estacionamento?.motorista?.cpf"
                    label="CPF"
                    readonly
                  />
                </div>
                <div class="col-md-3 col-sm-6 col-xs-12">
                  <q-input
                    filled
                    :model-value="estacionamento?.motorista?.telefone"
                    label="Telefone"
                    readonly
                  />
                </div>
                <div class="col-md-3 col-sm-6 col-xs-12">
                  <q-input
                    filled
                    :model-value="estacionamento?.motorista?.empresa"
                    label="Empresa"
                    readonly
                  />
                </div>
              </div>
            </q-card-section>
          </q-card>

          <!-- Informações do Caminhão (Somente Leitura) -->
          <q-card flat bordered class="q-mb-md">
            <q-card-section>
              <div class="text-subtitle1 q-mb-md">Informações do Caminhão</div>
              <div class="row q-gutter-md">
                <div class="col-md-3 col-sm-6 col-xs-12">
                  <q-input
                    filled
                    :model-value="estacionamento?.motorista?.caminhao?.placa"
                    label="Placa"
                    readonly
                  />
                </div>
                <div class="col-md-3 col-sm-6 col-xs-12">
                  <q-input
                    filled
                    :model-value="estacionamento?.motorista?.caminhao?.modelo"
                    label="Modelo"
                    readonly
                  />
                </div>
                <div class="col-md-3 col-sm-6 col-xs-12">
                  <q-input
                    filled
                    :model-value="estacionamento?.motorista?.caminhao?.cor"
                    label="Cor"
                    readonly
                  />
                </div>
              </div>
            </q-card-section>
          </q-card>

          <!-- Dados Editáveis do Estacionamento -->
          <q-card flat bordered>
            <q-card-section>
              <div class="text-subtitle1 q-mb-md">Dados do Estacionamento (Editáveis)</div>
              <div class="row q-gutter-md">
                <div class="col-md-4 col-sm-6 col-xs-12">
                  <q-select
                    filled
                    v-model="form.tipo_pagamento"
                    :options="['Dinheiro', 'Cartão', 'Pix', 'Abastecimento']"
                    label="Tipo de Pagamento"
                    :rules="[val => !!val || 'Campo obrigatório']"
                    @update:model-value="onTipoPagamentoChange"
                  />
                </div>
                <div class="col-md-4 col-sm-6 col-xs-12">
                  <q-input
                    filled
                    v-model.number="form.valor_pagamento"
                    label="Valor do Pagamento"
                    type="number"
                    step="0.01"
                    min="0"
                    prefix="R$"
                    :disable="form.tipo_pagamento === 'Abastecimento'"
                    :rules="[
                      val => form.tipo_pagamento === 'Abastecimento' || val > 0 || 'Valor deve ser maior que zero'
                    ]"
                  />
                </div>
                <div class="col-md-4 col-sm-12 col-xs-12">
                  <q-select
                    filled
                    v-model="form.tipo_veiculo"
                    :options="[
                      { label: 'Truck LS', value: 'truck_ls' },
                      { label: 'Bitrem', value: 'bitrem' }
                    ]"
                    option-label="label"
                    option-value="value"
                    emit-value
                    map-options
                    label="Tipo de Veículo"
                    :rules="[val => !!val || 'Campo obrigatório']"
                  />
                </div>
              </div>

              <!-- Aviso para Abastecimento -->
              <div v-if="form.tipo_pagamento === 'Abastecimento'" class="q-mt-md">
                <q-banner class="bg-orange-1 text-orange-8">
                  <template v-slot:avatar>
                    <q-icon name="info" color="orange" />
                  </template>
                  Estacionamento gratuito por abastecimento. O valor será automaticamente definido como R$ 0,00.
                </q-banner>
              </div>
            </q-card-section>
          </q-card>
        </q-card-section>
      </q-card>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useForm, router } from '@inertiajs/vue3'
import { useQuasar } from 'quasar'

const props = defineProps({
  title: String,
  estacionamento: Object
})

const $q = useQuasar()
const salvando = ref(false)

// Formulário para edição
const form = useForm({
  tipo_pagamento: '',
  valor_pagamento: 0,
  tipo_veiculo: ''
})

// Validação do formulário
const formularioValido = computed(() => {
  return form.tipo_pagamento &&
         form.tipo_veiculo &&
         (form.tipo_pagamento === 'Abastecimento' || form.valor_pagamento > 0)
})

// Função chamada quando o tipo de pagamento muda
function onTipoPagamentoChange(novoTipo) {
  if (novoTipo === 'Abastecimento') {
    form.valor_pagamento = 0
  }
}

// Função para salvar as alterações
function salvar() {
  if (!formularioValido.value) {
    $q.notify({
      color: 'negative',
      message: 'Por favor, preencha todos os campos obrigatórios',
      icon: 'error'
    })
    return
  }

  salvando.value = true

  form.put(route('estacionamento.update', props.estacionamento.id), {
    onSuccess: () => {
      $q.notify({
        color: 'positive',
        message: 'Registro atualizado com sucesso!',
        icon: 'check'
      })
      router.visit(route('estacionamento.index'))
    },
    onError: (errors) => {
      console.error('Erros de validação:', errors)
      $q.notify({
        color: 'negative',
        message: 'Erro ao salvar. Verifique os dados informados.',
        icon: 'error'
      })
    },
    onFinish: () => {
      salvando.value = false
    }
  })
}

// Carregar dados do estacionamento no formulário
onMounted(() => {
  if (props.estacionamento) {
    form.tipo_pagamento = props.estacionamento.tipo_pagamento || ''
    form.valor_pagamento = props.estacionamento.valor_pagamento || 0
    form.tipo_veiculo = props.estacionamento.tipo_veiculo || ''
  }
})
</script>

<style scoped>
.q-card {
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}
</style>