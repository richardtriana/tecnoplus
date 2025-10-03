<template>
<<<<<<< HEAD
    <div class="page w-100">
      <div class="page-header">
        <h3>Reporte de corte</h3>
      </div>
      <!-- Card de Totalizados -->
      <div class="card mb-3">
        <div class="card-body">
          <div class="row">
            <div class="col-md-6">
              <h5>Total Ventas</h5>
              <p class="display-4">{{ TotalList.total_sale | currency }}</p>
            </div>
            <div class="col-md-6">
              <h5>Total Facturas</h5>
              <p class="display-4">{{ TotalList.number_of_orders }}</p>
            </div>
          </div>
        </div>
      </div>
      <!-- Filtros y botones -->
      <div class="page-search mx-2 my-2 border p-2">
        <div class="form-row">
          <div class="form-group col-3">
            <label for="category">Estado</label>
            <v-select :options="statusOrders" label="status" :reduce="s => s.id" v-model="filter.status" />
          </div>
          <div class="form-group col-md-3">
            <label for="from_date">Desde</label>
            <input type="datetime-local" class="form-control" id="from_date" v-model="filter.from" />
          </div>
          <div class="form-group col-md-3">
            <label for="to_date">Hasta</label>
            <input type="datetime-local" class="form-control" id="to_date" v-model="filter.to" />
          </div>
          <div class="form-group col-3">
            <label for="category">Usuario</label>
            <v-select :options="userList" label="name" :reduce="u => u.id" v-model="filter.user_id" />
          </div>
          <div class="form-group col-md-3">
            <label for="box">Caja</label>
            <v-select :options="boxList" label="name" :reduce="b => b.id" v-model="filter.box_id" />
          </div>
          <div class="form-group col-3">
            <label for="nro_results">Mostrar {{ filter.nro_results }} resultados por página:</label>
            <input type="number" step="any" class="form-control" id="nro_results" v-model="filter.nro_results" />
          </div>
          <div class="col my-4 col-3">
            <button class="btn btn-success btn-block" @click="getOrders(1)">
              Buscar <i class="bi bi-search"></i>
            </button>
          </div>
          <div class="col-md-3 my-4">
            <button class="btn btn-outline-success btn-block" @click="getTicket()">
              Ticket <i class="bi bi-card-text"></i>
            </button>
          </div>
          <div class="col my-4 col-3 offset-md-9">
            <download-excel
              class="btn btn-outline-success mr-2 btn-block"
              :fields="json_fields"
              :data="List.data"
              name="closing-report.xls"
              type="xls"
            >
              <i class="bi bi-file-earmark-arrow-down-fill"></i> Exportar selección
            </download-excel>
          </div>
        </div>
      </div>
      <!-- Tabla de resultados -->
      <div class="page-content">
        <section class="p-3">
          <table class="table table-sm table-bordered table-hover">
            <thead class="thead-dark">
              <tr>
                <th>Fecha venta</th>
                <th>Número total de facturas</th>
                <th>Nro. facturas crédito</th>
                <th>Valor créditos</th>
                <th>Nro. cotizaciones</th>
                <th>Nro. facturas de contado</th>
                <th>Valor total de venta</th>
                <th>Costo total de venta</th>
                <th>Ganancia bruta</th>
                <th>Valor de pago</th>
                <th>Forma de pago</th>
                <th>Método de pago</th>
              </tr>
            </thead>
            <tbody v-if="List.data.length">
              <tr v-for="(l, i) in List.data" :key="i">
                <td>{{ l.date_paid }}</td>
                <td>{{ l.number_of_orders }}</td>
                <td>{{ l.credit }}</td>
                <td>{{ l.paid_credit | currency }}</td>
                <td>{{ l.quoted }}</td>
                <td>{{ l.registered }}</td>
                <td>{{ l.total_sale | currency }}</td>
                <td>{{ l.total_sale_iva_exc | currency }}</td>
                <td>{{ (l.total_sale - l.total_sale_iva_exc) | currency }}</td>
                <td>{{ l.payment_value | currency }}</td>
                <td>{{ l.payment_form }}</td>
                <td>{{ l.payment_method }}</td>
              </tr>
            </tbody>
            <tbody v-else>
              <tr>
                <td colspan="12">No hay resultados</td>
              </tr>
            </tbody>
          </table>
          <pagination
            :align="'center'"
            :data="List"
            :limit="8"
            @pagination-change-page="getOrders"
          >
            <span slot="prev-nav"><i class="bi bi-chevron-double-left"></i></span>
            <span slot="next-nav"><i class="bi bi-chevron-double-right"></i></span>
          </pagination>
        </section>
      </div>
    </div>
  </template>
  
  <script>
  export default {
    data() {
      return {
        List: {},
        TotalList: {},
        userList: [],
        boxList: [],
        filter: {
          from: "",
          to: "",
          status: "",
          user_id: "",
          box_id: "",
          nro_results: 15,
        },
        statusOrders: [
          { id: 2, status: "Facturado" },
          { id: 3, status: "Cotizado" },
          { id: 5, status: "Crédito" },
        ],
        json_fields: {
          'Fecha venta': { field: 'date_paid', callback: v => v },
          'Total facturas': { field: 'number_of_orders', callback: v => v },
          'Nro. crédito': { field: 'credit', callback: v => v },
          'Valor créditos': { field: 'paid_credit', callback: v => this.$options.filters.currency(v, 'export') },
          'Nro. cotizaciones': { field: 'quoted', callback: v => v },
          'Nro. contado': { field: 'registered', callback: v => v },
          'Valor venta': { field: 'total_sale', callback: v => this.$options.filters.currency(v, 'export') },
          'Costo venta': { field: 'total_sale_iva_exc', callback: v => this.$options.filters.currency(v, 'export') },
          'Ganancia': { callback: row => this.$options.filters.currency(row.total_sale - row.total_sale_iva_exc, 'export') },
          'Valor pago': { field: 'payment_value', callback: v => this.$options.filters.currency(v, 'export') },
          'Forma pago': { field: 'payment_form', callback: v => v },
          'Método pago': { field: 'payment_method', callback: v => v },
        }
      };
    },
    methods: {
      getOrders(page = 1) {
        const params = {
          page,
          from: this.filter.from,
          to: this.filter.to,
          status: this.filter.status,
          user_id: this.filter.user_id,
          box_id: this.filter.box_id,
          nro_results: this.filter.nro_results,
        };
        axios
          .get('api/reports/closing', { params, headers: this.$root.config.headers })
          .then(res => {
            this.List = res.data.orders;
            this.TotalList = res.data.totals;
          });
      },
      listBoxes() {
        axios
          .get('api/boxes/box-list', this.$root.config)
          .then(res => {
            this.boxList = res.data.boxes;
          });
      },
      listUsers() {
        axios
          .get('api/users/user-list', this.$root.config)
          .then(res => {
            this.userList = res.data.users;
          });
      },
      getTicket() {
        axios.post('api/reports-ticket/closing', { data: this.List.data }, this.$root.config);
      }
    },
    mounted() {
      this.getOrders(1);
      this.listBoxes();
      this.listUsers();
    }
  };
  </script>
