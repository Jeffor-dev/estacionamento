<template layout="AppShell,Authenticated">
  <div class="tw-p-6">
    <Head :title="title" />

    <h1 class="text-h4 tw-mb-6">
      {{ title }}
    </h1>

    <div class="q-pa-md">
      <q-card>
        <q-card-section>
          <div class="text-h6 tw-mb-4">Relatórios PDF</div>
          
          <!-- Filtros de Data e Turno -->
          <div class="row q-gutter-md tw-mb-4">
            <q-input
              filled
              v-model="dataSelecionada"
              mask="##/##/####"
              label="Data para Relatório"
              hint="Selecione a data para filtrar as movimentações"
              style="max-width: 300px"
            >
              <template v-slot:append>
                <q-btn
                  flat
                  round
                  dense
                  class="cursor-pointer"
                >
                  <i-mdi-calendar style="height: 32px; width: 32px;"/>
                  <q-popup-proxy cover transition-show="scale" transition-hide="scale">
                  <q-date v-model="dataSelecionada" mask="DD/MM/YYYY">
                    <div class="row items-center justify-end">
                    <q-btn v-close-popup label="Fechar" color="primary" flat />
                    </div>
                  </q-date>
                  </q-popup-proxy>
                </q-btn>
              </template>
            </q-input>
            
            <q-select
              filled
              v-model="turnoSelecionado"
              :options="opcoeTurnos"
              label="Turno"
              hint="Selecione o turno para filtrar por horário"
              style="max-width: 250px"
              option-value="value"
              option-label="label"
              map-options
              emit-value
            />
          </div>

          <q-list bordered>
            <q-item clickable @click="exportarMotoristasPDF">
              <q-item-section>Lista de motoristas cadastrados</q-item-section>
              <q-item-section side><q-btn color="primary" label="Exportar PDF" /></q-item-section>
            </q-item>
            <q-item>
              <q-item-section>{{ tituloMovimentacoes }}</q-item-section>
              <q-item-section side>
                <div class="q-gutter-sm">
                  <q-btn 
                    color="secondary" 
                    label="Visualizar" 
                    @click="visualizarMovimentacoes"
                  />
                  <q-btn 
                    color="primary" 
                    label="Exportar PDF" 
                    @click="exportarMovimentacoesPDF"
                  />
                </div>
              </q-item-section>
            </q-item>
          </q-list>
        </q-card-section>
      </q-card>
    </div>

    <!-- Modal de Visualização das Movimentações -->
    <q-dialog v-model="modalVisualizacao" maximized>
      <q-card>
        <q-card-section class="row items-center q-pb-none">
          <div class="text-h6">{{ tituloModalVisualizacao }}</div>
          <q-space />
          <q-btn flat dense v-close-popup color="white" label="Fechar" class="bg-amber" />
        </q-card-section>

        <q-card-section>
          <!-- Resumo dos Totais -->
          <div class="q-mb-md">
            <q-card flat bordered class="q-pa-md">
              <div class="text-subtitle1 q-mb-md">Resumo do Turno</div>
              <div class="row q-gutter-md">
                <div class="col">
                  <div class="text-caption text-grey-7">Total Geral</div>
                  <div class="text-h6 text-positive">{{ dadosVisualizacao.valor_total_formatado }}</div>
                </div>
                <div class="col">
                  <div class="text-caption text-grey-7">Total em Dinheiro</div>
                  <div class="text-body1">{{ dadosVisualizacao.totais_por_categoria?.dinheiro?.formatado }}</div>
                </div>
                <div class="col">
                  <div class="text-caption text-grey-7">Total no Cartão</div>
                  <div class="text-body1">{{ dadosVisualizacao.totais_por_categoria?.cartao?.formatado }}</div>
                </div>
                <div class="col">
                  <div class="text-caption text-grey-7">Total via Pix</div>
                  <div class="text-body1">{{ dadosVisualizacao.totais_por_categoria?.pix?.formatado }}</div>
                </div>
                <div class="col">
                  <div class="text-caption text-grey-7">Gratuito por Abastecimento</div>
                  <div class="text-body1">{{ dadosVisualizacao.totais_por_categoria?.abastecimento?.formatado }}</div>
                </div>
              </div>
            </q-card>
          </div>

          <!-- Tabela de Movimentações -->
          <q-table
            :rows="dadosVisualizacao.movimentacoes || []"
            :columns="colunasTabela"
            row-key="id"
            :pagination="{ rowsPerPage: 0 }"
            :loading="carregandoVisualizacao"
            flat
            bordered
          >
            <template v-slot:body-cell-valor="props">
              <q-td :props="props">
                <span :class="props.row.valor === '-' ? 'text-grey' : 'text-positive'">
                  {{ props.row.valor }}
                </span>
              </q-td>
            </template>
            
            <template v-slot:body-cell-tipo_pagamento="props">
              <q-td :props="props">
                <q-chip 
                  :color="getCorTipoPagamento(props.row.tipo_pagamento)"
                  text-color="white"
                  size="sm"
                >
                  {{ props.row.tipo_pagamento }}
                </q-chip>
              </q-td>
            </template>
          </q-table>
        </q-card-section>
      </q-card>
    </q-dialog>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import axios from 'axios'
