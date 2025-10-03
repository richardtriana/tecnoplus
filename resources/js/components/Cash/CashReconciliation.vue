<!-- resources/js/components/Cash/CashReconciliation.vue -->
<template>
  <div class="page w-100 ml-3">
    <!-- Header -->
    <div class="page-header d-flex justify-content-between align-items-center mb-4">
      <h3>
        <i
          :class="[
            'bi bi-cash-stack',
            sessionOpen ? 'text-success' : 'text-danger'
          ]"
          class="me-2"
        ></i>
        Gestión de Caja
      </h3>
      <div>
        <button
          v-if="selectedBox && !sessionOpen"
          class="btn btn-primary"
          @click="showOpenModal"
        >
          <i class="bi bi-box-arrow-in-up"></i> Abrir Caja
        </button>
        <button
          v-else-if="sessionOpen"
          class="btn btn-warning"
          @click="showCloseModal"
        >
          <i class="bi bi-box-arrow-in-down"></i> Cerrar Caja
        </button>
        <button
          class="btn btn-secondary"
          :disabled="!selectedBox"
          @click="showHistory"
        >
          <i class="bi bi-clock-history"></i> Historial
        </button>
      </div>
    </div>

    <!-- Selector de Caja -->
    <div class="form-group mb-4">
      <label>Seleccionar Caja</label>
      <v-select
        :options="listBoxes"
        label="name"
        :reduce="b => b.id"
        v-model="selectedBox"
        placeholder="Seleccione una caja"
      />
    </div>

    <!-- Mensajes según estado -->
    <div v-if="!selectedBox" class="alert alert-light text-center">
      Seleccione una caja para ver su arqueo
    </div>
    <div
      v-else-if="selectedBox && !sessionOpen"
      class="alert alert-danger text-center"
    >
      <i class="bi bi-lock-fill"></i> No hay sesión abierta
    </div>
    <div v-else class="mb-2">
      <small>
        Corte iniciado el
        <strong>{{ session.opened_at | formatDateTime }}</strong>
      </small>
    </div>

    <!-- Resumen de sesión: tarjetas -->
    <div v-if="sessionOpen" class="row mb-4">
      <div class="col-md-2" v-for="card in summaryCards" :key="card.key">
        <div
          :class="[
            'card',
            'h-100',
            'text-center',
            'card-hover',
            `border-${card.color}`
          ]"
        >
          <div class="card-body d-flex flex-column justify-content-center">
            <i
              :class="[
                card.icon,
                `text-${card.color}`,
                'display-4',
                'mb-2'
              ]"
            ></i>
            <h6 class="card-title">{{ card.title }}</h6>
            <p class="card-text display-6">
              <span v-if="card.isCurrency">
                {{ card.amount | currency }}
              </span>
              <span v-else>
                {{ card.amount }}
              </span>
            </p>
          </div>
        </div>
      </div>
    </div>

    <!-- Detalle por Forma/Método de Pago -->
    <div v-if="sessionOpen" class="mb-4">
      <h5>Detalle por Forma/Método de Pago</h5>
      <table class="table table-sm table-hover">
        <thead class="thead-light">
          <tr>
            <th>Forma Pago</th>
            <th>Método Pago</th>
            <th># Facturas</th>
            <th>Ventas Crédito</th>
            <th>Ventas Contado</th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="(row, i) in sessionReport"
            :key="i"
            @click="showRowDetail(row)"
            class="cursor-pointer"
          >
            <td>{{ row.payment_form }}</td>
            <td>{{ row.payment_method || '-' }}</td>
            <td>{{ row.number_of_orders }}</td>
            <td>{{ row.credit_sales | currency }}</td>
            <td>{{ row.cash_sales   | currency }}</td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Modal: Historial de Cierres -->
    <div class="modal fade" id="historyModal" tabindex="-1" ref="historyModal">
      <div class="modal-dialog modal-xl">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">
              <i class="bi bi-clock-history me-2"></i>
              Historial de Cierres
            </h5>
            <button class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body p-4">
            <CashReconciliationHistory
              :selected-box="selectedBox"
            />
          </div>
          <div class="modal-footer">
            <button
              type="button"
              class="btn btn-secondary"
              data-bs-dismiss="modal"
            >
              Cerrar
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal: Detalle de facturas -->
    <div
      class="modal fade"
      id="detailModal"
      tabindex="-1"
      ref="detailModal"
    >
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">{{ detailTitle }}</h5>
            <button
              type="button"
              class="btn-close"
              data-bs-dismiss="modal"
            ></button>
          </div>
          <div class="modal-body p-4">
            <table class="table table-striped">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Factura</th>
                  <th>Cliente</th>
                  <th class="text-end">Total</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="inv in detailInvoices" :key="inv.id">
                  <td>{{ inv.id }}</td>
                  <td>{{ inv.bill_number || inv.no_invoice }}</td>
                  <td>{{ inv.client?.razon_social || '-' }}</td>
                  <td class="text-end">{{ inv.total_iva_inc | currency }}</td>
                </tr>
                <tr v-if="!detailInvoices.length">
                  <td colspan="4" class="text-center text-muted">
                    No hay facturas
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
          <div class="modal-footer">
            <button
              type="button"
              class="btn btn-secondary"
              data-bs-dismiss="modal"
            >
              Cerrar
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal: Apertura de Caja -->
    <div class="modal fade" id="openModal" tabindex="-1" ref="openModal">
      <div class="modal-dialog modal-sm">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Apertura de Caja</h5>
            <button class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <label>Base Inicial</label>
            <input
              type="number"
              class="form-control mb-2"
              v-model.number="openBalance"
            />
            <small class="text-muted">
              Base mostrada: <strong>{{ openBalance | currency }}</strong>
            </small>
          </div>
          <div class="modal-footer">
            <button
              class="btn btn-secondary"
              data-bs-dismiss="modal"
            >
              Cancelar
            </button>
            <button class="btn btn-primary" @click="openCash">
              Abrir Caja
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal: Cierre de Caja -->
    <div class="modal fade" id="closeModal" tabindex="-1" ref="closeModal">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Cierre de Caja</h5>
            <button class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <p>
              Total en Caja:
              <strong>{{ totalEnCaja | currency }}</strong>
            </p>
            <div class="form-group">
              <label>¿Cuánto hay en caja?</label>
              <input
                type="number"
                class="form-control"
                v-model.number="reportedAmount"
              />
              <small class="form-text text-muted">
                {{ reportedAmount | currency }}
              </small>
            </div>
            <p :class="difference < 0 ? 'text-danger' : 'text-success'">
              {{ difference < 0 ? 'Faltan' : 'Sobra' }}
              <strong>{{ Math.abs(difference) | currency }}</strong>
            </p>
          </div>
          <div class="modal-footer">
            <button
              class="btn btn-secondary"
              data-bs-dismiss="modal"
            >
              Cancelar
            </button>
            <button class="btn btn-warning" @click="closeCash">
              Confirmar Cierre
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import vSelect from 'vue-select'
import axios   from 'axios'
import CashReconciliationHistory from './CashReconciliationHistory.vue'

