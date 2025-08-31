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
            <div class="col-3">
              <q-select
                filled
                v-model="tipoDocumento"
                label="Tipo de Documento"
                :options="tiposDocumento"
                @update:model-value="limparDocumento"
              />
            </div>
            <div class="col">
              <q-input 
                filled 
                v-model="motorista.cpf" 
                :label="tipoDocumento === 'CPF' ? 'CPF' : 'CNPJ'"
                :mask="tipoDocumento === 'CPF' ? '###.###.###-##' : '##.###.###/####-##'"
                :placeholder="tipoDocumento === 'CPF' ? '000.000.000-00' : '00.000.000/0000-00'"
              />
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
            <q-btn label="Cadastrar" color="secondary" @click="salvarMotorista" />
          </q-card-actions>
        </q-card-section>
      </q-card>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'

const motorista = ref({
  nome: '',
  cpf: '',
  telefone: '',
  observacao: '',
  empresa: '',
  placa: '',
  cor: '',
  modelo: ''
})

// Controle do tipo de documento
const tipoDocumento = ref('CPF')
const tiposDocumento = ['CPF', 'CNPJ']

// Função para limpar o campo documento quando trocar o tipo
const limparDocumento = () => {
  motorista.value.cpf = ''
}

// Função para validar CPF
const validarCPF = (cpf) => {
  cpf = cpf.replace(/[^\d]/g, '')
  if (cpf.length !== 11) return false
  
  // Verifica se todos os dígitos são iguais
  if (/^(\d)\1{10}$/.test(cpf)) return false
  
  // Validação do CPF
  let soma = 0
  for (let i = 0; i < 9; i++) {
    soma += parseInt(cpf.charAt(i)) * (10 - i)
  }
  let resto = 11 - (soma % 11)
  let digito1 = resto < 2 ? 0 : resto
  
  if (parseInt(cpf.charAt(9)) !== digito1) return false
  
  soma = 0
  for (let i = 0; i < 10; i++) {
    soma += parseInt(cpf.charAt(i)) * (11 - i)
  }
  resto = 11 - (soma % 11)
  let digito2 = resto < 2 ? 0 : resto
  
  return parseInt(cpf.charAt(10)) === digito2
}

// Função para validar CNPJ
const validarCNPJ = (cnpj) => {
  cnpj = cnpj.replace(/[^\d]/g, '')
  if (cnpj.length !== 14) return false
  
  // Verifica se todos os dígitos são iguais
  if (/^(\d)\1{13}$/.test(cnpj)) return false
  
  // Validação do CNPJ
  let tamanho = cnpj.length - 2
  let numeros = cnpj.substring(0, tamanho)
  let digitos = cnpj.substring(tamanho)
  let soma = 0
  let pos = tamanho - 7
  
  for (let i = tamanho; i >= 1; i--) {
    soma += numeros.charAt(tamanho - i) * pos--
    if (pos < 2) pos = 9
  }
  
  let resultado = soma % 11 < 2 ? 0 : 11 - (soma % 11)
  if (resultado !== parseInt(digitos.charAt(0))) return false
  
  tamanho = tamanho + 1
  numeros = cnpj.substring(0, tamanho)
  soma = 0
  pos = tamanho - 7
  
  for (let i = tamanho; i >= 1; i--) {
    soma += numeros.charAt(tamanho - i) * pos--
    if (pos < 2) pos = 9
  }
  
  resultado = soma % 11 < 2 ? 0 : 11 - (soma % 11)
  return resultado === parseInt(digitos.charAt(1))
}

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
  // Validar documento conforme o tipo selecionado
  if (tipoDocumento.value === 'CPF') {
    if (!validarCPF(motorista.value.cpf)) {
      alert('CPF inválido. Verifique os dados digitados.')
      return
    }
  } else if (tipoDocumento.value === 'CNPJ') {
    if (!validarCNPJ(motorista.value.cpf)) {
      alert('CNPJ inválido. Verifique os dados digitados.')
      return
    }
  }

  // Adicionar o tipo de documento aos dados enviados
  const dadosMotorista = {
    ...motorista.value,
    tipoDocumento: tipoDocumento.value
  }

  router.post(route('motorista.cadastrar'), dadosMotorista)
}
</script>
