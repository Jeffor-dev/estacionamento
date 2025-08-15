<template layout="AppShell,Authenticated">
  <div class="tw-p-6" style="max-width: 70vw;">
    <Head :title="title" />

    <q-table title="Motorista" ref="tableRef" :rows="dados" :columns="columns" row-key="id" :filter="filter"
      v-model:pagination="pagination" :loading="loading" @request="onRequest" class="my-sticky-header-table">
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
          <q-btn color="primary" no-caps :href="route('motorista.index')">
            Adicionar
            <q-tooltip>Adiciona Motorista</q-tooltip>
          </q-btn>

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

          <q-td key="nome" :props="props">
            {{ props.row.nome }}
          </q-td>
          <q-td key="cpf" :props="props">
            {{ props.row.cpf }}
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
        </q-tr>
      </template>
    </q-table>

  </div>
</template>

<script setup>
  // import { ref, computed, onMounted, onUpdated } from 'vue'
  import axios from 'axios'
  import { debounce } from 'lodash';

  defineProps({
    title: String,
    motoristas: Array,
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

  const columns = computed(() => {
    const baseColumns = [
      { name: 'id', required: true, label: 'ID', align: 'left', field: row => row.id, format: val => `${val}`, sortable: true, style: 'width: 80px' },
      { name: 'nome', align: 'left', label: 'Nome', field: 'nome' },
      { name: 'cpf', align: 'left', label: 'CPF', field: 'cpf' },
      { name: 'caminhao-placa', align: 'left', label: 'Caminhão Placa', field: row => row.caminhao?.placa || '' },
      { name: 'caminhao-modelo', align: 'right', label: 'Caminhão Modelo', field: row => row.caminhao?.modelo || '' },
      { name: 'caminhao-cor', align: 'right', label: 'Caminhão Cor', field: row => row.caminhao?.cor || '' },
    ]

    return baseColumns;
  });

  // Remover o onUpdated para evitar requisições duplicadas

  // Substituir onRequest por uma versão com debounce
  const debouncedRequest = debounce((props) => {
    const { page, rowsPerPage, sortBy, descending } = props.pagination;
    const filter = props.filter;
    loading.value = true;

    axios.get(route('motorista.api'), {
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

</script>
