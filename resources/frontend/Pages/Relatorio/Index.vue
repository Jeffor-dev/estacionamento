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
          <q-list bordered>
            <q-item clickable @click="exportarMotoristasPDF">
              <q-item-section>Lista de motoristas cadastrados</q-item-section>
              <q-item-section side><q-btn color="primary" label="Exportar PDF" /></q-item-section>
            </q-item>
            <q-item clickable @click="exportarMovimentacoesPDF">
              <q-item-section>Lista de movimentações do dia</q-item-section>
              <q-item-section side><q-btn color="primary" label="Exportar PDF" /></q-item-section>
            </q-item>
          </q-list>
        </q-card-section>
      </q-card>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import axios from 'axios'
import { jsPDF } from 'jspdf'
import autoTable from 'jspdf-autotable'

const props = defineProps({
  title: String
})

async function exportarMotoristasPDF() {
  const { data } = await axios.get('/api/relatorio/motoristas')
  const doc = new jsPDF()
  
  doc.text('Lista de Motoristas Cadastrados', 14, 16)

  doc.setFont('helvetica', 'normal')
  doc.setFontSize(10)
  
  // Use autoTable diretamente
  autoTable(doc, {
    head: [['ID', 'Nome', 'CPF', 'Telefone', 'Empresa', 'Placa', 'Modelo', 'Cor']],
    body: data.motoristas.map(m => [
      m.id, m.nome, m.cpf, m.telefone, m.empresa, m.placa, m.modelo, m.cor
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
  const { data } = await axios.get('/api/relatorio/movimentacoes')
  const doc = new jsPDF()
  
  doc.text('Movimentações do Dia', 14, 16)
  
  // Passar o doc como primeiro parâmetro
  autoTable(doc, {
    head: [['ID', 'Motorista', 'Placa', 'Modelo', 'Cor', 'Entrada', 'Saída', 'Valor']],
    body: data.movimentacoes.map(m => [
      m.id, m.motorista, m.placa, m.modelo, m.cor, m.entrada, m.saida, m.valor
    ]),
    startY: 22
  })
  
  doc.save('movimentacoes_do_dia.pdf')
}
</script>
