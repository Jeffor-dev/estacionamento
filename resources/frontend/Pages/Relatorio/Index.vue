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
          
          <!-- Filtro de Data -->
          <div class="tw-mb-4">
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
          </div>

          <!-- Relatório Mensal -->
          <div class="tw-mb-4">
            <q-card flat bordered class="q-pa-md">
              <div class="text-subtitle1 q-mb-md">Relatório Mensal</div>
              <div class="row q-gutter-md items-end">
                <div class="col-md-2 col-sm-4 col-xs-6">
                  <q-select
                    filled
                    v-model="mesSelecionado"
                    :options="opcoMeses"
                    label="Mês"
                    option-value="value"
                    option-label="label"
                    map-options
                    emit-value
                  />
                </div>
                <div class="col-md-2 col-sm-4 col-xs-6">
                  <q-input
                    filled
                    v-model.number="anoSelecionado"
                    label="Ano"
                    type="number"
                    :min="2020"
                    :max="2030"
                  />
                </div>
                <div class="col-md-4 col-sm-4 col-xs-12">
                  <q-btn 
                    color="primary" 
                    label="Gerar Relatório Mensal PDF"
                    @click="exportarRelatorioMensalPDF"
                    :loading="gerandoMensal"
                  />
                </div>
              </div>
            </q-card>
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

// Variáveis do relatório mensal
const mesSelecionado = ref(new Date().getMonth() + 1) // Mês atual
const anoSelecionado = ref(new Date().getFullYear()) // Ano atual
const gerandoMensal = ref(false)

// Opções de meses
const opcoMeses = [
  { value: 1, label: 'Janeiro' },
  { value: 2, label: 'Fevereiro' },
  { value: 3, label: 'Março' },
  { value: 4, label: 'Abril' },
  { value: 5, label: 'Maio' },
  { value: 6, label: 'Junho' },
  { value: 7, label: 'Julho' },
  { value: 8, label: 'Agosto' },
  { value: 9, label: 'Setembro' },
  { value: 10, label: 'Outubro' },
  { value: 11, label: 'Novembro' },
  { value: 12, label: 'Dezembro' }
]

// Computed para o título das movimentações baseado na data selecionada
const tituloMovimentacoes = computed(() => {
  const hoje = new Date().toLocaleDateString('pt-BR')
  
  if (dataSelecionada.value === hoje) {
    return `Lista de movimentações do dia (hoje)`
  } else {
    return `Lista de movimentações do dia ${dataSelecionada.value}`
  }
})

// Computed para o título do modal de visualização
const tituloModalVisualizacao = computed(() => {
  return `Movimentações - ${dataSelecionada.value}`
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
        data: dataFormatada
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
      data: dataFormatada
    }
  })
  const doc = new jsPDF()
  doc.setFontSize(12)
  
  // Título simples do PDF
  doc.text(`Movimentações do dia ${dataSelecionada.value}`, 12, 14)
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
  
  // Nome do arquivo com a data
  const nomeArquivo = `movimentacoes_${dataSelecionada.value.replace(/\//g, '-')}.pdf`
  doc.save(nomeArquivo)
}

async function exportarRelatorioMensalPDF() {
  gerandoMensal.value = true
  
  try {
    const { data } = await axios.get('/api/relatorio/mensal', {
      params: {
        mes: mesSelecionado.value,
        ano: anoSelecionado.value
      }
    })
    
    const doc = new jsPDF()
    doc.setFontSize(16)
    doc.text(`Relatório Mensal - ${data.nome_mes}/${data.ano}`, 14, 20)
    
    // Informações gerais
    doc.setFontSize(12)
    doc.text('Resumo Geral:', 14, 35)
    
    doc.setFontSize(10)
    const resumoY = 45
    doc.text(`• Quantidade total de registros: ${data.quantidade_registros}`, 20, resumoY)
    doc.text(`• Valor total arrecadado: ${data.valor_total_formatado}`, 20, resumoY + 10)
    
    // Totais por forma de pagamento
    doc.setFontSize(12)
    doc.text('Arrecadação por Forma de Pagamento:', 14, resumoY + 25)
    
    doc.setFontSize(10)
    const pagamentoY = resumoY + 35
    doc.text(`• Dinheiro: ${data.totais_por_forma_pagamento.dinheiro.formatado}`, 20, pagamentoY)
    doc.text(`• Cartão: ${data.totais_por_forma_pagamento.cartao.formatado}`, 20, pagamentoY + 10)
    doc.text(`• Pix: ${data.totais_por_forma_pagamento.pix.formatado}`, 20, pagamentoY + 20)
    doc.text(`• Abastecimento (Gratuito): ${data.totais_por_forma_pagamento.abastecimento.formatado}`, 20, pagamentoY + 30)
    
    // Tabela resumo usando autoTable
    autoTable(doc, {
      startY: pagamentoY + 45,
      head: [['Forma de Pagamento', 'Valor/Quantidade']],
      body: [
        ['Dinheiro', data.totais_por_forma_pagamento.dinheiro.formatado],
        ['Cartão', data.totais_por_forma_pagamento.cartao.formatado],
        ['Pix', data.totais_por_forma_pagamento.pix.formatado],
        ['Abastecimento (Gratuito)', data.totais_por_forma_pagamento.abastecimento.formatado],
        ['TOTAL ARRECADADO', data.valor_total_formatado]
      ],
      styles: {
        font: 'helvetica',
        fontSize: 10,
        cellPadding: 3,
        textColor: [0, 0, 0],
        lineColor: [200, 200, 200],
        lineWidth: 0.1
      },
      headStyles: {
        fontStyle: 'bold',
        fillColor: [52, 152, 219],
        textColor: [255, 255, 255],
        halign: 'center'
      },
      bodyStyles: {
        halign: 'center'
      },
      columnStyles: {
        0: { halign: 'left' },
        1: { halign: 'right', fontStyle: 'bold' }
      },
      // Destacar linha do total
      didParseCell: function (data) {
        if (data.row.index === 4) { // Linha do total
          data.cell.styles.fillColor = [240, 240, 240]
          data.cell.styles.fontStyle = 'bold'
        }
      }
    })
    
    // Rodapé
    const pageHeight = doc.internal.pageSize.height
    doc.setFontSize(8)
    doc.text('Relatório gerado automaticamente pelo sistema de estacionamento', 14, pageHeight - 10)
    doc.text(`Data de geração: ${new Date().toLocaleDateString('pt-BR')} às ${new Date().toLocaleTimeString('pt-BR')}`, 14, pageHeight - 5)
    
    // Nome do arquivo
    const nomeArquivoMensal = `relatorio_mensal_${String(mesSelecionado.value).padStart(2, '0')}_${anoSelecionado.value}.pdf`
    doc.save(nomeArquivoMensal)
    
  } catch (error) {
    console.error('Erro ao gerar relatório mensal:', error)
    // Aqui você pode adicionar uma notificação de erro se quiser
  } finally {
    gerandoMensal.value = false
  }
}
</script>
