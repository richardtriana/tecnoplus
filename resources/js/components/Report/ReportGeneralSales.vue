<template>
  <div class="page w-100">
    <!-- Encabezado -->
    <div class="page-header mb-4">
      <h3>Reporte general de venta</h3>
    </div>

<<<<<<< HEAD
    <!-- Card de Totales Generales -->
    <div class="card mb-3">
      <div class="card-body">
        <div class="row">
          <div class="col-md-6">
            <h5>Total Facturado</h5>
            <p class="display-4">{{ totalFacturado | currency }}</p>
=======
    <!-- TARJETAS DE RESUMEN -->
    <div class="row mb-3 px-3">
      <div class="col-md-6">
        <div class="card mb-2">
          <div class="card-body text-center">
            <h6 class="card-title">Total Facturado</h6>
            <p class="display-4 mb-0">{{ totalFacturado | currency }}</p>
>>>>>>> 0ed4468 (factuara electronica + reserva + caja)
          </div>
        </div>
      </div>
      <div class="col-md-6">
        <div class="card mb-2">
          <div class="card-body text-center">
            <h6 class="card-title">Ganancia del Día</h6>
            <p class="display-4 mb-0">{{ totalGanancia | currency }}</p>
          </div>
        </div>
      </div>
    </div>

<<<<<<< HEAD
    <!-- Card de Pagos Recibidos -->
    <div class="card mb-3">
      <div class="card-body">
        <h5>Pagos recibidos por Forma de pago</h5>
        <ul>
          <li v-for="(sum, form) in totalsByForm" :key="form">
            {{ form }}: {{ sum | currency }}
          </li>
        </ul>
        <h5>Pagos recibidos por Método de pago</h5>
        <ul>
          <li v-for="(sum, method) in totalsByMethod" :key="method">
            {{ method }}: {{ sum | currency }}
          </li>
        </ul>
      </div>
    </div>

    <!-- Filtros y Botones -->
    <div class="page-search mx-2 my-2 border p-2">
      <div class="form-row">
        <div class="form-group col-md-3">
          <label for="box">Caja</label>
          <v-select
            :options="boxList"
            label="name"
            :reduce="b => b.id"
            v-model="filter.box_id"
          />
        </div>
        <div class="form-group col-3">
          <label for="status">Estado</label>
          <v-select
            :options="statusOrders"
            label="status"
            :reduce="s => s.id"
            v-model="filter.status"
          />
        </div>
        <div class="form-group col-md-3">
          <label for="from_date">Desde</label>
          <input
            type="datetime-local"
            class="form-control"
            v-model="filter.from"
          />
        </div>
        <div class="form-group col-md-3">
          <label for="to_date">Hasta</label>
          <input
            type="datetime-local"
            class="form-control"
            v-model="filter.to"
          />
        </div>
        <div class="form-group col-3">
          <label for="user">Usuario</label>
          <v-select
            :options="userList"
            label="name"
            :reduce="u => u.id"
            v-model="filter.user_id"
          />
        </div>
        <div class="col my-4 col-4">
          <button
            class="btn btn-success btn-block"
            @click="getOrders(1)"
          >
            Buscar <i class="bi bi-search"></i>
          </button>
        </div>
        <div class="col-md-3 my-4">
          <button
            class="btn btn-outline-success btn-block"
            @click="getTicket()"
          >
=======
    <!-- GRÁFICOS -->
    <div class="row mb-4 px-3">
      <div class="col-md-6">
        <div class="card mb-3">
          <div class="card-body">
            <h6>Pagos por Forma de Pago</h6>
            <apexchart
              type="pie"
              height="250"
              :options="formChartOptions"
              :series="formChartSeries"
            />
          </div>
        </div>
      </div>
      <div class="col-md-6">
        <div class="card mb-3">
          <div class="card-body">
            <h6>Pagos por Método de Pago</h6>
            <apexchart
              type="pie"
              height="250"
              :options="methodChartOptions"
              :series="methodChartSeries"
            />
          </div>
        </div>
      </div>
    </div>

    <!-- FILTROS -->
    <div class="page-search mx-2 my-2 border p-3">
      <div class="row g-3">
        <div class="col-md-3">
          <label>Caja</label>
          <v-select
            :options="boxList"
            label="name"
            :reduce="b => b.id"
            v-model="filter.box_id"
            placeholder="Todas"
          />
        </div>
        <div class="col-md-3">
          <label>Estado</label>
          <v-select
            :options="statusOrders"
            label="status"
            :reduce="s => s.id"
            v-model="filter.status"
            placeholder="Todos"
          />
        </div>
        <div class="col-md-3">
          <label>Desde</label>
          <input type="datetime-local" class="form-control" v-model="filter.from" />
        </div>
        <div class="col-md-3">
          <label>Hasta</label>
          <input type="datetime-local" class="form-control" v-model="filter.to" />
        </div>
        <div class="col-md-3">
          <label>Usuario</label>
          <v-select
            :options="userList"
            label="name"
            :reduce="u => u.id"
            v-model="filter.user_id"
            placeholder="Todos"
          />
        </div>
        <div class="col-md-3">
          <label>Registros / página</label>
          <input
            type="number"
            class="form-control"
            v-model.number="perPage"
            min="1"
          />
        </div>
        <div class="col-md-3 d-flex align-items-end">
          <button class="btn btn-success w-100" @click="fetchOrders(1)">
            Buscar <i class="bi bi-search"></i>
          </button>
        </div>
        <div class="col-md-3 d-flex align-items-end">
          <button class="btn btn-outline-success w-100" @click="getTicket">
