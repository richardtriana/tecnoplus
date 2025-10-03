<template>
  <div class="page w-100">
    <!-- Encabezado -->
    <div class="page-header mb-4">
      <h3>Reporte de Productos Facturados</h3>
    </div>

    <!-- TARJETAS DE RESUMEN -->
    <div class="row mb-3 px-3">
      <div class="col-md-6">
        <div class="card mb-2">
          <div class="card-body text-center">
            <h6 class="card-title">Total Cantidad Vendida</h6>
            <p class="display-4 mb-0">{{ total_products }}</p>
          </div>
        </div>
      </div>
      <div class="col-md-6">
        <div class="card mb-2">
          <div class="card-body text-center">
            <h6 class="card-title">Valor Total</h6>
            <p class="display-4 mb-0">{{ total_value | currency }}</p>
          </div>
        </div>
      </div>
    </div>

    <!-- GRÁFICOS -->
    <div class="row mb-4 px-3">
      <div class="col-md-6">
        <div class="card mb-3">
          <div class="card-body">
            <h6>Cantidad por Categoría</h6>
            <apexchart
              type="pie"
              height="250"
              :options="categoryChartOptions"
              :series="categoryChartSeries"
            />
          </div>
        </div>
      </div>
      <div class="col-md-6">
        <div class="card mb-3">
          <div class="card-body">
            <h6>Valor por Categoría</h6>
            <apexchart
              type="pie"
              height="250"
              :options="valueChartOptions"
              :series="valueChartSeries"
            />
          </div>
        </div>
      </div>
    </div>

    <!-- FILTROS Y BOTONES -->
    <div class="page-search mx-2 my-2 border p-3">
      <div class="row g-3">
        <div class="col-md-3">
          <label>Producto</label>
          <input
            type="text"
            class="form-control"
            v-model="filter.product"
            placeholder="Nombre o parte"
          />
        </div>
        <div class="col-md-3">
          <label>Categoría</label>
          <select class="form-control" v-model="filter.category">
            <option value="">Todas</option>
            <option
              v-for="cat in categoriesList"
              :key="cat.id"
              :value="cat.id"
            >
              {{ cat.name }}
            </option>
          </select>
        </div>
        <div class="col-md-3">
          <label>Estado de Orden</label>
          <v-select
            :options="orderDeleteOptions"
            label="name"
            :reduce="o => o.id"
            v-model="filter.order_delete"
            placeholder="Todas"
          />
        </div>
        <div class="col-md-3">
          <label>Desde</label>
          <input
            type="datetime-local"
            class="form-control"
            v-model="filter.from"
          />
        </div>
        <div class="col-md-3">
          <label>Hasta</label>
          <input
            type="datetime-local"
            class="form-control"
            v-model="filter.to"
          />
        </div>
        <div class="col-md-3">
          <label>Registros / página</label>
          <input
            type="number"
            min="1"
            class="form-control"
            v-model.number="filter.nro_results"
          />
        </div>
        <div class="col-md-3 d-flex align-items-end">
          <button class="btn btn-success w-100" @click="getOrders(1)">
            Buscar <i class="bi bi-search"></i>
          </button>
        </div>
        <div class="col-md-3 d-flex align-items-end">
          <download-excel
            class="btn btn-outline-success w-100"
            :fields="json_fields"
            :data="List.data"
            name="productos-facturados.xls"
            type="xls"
          >
            <i class="bi bi-file-earmark-arrow-down-fill"></i>
            Exportar selección
          </download-excel>
        </div>
        <div class="col-md-3 d-flex align-items-end">
          <button class="btn btn-outline-success w-100" @click="getTicket">
            Ticket <i class="bi bi-card-text"></i>
          </button>
        </div>
      </div>
    </div>

    <!-- TABLA DE RESULTADOS -->
    <div class="page-content p-3">
      <table class="table table-sm table-bordered table-hover">
        <thead class="thead-dark">
          <tr>
            <th>Producto</th>
            <th>Categoría</th>
            <th>Código de barras</th>
            <th>Cantidad vendida</th>
            <th>Valor</th>
          </tr>
        </thead>
        <tbody v-if="List.data && List.data.length">
          <tr v-for="(l, i) in List.data" :key="i">
            <td>{{ l.product }}</td>
            <td>{{ l.category }}</td>
            <!-- Sistema de código de barras ORIGINAL intacto -->
            <td><span class="barcode">{{ l.barcode }}</span></td>
            <td>{{ l.quantity_of_products }}</td>
            <td>{{ l.value | currency }}</td>
          </tr>
          <tr class="font-weight-bold">
            <td colspan="3" class="text-right">Total:</td>
            <td>{{ total_products }}</td>
            <td>{{ total_value | currency }}</td>
          </tr>
        </tbody>
        <tbody v-else>
          <tr>
            <td colspan="5" class="text-center">No hay resultados</td>
          </tr>
        </tbody>
      </table>

      <pagination
        :data="List"
        :limit="filter.nro_results"
        align="center"
        @pagination-change-page="getOrders"
      >
        <span slot="prev-nav"><i class="bi bi-chevron-double-left"></i></span>
        <span slot="next-nav"><i class="bi bi-chevron-double-right"></i></span>
      </pagination>
    </div>
  </div>
