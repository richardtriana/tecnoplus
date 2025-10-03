<!-- resources/js/components/Cash/CashReconciliationHistory.vue -->
<template>
  <div>
    <!-- TOTALIZADOS -->
    <div class="row mb-3">
      <div class="col-md-3" v-for="card in totalCards" :key="card.key">
        <div :class="['card', 'text-white', card.bg]">
          <div class="card-body text-center">
            <h6 class="card-title">{{ card.title }}</h6>
            <p class="display-6 mb-0">{{ card.amount | currency }}</p>
          </div>
        </div>
      </div>
    </div>

    <!-- FILTROS -->
    <div class="row g-2 align-items-end mb-3">
      <div class="col-md-2">
        <label class="form-label">ID Cierre</label>
        <input
          type="number"
          v-model.number="filter.id"
          class="form-control"
          placeholder="—"
        />
      </div>
      <div class="col-md-2">
        <label class="form-label">Cajero Inicial</label>
        <v-select
          :options="userList"
          label="name"
          :reduce="u => u.id"
          v-model="filter.openingUserId"
          placeholder="Todos"
        />
      </div>
      <div class="col-md-2">
        <label class="form-label">Desde</label>
        <input
          type="date"
          v-model="filter.from"
          class="form-control"
        />
      </div>
      <div class="col-md-2">
        <label class="form-label">Hasta</label>
        <input
          type="date"
          v-model="filter.to"
          class="form-control"
        />
      </div>
      <div class="col-md-2">
        <label class="form-label">Tipo Diferencia</label>
        <select v-model="filter.diffType" class="form-select">
          <option value="all">Todos</option>
          <option value="positive">Sobrante</option>
          <option value="negative">Faltante</option>
          <option value="zero">Correcto</option>
        </select>
      </div>
      <div class="col-md-2">
        <label class="form-label">Por página</label>
        <input
          type="number"
          v-model.number="perPage"
          min="1"
          class="form-control"
        />
      </div>
    </div>

    <!-- TABLA -->
    <table class="table table-sm table-hover history-table">
      <thead class="thead-light">
        <tr>
          <th>ID</th>
          <th>Apertura</th>
          <th>Cierre</th>
          <th>Cajero Inicial</th>
          <th>Cajero Cierre</th>
          <th class="highlight-header">Calculado</th>
          <th class="highlight-header">Reportado</th>
          <th class="highlight-header">Diferencia</th>
          <th>Conclusión</th>
          <th>Imprimir</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="hist in paginated" :key="hist.id">
          <td class="value-cell">{{ hist.id }}</td>
          <td class="value-cell">{{ hist.opened_at | formatDateTime }}</td>
          <td class="value-cell">{{ hist.closed_at | formatDateTime }}</td>
          <td class="value-cell">{{ hist.opening_user.name }}</td>
          <td class="value-cell">{{ hist.closing_user.name }}</td>
          <td class="highlight-cell">{{ hist.computed_balance | currency }}</td>
          <td class="highlight-cell">{{ hist.reported_balance | currency }}</td>
          <td
            :class="[
              'highlight-cell',
              hist.difference < 0
                ? 'text-danger'
                : hist.difference > 0
                ? 'text-warning'
                : 'text-success'
            ]"
          >
            {{ hist.difference < 0 ? '-' : '' }}{{ Math.abs(hist.difference) | currency }}
          </td>
          <td>{{ conclusion(hist.difference) }}</td>
          <td class="text-center">
            <button
              class="btn btn-sm btn-outline-primary"
              @click="printRow(hist)"
            >
              <i class="bi bi-printer"></i>
            </button>
          </td>
        </tr>
        <tr v-if="!filtered.length">
          <td colspan="10" class="text-center text-muted">
            No hay cierres para estos filtros.
          </td>
        </tr>
      </tbody>
    </table>

    <!-- PAGINACIÓN -->
    <nav v-if="pages > 1" aria-label="Page navigation">
      <ul class="pagination justify-content-center">
        <li class="page-item" :class="{ disabled: currentPage === 1 }">
          <button
            class="page-link"
            @click="currentPage--"
            :disabled="currentPage === 1"
          >
            Anterior
          </button>
        </li>
        <li
          class="page-item"
          v-for="p in pages"
          :key="p"
          :class="{ active: p === currentPage }"
        >
          <button class="page-link" @click="currentPage = p">{{ p }}</button>
        </li>
        <li class="page-item" :class="{ disabled: currentPage === pages }">
          <button
            class="page-link"
            @click="currentPage++"
            :disabled="currentPage === pages"
          >
            Siguiente
          </button>
        </li>
      </ul>
    </nav>
  </div>