import { jsPDF } from 'jspdf'
import autoTable from 'jspdf-autotable'

const props = defineProps({
  title: String
})

// Data selecionada para filtro (inicializa com hoje)
const dataSelecionada = ref(new Date().toLocaleDateString('pt-BR'))

// Turno selecionado (padrão é turno 1)
const turnoSelecionado = ref(1)

// Opções de turnos
const opcoeTurnos = [
  { value: 1, label: 'Turno 1 (12:00 - 22:15)' },
  { value: 2, label: 'Turno 2 (22:15 - 05:30)' },
  { value: 3, label: 'Turno 3 (05:30 - 11:59)' }
]

// Variáveis do modal de visualização
const modalVisualizacao = ref(false)
const carregandoVisualizacao = ref(false)
const dadosVisualizacao = ref({})

// Colunas da tabela de visualização
const colunasTabela = [
  { name: 'motorista', label: 'Motorista', field: 'motorista', align: 'left', sortable: true },
  { name: 'modelo', label: 'Modelo', field: 'modelo', align: 'left', sortable: true },
  { name: 'placa', label: 'Placa', field: 'placa', align: 'center', sortable: true },
  { name: 'entrada', label: 'Entrada', field: 'entrada', align: 'center', sortable: true },
  { name: 'valor', label: 'Valor', field: 'valor', align: 'center', sortable: true },
  { name: 'tipo_pagamento', label: 'Pagamento', field: 'tipo_pagamento', align: 'center', sortable: true }
]

// Computed para o título das movimentações baseado na data e turno selecionados
const tituloMovimentacoes = computed(() => {
  const hoje = new Date().toLocaleDateString('pt-BR')
  const turnoTexto = opcoeTurnos.find(t => t.value === turnoSelecionado.value)?.label || 'Turno 1'
  
  if (dataSelecionada.value === hoje) {
    return `Lista de movimentações do dia (hoje) - ${turnoTexto}`
  } else {
    return `Lista de movimentações do dia ${dataSelecionada.value} - ${turnoTexto}`
  }
})

// Computed para o título do modal de visualização
const tituloModalVisualizacao = computed(() => {
  const turnoTexto = opcoeTurnos.find(t => t.value === turnoSelecionado.value)?.label || 'Turno 1'
  return `Movimentações - ${dataSelecionada.value} - ${turnoTexto}`
})

// Função para converter data de DD/MM/YYYY para YYYY-MM-DD
const formatarDataParaAPI = (data) => {
  const [dia, mes, ano] = data.split('/')
  return `${ano}-${mes}-${dia}`
}

// Função para definir cores dos chips de tipo de pagamento
const getCorTipoPagamento = (tipo) => {
  switch (tipo) {
    case 'Dinheiro': return 'green'
    case 'Cartão': return 'blue'
    case 'Pix': return 'purple'
    case 'Abastecimento': return 'orange'
    case 'GRATUITO': return 'grey'
    default: return 'grey'
  }
}