</template>

<script>
import axios from 'axios'
import vSelect from 'vue-select'
import VueApexCharts from 'vue-apexcharts'
import DownloadExcel from 'vue-json-excel'
import Pagination from 'laravel-vue-pagination'

export default {
  name: 'ReportInvoicedProducts',
  components: {
    vSelect,
    apexchart: VueApexCharts,
    DownloadExcel,
    Pagination
  },
  data() {
    return {
      List: { data: [], current_page: 1 },
      filter: {
        product: '',
        category: '',
        order_delete: 'all',
        from: '',
        to: '',
        nro_results: 15
      },
      categoriesList: [],
      orderDeleteOptions: [
        { id: 'all', name: 'Todas' },
        { id: 'active', name: 'Activas' },
        { id: 'deleted', name: 'Eliminadas' }
      ],
      json_fields: {
        Producto: { field: 'product' },
        Categoría: { field: 'category' },
        'Código de barras': { field: 'barcode' },
        'Cantidad vendida': { field: 'quantity_of_products' },
        Valor: { field: 'value' }
      }
    }
  },
  computed: {
    total_products() {
      return this.List.data.reduce((sum, i) => sum + Number(i.quantity_of_products), 0)
    },
    total_value() {
      return this.List.data.reduce((sum, i) => sum + Number(i.value), 0)
    },
    categoryChartData() {
      const map = {}
      this.List.data.forEach(i => {
        const c = i.category || 'Sin categoría'
        map[c] = (map[c] || 0) + Number(i.quantity_of_products)
      })
      return Object.entries(map).map(([cat, qty]) => ({ cat, qty }))
    },
    categoryChartSeries() {
      return this.categoryChartData.map(d => d.qty)
    },
    categoryChartOptions() {
      return {
        labels: this.categoryChartData.map(d => d.cat),
        legend: { position: 'bottom' },
        chart: { toolbar: { show: false } }
      }
    },
    valueChartData() {
      const map = {}
      this.List.data.forEach(i => {
        const c = i.category || 'Sin categoría'
        map[c] = (map[c] || 0) + Number(i.value)
      })
      return Object.entries(map).map(([cat, val]) => ({ cat, val }))
    },
    valueChartSeries() {
      return this.valueChartData.map(d => d.val)
    },
    valueChartOptions() {
      return {
        labels: this.valueChartData.map(d => d.cat),
        legend: { position: 'bottom' },
        chart: { toolbar: { show: false } }
      }
    }
  },
  watch: {
    'filter.nro_results'() {
      this.getOrders(1)
    }
  },
  methods: {
    getOrders(page = 1) {
      axios
        .get('api/reports/invoiced-products', {
          params: { ...this.filter, page, per_page: this.filter.nro_results },
          headers: this.$root.config.headers
        })
        .then(({ data }) => {
          this.List = data
        })
        .catch(() => {
          this.List = { data: [] }
        })
    },
    getCategories() {
      axios
        .get('api/categories?paginate=0', this.$root.config)
        .then(({ data }) => {
          this.categoriesList = data.categories
        })
    },
    getTicket() {
      axios
        .post(
          'api/reports-ticket/invoiced-products',
          { data: this.List.data },
          this.$root.config
        )
        .catch(() => {})
    }
  },
  mounted() {
    this.getOrders(1)
    this.getCategories()
  },
  filters: {
    currency(value) {
      return new Intl.NumberFormat('es-CO', {
        style: 'currency',
        currency: 'COP'
      }).format(value)
    }
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
/* El sistema de código de barras original: */
.barcode {
  /* Aquí puedes conservar tu font-family o estilos previos, por ejemplo: */
  /* font-family: 'Libre Barcode 39', cursive; */
}
</style>
