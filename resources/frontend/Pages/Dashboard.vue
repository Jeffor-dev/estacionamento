<template layout="AppShell,Authenticated">
  <div class="tw-p-6">
    <Head :title="title" />

    <div class="row q-gutter-md">
      <!-- Cards de Estatísticas -->
      <div class="col-12">
        <h1 class="text-h4 tw-mb-6">{{ title }}</h1>
      </div>

      <!-- Card Lucro do Dia -->
      <div class="col-12 col-md-6 col-lg-3">
        <q-card class="text-white" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%)">
          <q-card-section>
            <div class="text-h6">Lucro do Dia</div>
            <div class="text-h4">R$ {{ formatMoney(stats.lucroDia) }}</div>
            <div class="text-subtitle2">{{ new Date().toLocaleDateString('pt-BR') }}</div>
          </q-card-section>
        </q-card>
      </div>

      <!-- Card Lucro do Mês -->
      <div class="col-12 col-md-6 col-lg-3">
        <q-card class="text-white" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%)">
          <q-card-section>
            <div class="text-h6">Lucro do Mês</div>
            <div class="text-h4">R$ {{ formatMoney(stats.lucroMes) }}</div>
            <div class="text-subtitle2">{{ new Date().toLocaleDateString('pt-BR', { month: 'long', year: 'numeric' }) }}</div>
          </q-card-section>
        </q-card>
      </div>

      <!-- Card Lucro Total -->
      <div class="col-12 col-md-6 col-lg-3">
        <q-card class="text-white" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%)">
          <q-card-section>
            <div class="text-h6">Lucro Total</div>
            <div class="text-h4">R$ {{ formatMoney(stats.lucroTotal) }}</div>
            <div class="text-subtitle2">Desde o início</div>
          </q-card-section>
        </q-card>
      </div>

      <!-- Card Motoristas -->
      <div class="col-12 col-md-6 col-lg-3">
        <q-card class="text-white" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%)">
          <q-card-section>
            <div class="text-h6">Motoristas</div>
            <div class="text-h4">{{ stats.totalMotoristas }}</div>
            <div class="text-subtitle2">Cadastrados</div>
          </q-card-section>
        </q-card>
      </div>

      <!-- Cards de Atividade -->
      <div class="col-12 col-md-6">
        <q-card>
          <q-card-section>
            <div class="text-h6 q-mb-md">Atividade de Hoje</div>
            <div class="row q-gutter-md">
              <div class="col">
                <q-linear-progress 
                  :value="stats.movimentacoesDia / 20" 
                  size="8px" 
                  color="primary"
                  class="q-mb-sm"
                />
                <div class="text-h5">{{ stats.movimentacoesDia }}</div>
                <div class="text-caption text-grey-6">Movimentações</div>
              </div>
              <div class="col">
                <q-linear-progress 
                  :value="stats.veiculosAtivos / 10" 
                  size="8px" 
                  color="orange"
                  class="q-mb-sm"
                />
                <div class="text-h5">{{ stats.veiculosAtivos }}</div>
                <div class="text-caption text-grey-6">Veículos Ativos</div>
              </div>
            </div>
          </q-card-section>
        </q-card>
      </div>

      <!-- Gráfico de Lucro Semanal -->
      <div class="col-12 col-md-6">
        <q-card>
          <q-card-section>
            <div class="text-h6 q-mb-md">Lucro dos Últimos 7 Dias</div>
            <div style="height: 200px;">
              <Line
                :data="chartLucroSemana"
                :options="chartOptions"
                v-if="chartLucroSemana"
              />
            </div>
          </q-card-section>
        </q-card>
      </div>

      <!-- Gráfico de Tipos de Pagamento -->
      <div class="col-12 col-md-6">
        <q-card>
          <q-card-section>
            <div class="text-h6 q-mb-md">Tipos de Pagamento</div>
            <div style="height: 200px;">
              <Doughnut
                :data="chartTiposPagamento"
                :options="doughnutOptions"
                v-if="chartTiposPagamento"
              />
            </div>
          </q-card-section>
        </q-card>
      </div>

      <!-- Gráfico de Movimentações Mensais -->
      <div class="col-12 col-md-6">
        <q-card>
          <q-card-section>
            <div class="text-h6 q-mb-md">Movimentações por Mês</div>
            <div style="height: 200px;">
              <Bar
                :data="chartMovimentacoesMes"
                :options="barOptions"
                v-if="chartMovimentacoesMes"
              />
            </div>
          </q-card-section>
        </q-card>
      </div>
    </div>
  </div>
</template>

<script setup>
import { Line, Bar, Doughnut } from 'vue-chartjs'
import {
  Chart as ChartJS,
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  BarElement,
  ArcElement,
  Title,
  Tooltip,
  Legend,
  Filler
} from 'chart.js'

// Registrar componentes do Chart.js
ChartJS.register(
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  BarElement,
  ArcElement,
  Title,
  Tooltip,
  Legend,
  Filler
)

const props = defineProps({
  title: String,
  stats: Object,
  graficos: Object
})

// Função para formatar valores monetários
const formatMoney = (value) => {
  return new Intl.NumberFormat('pt-BR', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  }).format(value || 0)
}

// Configuração do gráfico de lucro semanal
const chartLucroSemana = computed(() => {
  if (!props.graficos?.lucroSemana) return null
  
  return {
    labels: props.graficos.lucroSemana.map(item => item.data),
    datasets: [
      {
        label: 'Lucro (R$)',
        data: props.graficos.lucroSemana.map(item => item.valor),
        borderColor: '#667eea',
        backgroundColor: 'rgba(102, 126, 234, 0.1)',
        tension: 0.4,
        fill: true
      }
    ]
  }
})

// Configuração do gráfico de tipos de pagamento
const chartTiposPagamento = computed(() => {
  if (!props.graficos?.tiposPagamento) return null
  
  return {
    labels: props.graficos.tiposPagamento.map(item => item.tipo),
    datasets: [
      {
        data: props.graficos.tiposPagamento.map(item => item.total),
        backgroundColor: [
          '#FF6384',
          '#36A2EB',
          '#FFCE56',
          '#4BC0C0',
          '#9966FF',
          '#FF9F40'
        ]
      }
    ]
  }
})

// Configuração do gráfico de movimentações mensais
const chartMovimentacoesMes = computed(() => {
  if (!props.graficos?.movimentacoesMes) return null
  
  return {
    labels: props.graficos.movimentacoesMes.map(item => item.mes),
    datasets: [
      {
        label: 'Movimentações',
        data: props.graficos.movimentacoesMes.map(item => item.total),
        backgroundColor: 'rgba(244, 156, 166, 0.8)',
        borderColor: '#f49ca6',
        borderWidth: 1
      }
    ]
  }
})

// Opções gerais dos gráficos
const chartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      display: false
    }
  },
  scales: {
    y: {
      beginAtZero: true,
      grid: {
        color: 'rgba(0,0,0,0.1)'
      }
    },
    x: {
      grid: {
        display: false
      }
    }
  }
}

const doughnutOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      position: 'bottom'
    }
  }
}

const barOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      display: false
    }
  },
  scales: {
    y: {
      beginAtZero: true,
      grid: {
        color: 'rgba(0,0,0,0.1)'
      }
    },
    x: {
      grid: {
        display: false
      }
    }
  }
}
</script>
