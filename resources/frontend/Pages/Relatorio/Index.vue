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

          <q-list bordered>
            <q-item clickable @click="exportarMotoristasPDF">
              <q-item-section>Lista de motoristas cadastrados</q-item-section>
              <q-item-section side><q-btn color="primary" label="Exportar PDF" /></q-item-section>
            </q-item>
            <q-item clickable @click="exportarMovimentacoesPDF">
              <q-item-section>{{ tituloMovimentacoes }}</q-item-section>
              <q-item-section side><q-btn color="primary" label="Exportar PDF" /></q-item-section>
            </q-item>
          </q-list>
        </q-card-section>
      </q-card>
    </div>
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

// Computed para o título das movimentações baseado na data selecionada
const tituloMovimentacoes = computed(() => {
  const hoje = new Date().toLocaleDateString('pt-BR')
  if (dataSelecionada.value === hoje) {
    return 'Lista de movimentações do dia (hoje)'
  } else {
    return `Lista de movimentações do dia ${dataSelecionada.value}`
  }
})

// Função para converter data de DD/MM/YYYY para YYYY-MM-DD
const formatarDataParaAPI = (data) => {
  const [dia, mes, ano] = data.split('/')
  return `${ano}-${mes}-${dia}`
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
  doc.text('Movimentações do dia ' + dataSelecionada.value, 12, 14)
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
  
  // Nome do arquivo com a data
  const nomeArquivo = `movimentacoes_${dataSelecionada.value.replace(/\//g, '-')}.pdf`
  doc.save(nomeArquivo)
}
</script>