>>>>>>> 0ed4468 (factuara electronica + reserva + caja)
            Ticket <i class="bi bi-card-text"></i>
          </button>
        </div>
      </div>
    </div>

<<<<<<< HEAD
    <!-- Tabla de Resultados -->
    <div class="page-content">
      <section class="p-3">
        <table class="table table-sm table-bordered table-hover">
          <thead class="thead-dark">
            <tr>
              <th>Número facturas</th>
              <th>Registradas</th>
              <th>Suspendidas</th>
              <th>Cotizadas</th>
              <th>Créditos</th>
              <th>Total costo</th>
              <th>IVA excluido</th>
              <th>IVA incluido</th>
              <th>Descuento</th>
              <th>Total facturado</th>
              <th>Ganancia</th>
              <th>Valor pago</th>
              <th>Forma de pago</th>
              <th>Método de pago</th>
            </tr>
          </thead>
          <tbody v-if="List.length">
            <tr v-for="(l, index) in List" :key="index">
              <td>{{ l.number_of_orders }}</td>
              <td>{{ l.registered }}</td>
              <td>{{ l.suspended }}</td>
              <td>{{ l.quoted }}</td>
              <td>{{ l.credit }}</td>
              <td>{{ l.total_cost_price_tax_inc | currency }}</td>
              <td>{{ l.total_iva_exc | currency }}</td>
              <td>{{ l.total_iva_inc | currency }}</td>
              <td>{{ l.total_discount | currency }}</td>
              <td>{{ l.total_paid | currency }}</td>
              <td>{{ (l.total_iva_exc - l.total_cost_price_tax_inc) | currency }}</td>
              <td>{{ l.payment_value | currency }}</td>
              <td>{{ l.payment_form }}</td>
              <td>{{ l.payment_method }}</td>
            </tr>
          </tbody>
          <tbody v-else>
            <tr>
              <td colspan="14">No hay resultados</td>
            </tr>
          </tbody>
        </table>
      </section>
=======
    <!-- TABLA DE RESULTADOS -->
    <div class="page-content p-3">
      <table class="table table-sm table-bordered table-hover">
        <thead class="thead-dark">
          <tr>
            <th>N° Facturas</th>
            <th>Registradas</th>
            <th>Suspendidas</th>
            <th>Cotizadas</th>
            <th>Créditos</th>
            <th>Total Costo</th>
            <th>IVA Excluido</th>
            <th>IVA Incluido</th>
            <th>Descuento</th>
            <th>Total Facturado</th>
            <th>Ganancia</th>
            <th>Valor Pago</th>
            <th>Forma de Pago</th>
            <th>Método de Pago</th>
          </tr>
        </thead>
        <tbody v-if="sales.data.length">
          <tr v-for="(l, i) in sales.data" :key="i">
            <td>{{ l.number_of_orders }}</td>
            <td>{{ l.registered }}</td>
            <td>{{ l.suspended }}</td>
            <td>{{ l.quoted }}</td>
            <td>{{ l.credit }}</td>
            <td>{{ l.total_cost_price_tax_inc | currency }}</td>
            <td>{{ l.total_iva_exc | currency }}</td>
            <td>{{ l.total_iva_inc | currency }}</td>
            <td>{{ l.total_discount | currency }}</td>
            <td>{{ l.total_paid | currency }}</td>
            <td>{{ (l.total_iva_exc - l.total_cost_price_tax_inc) | currency }}</td>
            <td>{{ l.payment_value | currency }}</td>
            <td>{{ l.payment_form }}</td>
            <td>{{ l.payment_method }}</td>
          </tr>
        </tbody>
        <tbody v-else>
          <tr>
            <td colspan="14" class="text-center">No hay resultados</td>
          </tr>
        </tbody>
      </table>

      <pagination
        :data="sales"
        :limit="perPage"
        align="center"
        @pagination-change-page="fetchOrders"
      />
>>>>>>> 0ed4468 (factuara electronica + reserva + caja)
    </div>
  </div>
</template>

<script>
import axios from 'axios'
import vSelect from 'vue-select'
import VueApexCharts from 'vue-apexcharts'

