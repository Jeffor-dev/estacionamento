<template layout="AppShell,Authenticated">
  <div class="tw-p-6">
    <Head :title="title" />

    <div class="q-pa-md">
      <div class="q-gutter-md row bg-grey-2 tw-pb-4 tw-mb-5">
           <q-btn color="secondary" label="Registrar Entrada" :href="route('estacionamento.cadastro')"/>
           <q-btn color="warning" label="Exportar PDF" :href="route('motorista.cadastro')"/>
      </div>
      <div class="row items-center tw-mb-4">
        <q-radio v-model="statusFiltro" val="ativo" label="Ativos" color="green" />
        <q-radio v-model="statusFiltro" val="finalizado" label="Finalizados" color="blue" class="tw-ml-4" />
      </div>
    </div>
        <q-table 
        title="Controle de Estacionamento" 
        ref="tableRef" 
  :rows="dadosFiltrados" 
        :columns="columns" 
        row-key="id" 
        :filter="filter"
        v-model:pagination="pagination" 
        :loading="loading" 
        @request="onRequest" 
        class="my-sticky-header-table"
        no-data-label="Nenhum registro encontrado"
        no-results-label="Nenhum resultado encontrado para sua busca"
        loading-label="Carregando..."
        rows-per-page-label="Registros por página:"
        >
        <template v-slot:top-left>

          <q-input outlined bottom-slots label="Buscar" v-model="filter.search" debounce="500" counter maxlength="15"
            dense="">
            <template v-slot:append>
              <i-mdi-search />
            </template>
          </q-input>

        </template>
        <template v-slot:top-right>
          <div class="q-gutter-sm">
            <q-btn color="orange" >
              <i-mdi-menu />
              <q-menu>
                <q-list style="min-width: 100px">
                  <q-item clickable v-close-popup>
                    <q-item-section>Exportar PDF</q-item-section>
                  </q-item>
                </q-list>
              </q-menu>
            </q-btn>
          </div>
        </template>

        <template v-slot:body="props">
          <q-tr :props="props"
            class="cursor-pointer"
            @click="visualizar(props.row.gid)">

            <q-td key="id" :props="props">
              {{ props.row.id }}
            </q-td>
            <q-td key="motorista" :props="props">
              {{ props.row.motorista }}
            </q-td>
            <q-td key="entrada" :props="props">
              {{ new Date(props.row.entrada).toLocaleTimeString('pt-BR') }}
            </q-td>
            <q-td key="caminhao-placa" :props="props">
              {{ props.row.caminhao?.placa || 'N/A' }}
            </q-td>
            <q-td key="caminhao-modelo" :props="props">
              {{ props.row.caminhao?.modelo || 'N/A' }}
            </q-td>
            <q-td key="caminhao-cor" :props="props">
              {{ props.row.caminhao?.cor || 'N/A' }}
            </q-td>
            <q-td key="saida" :props="props">
              {{ props.row.saida ? new Date(props.row.saida).toLocaleString('pt-BR') : 'Em aberto' }}
            </q-td>
            <q-td key="valor" :props="props">
              {{ props.row.valor ? `R$ ${props.row.valor}` : '-' }}
            </q-td>
            <q-td key="status" :props="props">
              <q-badge :color="props.row.status === 'ativo' ? 'green' : 'blue'" :label="props.row.status === 'ativo' ? 'Ativo' : 'Finalizado'" />
            </q-td>
            <q-td key="actions" :props="props">
              <div class="q-gutter-xs">
                <q-btn v-if="props.row.status === 'ativo'" color="negative" size="sm" @click.stop="abrirModalConfirmacao(props.row.id, props.row.motorista)">
                  <i-mdi-truck />
                  <q-tooltip>Registrar Saída</q-tooltip>
                </q-btn>
                <q-btn v-if="props.row.status === 'finalizado'" color="primary" size="sm" @click.stop="gerarCupom(props.row.id)">
                  <i-mdi-printer />
                  <q-tooltip>Gerar Cupom</q-tooltip>
                </q-btn>
              </div>
            </q-td>
          </q-tr>
        </template>
    </q-table>

    <!-- Modal de Confirmação -->
    <q-dialog v-model="modalConfirmacao" persistent>
      <q-card>
        <q-card-section class="row items-center">
          <i-mdi-warning class="q-mr-sm" />
          <span class="q-ml-sm">Confirmar Saída</span>
        </q-card-section>

        <q-card-section class="q-pt-none">
          Tem certeza que deseja registrar a saída do motorista <strong>{{ motoristaSelecionado }}</strong>?
        </q-card-section>

        <q-card-actions align="right">
          <q-btn flat label="Cancelar" color="primary" @click="fecharModal" />
          <q-btn flat label="Confirmar" color="negative" @click="confirmarSaida" :loading="processandoSaida" />
        </q-card-actions>
      </q-card>
    </q-dialog>
  </div>