=======
  <div class="page w-100">
    <div class="page-header mb-4">
      <h3>Reporte de corte</h3>
    </div>

    <!-- TARJETAS DE RESUMEN -->
    <div class="row mb-4">
      <div class="col-md-3">
        <div class="card text-white bg-primary">
          <div class="card-body">
            <h6 class="card-title">Total Ventas</h6>
            <p class="display-6 mb-0">{{ TotalList.total_sale | currency }}</p>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card text-white bg-secondary">
          <div class="card-body">
            <h6 class="card-title">Total Facturas</h6>
            <p class="display-6 mb-0">{{ totalInvoices }}</p>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card text-white bg-info">
          <div class="card-body">
            <h6 class="card-title">Facturas a Crédito</h6>
            <p class="display-6 mb-0">{{ totalCreditInvoices }}</p>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card text-white bg-success">
          <div class="card-body">
            <h6 class="card-title">Ventas a Crédito</h6>
            <p class="display-6 mb-0">{{ totalCreditSales | currency }}</p>
          </div>
        </div>
      </div>
    </div>

    <!-- GRÁFICOS -->
    <div class="row mb-4">
      <div class="col-md-4">
        <div class="card mb-3">
          <div class="card-body">
            <h6>Distribución por Método de Pago</h6>
            <apexchart
              type="pie"
              height="250"
              :options="chartOptions"
              :series="chartSeries"
            />
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card mb-3">
          <div class="card-body">
            <h6>Distribución por Forma de Pago</h6>
            <apexchart
              type="pie"
              height="250"
              :options="formChartOptions"
              :series="formChartSeries"
            />
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card mb-3">
          <div class="card-body">
            <h6>Ventas en el tiempo</h6>
            <apexchart
              type="line"
              height="250"
              :options="timeChartOptions"
              :series="timeChartSeries"
            />
          </div>
        </div>
      </div>
    </div>

    <!-- FILTROS -->
    <div class="page-search mx-2 my-2 border p-3">
      <div class="row g-3">
        <div class="col-md-2">
          <label>Estado</label>
          <v-select
            :options="statusOrders"
            label="status"
            :reduce="s => s.id"
            v-model="filter.status"
          />
        </div>
        <div class="col-md-2">
          <label>Desde</label>
          <input
            type="datetime-local"
            class="form-control"
            v-model="filter.from"
          />
        </div>
        <div class="col-md-2">
          <label>Hasta</label>
          <input
            type="datetime-local"
            class="form-control"
            v-model="filter.to"
          />
        </div>
        <div class="col-md-2">
          <label>Usuario</label>
          <v-select
            :options="userList"
            label="name"
            :reduce="u => u.id"
            v-model="filter.user_id"
          />
        </div>
        <div class="col-md-2">
          <label>Caja</label>
          <v-select
            :options="boxList"
            label="name"
            :reduce="b => b.id"
            v-model="filter.box_id"
          />
        </div>
        <div class="col-md-1">
          <label>Resultados / página</label>
          <input
            type="number"
            class="form-control"
            v-model.number="filter.nro_results"
            min="1"
          />
        </div>
        <div class="col-md-1 d-flex align-items-end">
          <button class="btn btn-success w-100" @click="getOrders(1)">
            Buscar
          </button>
        </div>
        <!-- NUEVO: Botón Ticket -->
        <div class="col-md-2 d-flex align-items-end">
          <button class="btn btn-outline-primary w-100" @click="getTicket">
            Ticket <i class="bi bi-card-text"></i>
          </button>
        </div>
      </div>
    </div>

    <!-- TABLA DE RESULTADOS -->
    <div class="page-content">
      <section class="p-3">
        <table class="table table-sm table-bordered table-hover">
          <thead class="thead-dark">
            <tr>
              <th>Fecha venta</th>
              <th>Total Fac.</th>
              <th>Fac. Crédito</th>
              <th>Ventas Crédito</th>
              <th>Fac. Contado</th>
              <th>Valor Venta</th>
              <th>Valor Costos</th>
              <th>Ganancia</th>
              <th>Pago Recibido</th>
              <th>Forma Pago</th>
              <th>Método Pago</th>
            </tr>
          </thead>
          <tbody v-if="List.data.length">
            <tr v-for="(l, i) in List.data" :key="i">
              <td>{{ l.date_paid }}</td>
              <td>{{ l.number_of_orders }}</td>
              <td>{{ l.credit }}</td>
              <td>{{ l.paid_credit | currency }}</td>
              <td>{{ l.registered }}</td>
              <td>{{ l.total_sale | currency }}</td>
              <td>{{ l.total_sale_iva_exc | currency }}</td>
              <td>{{ (l.total_sale - l.total_sale_iva_exc) | currency }}</td>
              <td>{{ l.payment_value | currency }}</td>
              <td>{{ l.payment_form }}</td>
              <td>{{ l.payment_method || 'N/A' }}</td>
            </tr>
          </tbody>
          <tbody v-else>
            <tr>
              <td colspan="11">No hay resultados</td>
            </tr>
          </tbody>
        </table>
        <pagination
          :align="'center'"
          :data="List"
          :limit="8"
          @pagination-change-page="getOrders"
        >
          <span slot="prev-nav"><i class="bi bi-chevron-double-left"></i></span>
          <span slot="next-nav"><i class="bi bi-chevron-double-right"></i></span>
        </pagination>
      </section>
    </div>
  </div>