// Função para visualizar movimentações
async function visualizarMovimentacoes() {
  carregandoVisualizacao.value = true
  
  try {
    const dataFormatada = formatarDataParaAPI(dataSelecionada.value)
    
    const { data } = await axios.get('/api/relatorio/movimentacoes', {
      params: {
        data: dataFormatada,
        turno: turnoSelecionado.value
      }
    })
    
    dadosVisualizacao.value = data
    modalVisualizacao.value = true
  } catch (error) {
    console.error('Erro ao buscar movimentações:', error)
    // Aqui você pode adicionar uma notificação de erro se quiser
  } finally {
    carregandoVisualizacao.value = false
  }
}

async function exportarMotoristasPDF() {
  const { data } = await axios.get('/api/relatorio/motoristas')
  const doc = new jsPDF()
  
  doc.text('Lista de Motoristas Cadastrados', 14, 16)

  doc.setFont('helvetica', 'normal')
  doc.setFontSize(10)
  
  // Use autoTable diretamente
  autoTable(doc, {
    head: [['#', 'Nome', 'CPF', 'Telefone', 'Empresa', 'Placa', 'Modelo', 'Cor']],
    body: data.motoristas.map((m, index) => [
      index + 1, m.nome, m.cpf, m.telefone, m.empresa, m.placa, m.modelo, m.cor
    ]),
    startY: 22,
    styles: {
      font: 'helvetica',        // Fonte para toda a tabela
      fontStyle: 'normal',      // normal, bold, italic, bolditalic
      fontSize: 9,              // Tamanho da fonte
      cellPadding: 3,
      textColor: [0, 0, 0],     // Cor do texto [R, G, B]
      lineColor: [200, 200, 200], // Cor das linhas
      lineWidth: 0.1
    },
    headStyles: {
      font: 'helvetica',
      fontStyle: 'bold',        // Cabeçalho em negrito
      fontSize: 10,             // Fonte maior no cabeçalho
      fillColor: [52, 152, 219], // Cor de fundo
      textColor: [255, 255, 255], // Texto branco
      halign: 'center'          // Centralizar texto
    },
    bodyStyles: {
      font: 'helvetica',
      fontStyle: 'normal',
      fontSize: 8,
      textColor: [50, 50, 50]
    },
    
    // Estilos para linhas alternadas
    alternateRowStyles: {
      fillColor: [245, 245, 245],
      font: 'helvetica'
    },
  })


  
  doc.save('motoristas_cadastrados.pdf')
}