</template>

<script>
import axios from 'axios'
import vSelect from 'vue-select'

export default {
  name: 'CashReconciliationHistory',
  components: { 'v-select': vSelect },
  props: {
    selectedBox: {
      type: [Number, String],
      required: true
    }
  },
  data() {
    return {
      history: [],
      userList: [],
      filter: {
        id: '',
        openingUserId: '',
        from: '',
        to: '',
        diffType: 'all'
      },
      perPage: 5,
      currentPage: 1
    }
  },
  watch: {
    selectedBox(newId) {
      this.loadHistory(newId)
      this.loadUsers()
    }
  },
  mounted() {
    if (this.selectedBox) {
      this.loadHistory(this.selectedBox)
      this.loadUsers()
    }
  },
  methods: {
    loadHistory(boxId) {
      axios
        .get(`api/cash-reconciliations/closed?box_id=${boxId}`, this.$root.config)
        .then(r => {
          this.history = r.data
          this.currentPage = 1
        })
        .catch(console.error)
    },
    loadUsers() {
      axios
        .get('api/users/user-list', this.$root.config)
        .then(r => {
          this.userList = r.data.users
        })
        .catch(console.error)
    },
    conclusion(diff) {
      const val = Number(diff)
      if (Math.abs(val) < 0.001) return 'Correcto'
      return val > 0 ? 'Sobrante' : 'Faltante'
    },
    printRow(hist) {
      axios
        .post(
          'api/reports-ticket/history',
          { data: [hist] },
          this.$root.config
        )
        .catch(console.error)
    }
  },
  computed: {
    filtered() {
      return this.history.filter(h => {
        if (this.filter.id && h.id !== this.filter.id) return false
        if (
          this.filter.openingUserId &&
          h.opening_user_id != this.filter.openingUserId
        )
          return false
        if (this.filter.from && h.opened_at.slice(0, 10) < this.filter.from)
          return false
        if (this.filter.to && h.closed_at.slice(0, 10) > this.filter.to)
          return false
        if (this.filter.diffType === 'positive' && h.difference <= 0)
          return false
        if (this.filter.diffType === 'negative' && h.difference >= 0)
          return false
        if (this.filter.diffType === 'zero' && Math.abs(h.difference) > 0.001)
          return false
        return true
      })
    },
    pages() {
      return Math.ceil(this.filtered.length / this.perPage) || 1
    },
    paginated() {
      const start = (this.currentPage - 1) * this.perPage
      return this.filtered.slice(start, start + this.perPage)
    },
    totalCards() {
      const totalCalc = this.history.reduce(
        (sum, h) => sum + Number(h.computed_balance),
        0
      )
      const totalRep = this.history.reduce(
        (sum, h) => sum + Number(h.reported_balance),
        0
      )
      const totalDiff = this.history.reduce(
        (sum, h) => sum + Number(h.difference),
        0
      )
      return [
        { key:'count', title:'# Cierres',        amount:this.history.length,       bg:'bg-primary' },
        { key:'calc',  title:'Total Calculado',  amount:totalCalc,                bg:'bg-info'    },
        { key:'rep',   title:'Total Reportado',  amount:totalRep,                 bg:'bg-success' },
        { key:'diff',  title:'Total Diferencia', amount:totalDiff,                bg:'bg-warning' }
      ]
    }
  },
  filters: {
    currency(v) {
      return new Intl.NumberFormat('es-CO',{style:'currency',currency:'COP'}).format(v)
    },
    formatDateTime(v) {
      if (!v) return ''
      return new Date(v).toLocaleString('es-CO',{
        day:'2-digit',month:'short',year:'numeric',
        hour:'2-digit',minute:'2-digit'
      })
    }
  }
}
</script>

<style scoped>
.history-table td.value-cell {
  font-size: 0.95rem;
}
.history-table th.highlight-header {
  font-weight: bold;
  font-size: 1rem;
  text-align: center;
}
.history-table td.highlight-cell {
  font-weight: bold;
  font-size: 1.2rem;
  text-align: center;
}
</style>