export default {
  name: 'ReportGeneralSales',
  components: { vSelect, apexchart: VueApexCharts },
  data() {
    return {
      sales: { data: [], current_page: 1 },
      boxList: [],
      userList: [],
      filter: { from: '', to: '', box_id: null, user_id: null, status: '' },
      perPage: 15,
      statusOrders: [
<<<<<<< HEAD
        { id: 0, status: "Desechada" },
        { id: 1, status: "Pedido" },
        { id: 2, status: "Facturado" },
        { id: 3, status: "Cotizado" },
        { id: 5, status: "Crédito" },
      ],
    };
  },
  computed: {
    totalFacturado() {
      return this.List.reduce((sum, i) => sum + Number(i.total_paid), 0);
    },
    totalGanancia() {
      return this.List.reduce(
=======
        { id: '', status: 'Todos' },
        { id: 0, status: 'Desechada' },
        { id: 1, status: 'Pedido' },
        { id: 2, status: 'Facturado' },
        { id: 3, status: 'Cotizado' },
        { id: 5, status: 'Crédito' }
      ]
    }
  },
  computed: {
    totalFacturado() {
      return this.sales.data.reduce((sum, i) => sum + Number(i.total_paid), 0)
    },
    totalGanancia() {
      return this.sales.data.reduce(
>>>>>>> 0ed4468 (factuara electronica + reserva + caja)
        (sum, i) => sum + (Number(i.total_iva_exc) - Number(i.total_cost_price_tax_inc)),
        0
      )
    },
<<<<<<< HEAD
    totalsByForm() {
      return this.List.reduce((acc, i) => {
        const form = i.payment_form || 'N/A';
        acc[form] = (acc[form] || 0) + Number(i.payment_value);
        return acc;
      }, {});
    },
    totalsByMethod() {
      return this.List.reduce((acc, i) => {
        const method = i.payment_method || 'N/A';
        acc[method] = (acc[method] || 0) + Number(i.payment_value);
        return acc;
      }, {});
    },
  },
  methods: {
    getOrders(page = 1) {
      const params = { ...this.filter, page };
      axios
        .get('api/reports/general-sales-report', {
          params,
          headers: this.$root.config.headers,
        })
        .then(({ data }) => {
          this.List = data.orders;
        })
        .catch(() => {
          this.List = [];
        });
=======
    formChartData() {
      const map = {}
      this.sales.data.forEach(i => {
        const f = i.payment_form || 'N/A'
        map[f] = (map[f] || 0) + Number(i.payment_value)
      })
      return Object.entries(map).map(([form, total]) => ({ form, total }))
    },
    formChartSeries() {
      return this.formChartData.map(d => d.total)
    },
    formChartOptions() {
      return {
        labels: this.formChartData.map(d => d.form),
        legend: { position: 'bottom' },
        chart: { toolbar: { show: false } }
      }
    },
    methodChartData() {
      const map = {}
      this.sales.data.forEach(i => {
        const m = i.payment_method || 'N/A'
        map[m] = (map[m] || 0) + Number(i.payment_value)
      })
      return Object.entries(map).map(([method, total]) => ({ method, total }))
    },
    methodChartSeries() {
      return this.methodChartData.map(d => d.total)
    },
    methodChartOptions() {
      return {
        labels: this.methodChartData.map(d => d.method),
        legend: { position: 'bottom' },
        chart: { toolbar: { show: false } }
      }
    }
  },
  watch: {
    perPage() {
      this.fetchOrders(1)
    }
  },
  methods: {
    fetchOrders(page = 1) {
      axios
        .get('api/reports/general-sales-report', {
          params: { ...this.filter, per_page: this.perPage, page },
          headers: this.$root.config.headers
        })
        .then(({ data }) => {
          this.sales.data = data.orders
          this.sales.current_page = page
        })
        .catch(() => {
          this.sales.data = []
        })
>>>>>>> 0ed4468 (factuara electronica + reserva + caja)
    },
    listBoxes() {
      axios
        .get('api/boxes/box-list', this.$root.config)
<<<<<<< HEAD
        .then(({ data }) => {
          this.boxList = data.boxes;
        });
=======
        .then(r => {
          this.boxList = r.data.boxes
        })
>>>>>>> 0ed4468 (factuara electronica + reserva + caja)
    },
    listUsers() {
      axios
        .get('api/users/user-list', this.$root.config)
<<<<<<< HEAD
        .then(({ data }) => {
          this.userList = data.users;
        });
=======
        .then(r => {
          this.userList = r.data.users
        })
>>>>>>> 0ed4468 (factuara electronica + reserva + caja)
    },
    getTicket() {
      axios.post(
        'api/reports-ticket/general-sales-report',
<<<<<<< HEAD
        { data: this.List },
        this.$root.config
      );
    },
=======
        { data: this.sales.data },
        this.$root.config
      )
    }
>>>>>>> 0ed4468 (factuara electronica + reserva + caja)
  },
  mounted() {
    this.fetchOrders(1)
    this.listBoxes()
    this.listUsers()
  }
}
</script>

<style scoped>
.page-header h3 {
  margin-bottom: 1rem;
}
.card .display-4 {
  font-size: 2rem;
}
.page-search .form-row .form-group {
  margin-bottom: 1rem;
}
</style>