export default {
  name: 'CashReconciliation',
  components: {
    'v-select': vSelect,
    CashReconciliationHistory
  },
  data() {
    return {
      listBoxes:      [],
      selectedBox:    null,
      sessionOpen:    false,
      session:        {},
      sessionReport:  [],
      sessionTotals:  {},
      openBalance:    0,
      reportedAmount: 0,
      detailTitle:    '',
      detailInvoices: []
    }
  },
  filters: {
    currency(v) {
      return new Intl.NumberFormat('es-CO',{ style:'currency',currency:'COP' }).format(v)
    },
    formatDateTime(v) {
      if (!v) return ''
      return new Date(v).toLocaleString('es-CO',{
        day:'2-digit',month:'short',year:'numeric',
        hour:'2-digit',minute:'2-digit'
      })
    }
  },
  mounted() {
    this.loadBoxes()
    this.$watch('selectedBox', () => this.loadSession())
  },
  computed: {
    summaryCards() {
      const base = parseFloat(this.session.opening_balance) || 0
      const efectivo = parseFloat(
        (this.sessionReport.find(r =>
          r.payment_method &&
          r.payment_method.toLowerCase() === 'efectivo'
        ) || {}).cash_sales
      ) || 0
      const credit = parseFloat(this.sessionTotals.credit_sales) || 0
      const count = this.sessionTotals.number_of_orders || 0
      return [
        {
          key:'base',
          title:'Base Inicial',
          amount: base,
          isCurrency:true,
          icon:'bi bi-cash-stack',
          color:'info'
        },
        {
          key:'ventasEfect',
          title:'Ventas Efectivo',
          amount: efectivo,
          isCurrency:true,
          icon:'bi bi-currency-dollar',
          color:'success'
        },
        {
          key:'ventasCred',
          title:'Ventas Crédito',
          amount: credit,
          isCurrency:true,
          icon:'bi bi-credit-card',
          color:'warning'
        },
        {
          key:'numFact',
          title:'N° Facturas',
          amount: count,
          isCurrency:false,
          icon:'bi bi-receipt',
          color:'secondary'
        },
        {
          key:'totalCaja',
          title:'Total en Caja',
          amount: base + efectivo,
          isCurrency:true,
          icon:'bi bi-wallet2',
          color:'primary'
        }
      ]
    },
    totalEnCaja() {
      return (this.summaryCards.find(c => c.key==='totalCaja')||{}).amount || 0
    },
    difference() {
      return (this.reportedAmount || 0) - this.totalEnCaja
    }
  },
  methods: {
    loadBoxes() {
      axios.get('api/boxes/byUser', this.$root.config)
           .then(r => this.listBoxes = r.data.boxes)
    },
    loadSession() {
      if (!this.selectedBox) return
      axios.get(`api/cash-reconciliations/open?box_id=${this.selectedBox}`, this.$root.config)
           .then(r => {
             this.sessionOpen  = !!r.data.is_open
             this.session      = r.data
             this.openBalance  = parseFloat(r.data.opening_balance) || 0
             if (this.sessionOpen) {
               this.loadSessionReport()
               this.reportedAmount = this.totalEnCaja
             } else {
               this.sessionReport = []
               this.sessionTotals = {}
             }
           })
    },
    loadSessionReport() {
      axios.get(`api/cash-reconciliations/session-report?box_id=${this.selectedBox}`, this.$root.config)
           .then(r => {
             this.sessionReport = r.data.session
             this.sessionTotals = r.data.totals
           })
    },
    showHistory() {
      $('#historyModal').modal('show')
    },
    showOpenModal() {
      $('#openModal').modal('show')
    },
    showCloseModal() {
      $('#closeModal').modal('show')
    },
    openCash() {
      axios.post('api/cash-reconciliations', {
        box_id: this.selectedBox,
        opening_balance: this.openBalance
      }, this.$root.config)
      .then(() => {
        $('#openModal').modal('hide')
        this.loadSession()
      })
    },
    closeCash() {
      axios.put(
        `api/cash-reconciliations/${this.session.id}/close`,
        { reported_balance: this.reportedAmount },
        this.$root.config
      ).then(() => {
        $('#closeModal').modal('hide')
        this.loadSession()
      })
    },
    showRowDetail(row) {
      this.detailTitle = `Facturas - ${row.payment_form}` +
                         (row.payment_method ? ` / ${row.payment_method}` : '')
      axios.get(
        `api/cash-reconciliations/orders?box_id=${this.selectedBox}` +
        `&payment_form_id=${row.payment_form_id}` +
        (row.payment_method_id ? `&payment_method_id=${row.payment_method_id}` : ''),
        this.$root.config
      )
      .then(r => {
        this.detailInvoices = r.data.orders || r.data
        $('#detailModal').modal('show')
      })
    }
  }
}
</script>

<style scoped>
.modal-body {
  padding: 1.5rem !important;
}
.table-striped tbody tr {
  vertical-align: middle;
}
.page-header h3 {
  margin-bottom: 1rem;
}
.card-hover:hover {
  box-shadow: 0 4px 12px rgba(0,0,0,0.15);
  transform: translateY(-2px);
  transition: all 0.2s ease-in-out;
}
.cursor-pointer {
  cursor: pointer;
}
</style>