async function exportarMovimentacoesPDF() {
  // Converter data selecionada para formato da API
  const dataFormatada = formatarDataParaAPI(dataSelecionada.value)
  
  const { data } = await axios.get('/api/relatorio/movimentacoes', {
    params: {
      data: dataFormatada,
      turno: turnoSelecionado.value
    }
  })
  const doc = new jsPDF()
  doc.setFontSize(12)
  
  // Incluir turno no título do PDF
  const turnoTexto = opcoeTurnos.find(t => t.value === turnoSelecionado.value)?.label || 'Turno 1'
  doc.text(`Movimentações do dia ${dataSelecionada.value} - ${turnoTexto}`, 12, 14)
  doc.setFont('helvetica', 'normal')
  doc.setFontSize(10)
  
  // Usar o valor total calculado pelo controller
  const valorTotal = data.valor_total_formatado
  
  // Passar o doc como primeiro parâmetro
  autoTable(doc, {
    head: [['#', 'Motorista', 'Modelo', 'Placa', 'Entrada', 'Valor', 'Pagamento']],
    body: data.movimentacoes.map((m, index) => [
      index + 1, m.motorista, m.modelo, m.placa, m.entrada, m.valor, m.tipo_pagamento
    ]),
    startY: 22,
    pageBreak: 'auto',
    rowPageBreak: 'avoid',
    tableLineColor: [200, 200, 200],
    tableLineWidth: 0.1,
    // Configurar altura máxima para forçar quebra após 30 linhas
    pageBreakBefore: function(cursor, doc) {
      // Se já temos 30 linhas na página atual, quebra
      return cursor.y > 750; // Altura que comporta aproximadamente 30 linhas
    },
      styles: {
      font: 'helvetica',        // Fonte para toda a tabela
      fontStyle: 'normal',      // normal, bold, italic, bolditalic
      fontSize: 8,              // Fonte um pouco maior
      cellPadding: 2.44,         // Padding ligeiramente maior
      textColor: [0, 0, 0],     // Cor do texto [R, G, B]
      lineColor: [200, 200, 200], // Cor das linhas
      lineWidth: 0.1
    },
    headStyles: {
      font: 'helvetica',
      fontStyle: 'bold',        // Cabeçalho em negrito
      fontSize: 10,             // Fonte do cabeçalho um pouco maior
      fillColor: [52, 152, 219], // Cor de fundo
      textColor: [255, 255, 255], // Texto branco
      halign: 'center',         // Centralizar texto
      cellPadding: 2.5          // Padding do cabeçalho
    },
    bodyStyles: {
      font: 'helvetica',
      fontStyle: 'normal',
      fontSize: 8,
      textColor: [50, 50, 50]
    },
    
    // Estilos para linhas alternadas
    alternateRowStyles: {
      fillColor: [245, 245, 245],
      font: 'helvetica'
    },
  })
  
  // Adicionar linha de total como uma segunda tabela para manter o estilo
  autoTable(doc, {
    body: [['', '', '', '', 'TOTAL:', valorTotal, '']],
    startY: doc.lastAutoTable.finalY,
    styles: {
      font: 'helvetica',
      fontStyle: 'bold',
      fontSize: 8,             // Fonte proporcional
      cellPadding: 2.2,         // Padding balanceado
      textColor: [0, 0, 0], // Preto por padrão
      lineColor: [200, 200, 200],
      lineWidth: 0.1,
      fillColor: [240, 240, 240], // Fundo cinza claro para destacar
      halign: 'center'
    },
    columnStyles: {
      4: { 
        halign: 'right',
        textColor: [128, 128, 128] // Cinza para "TOTAL:"
      }, 
      5: { 
        halign: 'center', 
        fontStyle: 'bold',
        textColor: [0, 0, 0] // Preto para o valor
      }
    }
  })

  // Adicionar totais por categoria
  const totaisPorCategoria = data.totais_por_categoria
  const linhasTotaisCategoria = [
    ['', '', '', '', 'Total em Dinheiro:', totaisPorCategoria.dinheiro.formatado, ''],
    ['', '', '', '', 'Total no Cartão:', totaisPorCategoria.cartao.formatado, ''],
    ['', '', '', '', 'Total via Pix:', totaisPorCategoria.pix.formatado, ''],
    ['', '', '', '', 'Gratuito por Abastecimento:', totaisPorCategoria.abastecimento.formatado, '']
  ]
  
  autoTable(doc, {
    body: linhasTotaisCategoria,
    startY: doc.lastAutoTable.finalY + 2, // Pequeno espaço após o total geral
    styles: {
      font: 'helvetica',
      fontStyle: 'normal',
      fontSize: 8,
      cellPadding: 2,
      textColor: [60, 60, 60],
      lineColor: [220, 220, 220],
      lineWidth: 0.1,
      fillColor: [250, 250, 250], // Fundo mais claro
      halign: 'center'
    },
    columnStyles: {
      4: { 
        halign: 'right',
        textColor: [100, 100, 100], // Cinza mais claro para os labels
        fontStyle: 'normal'
      }, 
      5: { 
        halign: 'center', 
        fontStyle: 'bold',
        textColor: [0, 0, 0] // Preto para os valores
      }
    }
  })
  
  // Nome do arquivo com a data e turno
  const nomeArquivo = `movimentacoes_${dataSelecionada.value.replace(/\//g, '-')}_turno${turnoSelecionado.value}.pdf`
  doc.save(nomeArquivo)
}
</script>