</template>

<script setup>
  import axios from 'axios'
  import { debounce } from 'lodash';
  import { useQuasar } from 'quasar'

  const $q = useQuasar()

  defineProps({
    title: String
  })

  const csrfToken = computed(() => {
    return usePage().props.csrf_token
  })

    const filter = ref({
    search: '',
  })
  const tableRef = ref()
  const loading = ref(false)
  const pagination = ref({
    sortBy: null,
    descending: false,
    page: 1,
    rowsPerPage: 10,
    rowsNumber: 0
  })
  const dados = ref([])
  const statusFiltro = ref('ativo')
  const dadosFiltrados = computed(() => {
    if (statusFiltro.value === 'finalizado') {
      return dados.value
        .filter(row => row.status === 'finalizado')
        .sort((a, b) => new Date(b.saida) - new Date(a.saida))
    }
    return dados.value.filter(row => row.status === statusFiltro.value)
  })

  // Modal de confirmação
  const modalConfirmacao = ref(false)
  const processandoSaida = ref(false)
  const registroSelecionado = ref(null)
  const motoristaSelecionado = ref('')

  const columns = computed(() => {
    const baseColumns = [
      { name: 'id', required: true, label: 'ID', align: 'left', field: row => row.id, format: val => `${val}`, sortable: true, style: 'width: 80px' },
      { name: 'motorista', align: 'left', label: 'Motorista', field: 'motorista' },
      { name: 'entrada', align: 'left', label: 'Entrada', field: 'entrada' },
      { name: 'caminhao-placa', align: 'left', label: 'Placa', field: row => row.caminhao?.placa || '' },
      { name: 'caminhao-modelo', align: 'center', label: 'Modelo', field: row => row.caminhao?.modelo || '' },
      { name: 'caminhao-cor', align: 'center', label: 'Cor', field: row => row.caminhao?.cor || '' },
      { name: 'saida', align: 'center', label: 'Saída', field: 'saida' },
      { name: 'valor', align: 'center', label: 'Valor', field: 'valor' },
      { name: 'status', align: 'center', label: 'Status', field: 'status' },
      { name: 'actions', align: 'center', label: 'Ações', field: 'actions', sortable: false, style: 'width: 120px' }
    ]

    return baseColumns;
  });

  const debouncedRequest = debounce((props) => {
    const { page, rowsPerPage, sortBy, descending } = props.pagination;
    const filter = props.filter;
    loading.value = true;

    axios.get(route('estacionamento.api'), {
      params: {
        busca: filter.search,
        page: page,
        rowsPerPage: rowsPerPage,
        sortBy: sortBy,
        descending: descending
      }
    }).then(response => {
      pagination.value.rowsNumber = response.data.rowsNumber;
      dados.value.splice(0, dados.value.length, ...response.data.lista);

      pagination.value.page = page;
      pagination.value.rowsPerPage = rowsPerPage;
      pagination.value.sortBy = sortBy;
      pagination.value.descending = descending;
      loading.value = false;

    }).finally(() => {
      loading.value = false;
    });
  }, 700); // 700ms debounce

  function onRequest(props) {
    debouncedRequest(props);
  }

  onMounted(() => {
    tableRef.value.requestServerInteraction()
  })

  function visualizar(id) {
    router.get(route('motorista.visualizar', id));
  }

  function abrirModalConfirmacao(id, motorista) {
    registroSelecionado.value = id
    motoristaSelecionado.value = motorista
    modalConfirmacao.value = true
  }

  function fecharModal() {
    modalConfirmacao.value = false
    registroSelecionado.value = null
    motoristaSelecionado.value = ''
    processandoSaida.value = false
  }

  function confirmarSaida() {
    processandoSaida.value = true
    
    router.put(route('estacionamento.saida', registroSelecionado.value), {}, {
      onSuccess: (page) => {
        // Sucesso - fechar modal e atualizar tabela
        const id = registroSelecionado.value
        fecharModal()
        tableRef.value.requestServerInteraction()
        // Mostrar notificação de sucesso (usando Quasar Notify)
        $q.notify({
          type: 'positive',
          message: `Saída registrada com sucesso!`,
          position: 'top'
        })
        setTimeout(() => {
          gerarCupom(id)
        }, 2000)
      },
      onError: (errors) => {
        // Erro - mostrar notificação de erro
        processandoSaida.value = false
        
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
        processandoSaida.value = false
      }
    })
  }

  function gerarCupom(id) {
    // Abrir cupom em nova aba
    window.open(route('cupom.gerar', id), '_blank')
  }
</script>