</template>

<script>
import axios from "axios";
import vSelect from "vue-select";
import VueApexCharts from "vue-apexcharts";

export default {
  components: {
    vSelect,
    apexchart: VueApexCharts
  },
  data() {
    return {
      List: {},
      TotalList: {},
      userList: [],
      boxList: [],
      filter: {
        from: "",
        to: "",
        status: "",
        user_id: "",
        box_id: "",
        nro_results: 15
      },
      statusOrders: [
        { id: 2, status: "Facturado" },
        { id: 3, status: "Cotizado" },
        { id: 5, status: "Crédito" }
      ]
    };
  },
  computed: {
    // Total de facturas
    totalInvoices() {
      return (this.List.data || []).reduce(
        (sum, l) => sum + l.number_of_orders,
        0
      );
    },
    // Facturas a crédito
    totalCreditInvoices() {
      return (this.List.data || []).reduce((sum, l) => sum + l.credit, 0);
    },
    // Ventas a crédito (valor)
    totalCreditSales() {
      return (this.List.data || []).reduce(
        (sum, l) => sum + (l.paid_credit || 0),
        0
      );
    },
    // Distribución por método
    paymentChartData() {
      const map = {};
      (this.List.data || []).forEach(l => {
        const m = l.payment_method || "Desconocido";
        map[m] = (map[m] || 0) + l.payment_value;
      });
      return Object.entries(map).map(([method, total]) => ({ method, total }));
    },
    chartSeries() {
      return this.paymentChartData.map(d => d.total);
    },
    chartOptions() {
      return {
        labels: this.paymentChartData.map(d => d.method),
        legend: { position: "bottom" },
        chart: { toolbar: { show: false } }
      };
    },
    // Distribución por forma de pago
    formChartData() {
      const map = {};
      (this.List.data || []).forEach(l => {
        const f = l.payment_form || "Desconocido";
        map[f] = (map[f] || 0) + l.payment_value;
      });
      return Object.entries(map).map(([form, total]) => ({ form, total }));
    },
    formChartSeries() {
      return this.formChartData.map(d => d.total);
    },
    formChartOptions() {
      return {
        labels: this.formChartData.map(d => d.form),
        legend: { position: "bottom" },
        chart: { toolbar: { show: false } }
      };
    },
    // Ventas en el tiempo
    timeCategories() {
      return (this.List.data || []).map(l => l.date_paid);
    },
    timeSeries() {
      return (this.List.data || []).map(l => l.total_sale);
    },
    timeChartSeries() {
      return [{ name: "Ventas", data: this.timeSeries }];
    },
    timeChartOptions() {
      return {
        xaxis: { categories: this.timeCategories, type: "datetime" },
        chart: { toolbar: { show: false } },
        stroke: { curve: "smooth" },
        dataLabels: { enabled: false }
      };
    }
  },
  methods: {
    getOrders(page = 1) {
      const params = {
        page,
        from: this.filter.from,
        to: this.filter.to,
        status: this.filter.status,
        user_id: this.filter.user_id,
        box_id: this.filter.box_id,
        nro_results: this.filter.nro_results
      };
      axios
        .get("api/reports/closing", {
          params,
          headers: this.$root.config.headers
        })
        .then(res => {
          this.List = res.data.orders;
          this.TotalList = res.data.totals;
        });
    },
    listBoxes() {
      axios.get("api/boxes/box-list", this.$root.config).then(r => {
        this.boxList = r.data.boxes;
      });
    },
    listUsers() {
      axios.get("api/users/user-list", this.$root.config).then(r => {
        this.userList = r.data.users;
      });
    },
    getTicket() {
      axios.post(
        "api/reports-ticket/closing",
        { data: this.List.data },
        this.$root.config
      );
    }
  },
  mounted() {
    this.getOrders(1);
    this.listBoxes();
    this.listUsers();
  }
};
</script>

<style scoped>
.page-header h3 {
  margin-bottom: 1rem;
}
.card .display-6 {
  font-size: 2rem;
}
.page-search .form-row .form-group {
  margin-bottom: 1rem;
}
</style>
>>>>>>> 0ed4468 (factuara electronica + reserva + caja)
