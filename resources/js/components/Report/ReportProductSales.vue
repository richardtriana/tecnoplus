<template>
  <div class="page w-100">
    <div class="page-header mb-4">
      <h3>Reporte general de productos vendidos</h3>
    </div>

    <!-- TARJETAS DE RESUMEN -->
    <div class="row mb-4">
      <div class="col-md-6">
        <div class="card text-white bg-primary">
          <div class="card-body">
            <h5>Total Cantidad Vendida</h5>
            <p class="display-4 mb-0">{{ total_products }}</p>
          </div>
        </div>
      </div>
      <div class="col-md-6">
        <div class="card text-white bg-secondary">
          <div class="card-body">
            <h5>Valor Total</h5>
            <p class="display-4 mb-0">{{ total_value | currency }}</p>
          </div>
        </div>
      </div>
    </div>

    <!-- GRÁFICOS -->
    <div class="row mb-4">
      <div class="col-md-6">
        <div class="card mb-3">
          <div class="card-body">
            <h6>Top 10 Productos por Cantidad Vendida</h6>
            <apexchart
              v-if="topChartSeries[0].data.length"
              type="bar"
              height="350"
              :options="topChartOptions"
              :series="topChartSeries"
            />
          </div>
        </div>
      </div>
      <div class="col-md-6">
        <div class="card mb-3">
          <div class="card-body">
            <h6>Distribución por Categoría</h6>
            <apexchart
              v-if="categoryChartSeries.length"
              type="pie"
              height="350"
              :options="categoryChartOptions"
              :series="categoryChartSeries"
            />
          </div>
        </div>
      </div>
    </div>

    <!-- FILTROS Y BOTONES -->
    <div class="page-search mx-2 my-2 border p-3">
      <div class="row g-3">
        <div class="col-md-3">
          <label for="filter_product">Producto</label>
          <input
            id="filter_product"
            type="text"
            class="form-control"
            v-model="filter.product"
          />
        </div>
        <div class="col-md-3">
          <label for="filter_category">Categoría</label>
          <select
            id="filter_category"
            class="form-control"
            v-model="filter.category"
          >
            <option value="">Todos</option>
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
          <label for="filter_status">Estado del Producto</label>
          <select
            id="filter_status"
            class="form-control"
            v-model="filter.product_status"
          >
            <option value="all">Todos</option>
            <option value="active">Activos</option>
            <option value="inactive">Inactivos</option>
          </select>
        </div>
        <div class="col-md-3">
          <label for="from_date">Desde</label>
          <input
            id="from_date"
            type="datetime-local"
            class="form-control"
            v-model="filter.from"
          />
        </div>
        <div class="col-md-3">
          <label for="to_date">Hasta</label>
          <input
            id="to_date"
            type="datetime-local"
            class="form-control"
            v-model="filter.to"
          />
        </div>
        <div class="col-md-3">
          <label for="nro_results"
            >Mostrar {{ filter.nro_results }} por pág.</label
          >
          <input
            id="nro_results"
            type="number"
            class="form-control"
            v-model.number="filter.nro_results"
            min="1"
          />
        </div>
        <div class="col-md-3 d-flex align-items-end">
          <button class="btn btn-success w-100" @click="getOrders(1)">
            Buscar <i class="bi bi-search"></i>
          </button>
        </div>
        <div class="col-md-3 d-flex align-items-end">
          <button class="btn btn-secondary w-100" @click="getTicket()">
            Ticket <i class="bi bi-card-text"></i>
          </button>
        </div>
        <div class="col-md-3 d-flex align-items-end">
          <download-excel
            class="btn btn-secondary w-100"
            :fields="json_fields"
            :data="List.data"
            name="product-list.xls"
            type="xls"
          >
            <i class="bi bi-file-earmark-arrow-down-fill"></i> Exportar
          </download-excel>
        </div>
      </div>
    </div>

    <!-- TABLA DE RESULTADOS -->
    <div class="page-content">
      <section class="p-3">
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
            <tr v-for="(l, index) in List.data" :key="index">
              <td>{{ l.product }}</td>
              <td>{{ l.category }}</td>
              <td><span class="barcode">{{ l.barcode }}</span></td>
              <td>{{ l.quantity_of_products }}</td>
              <td>{{ l.value | currency }}</td>
            </tr>
            <tr>
              <th colspan="3">Total:</th>
              <th>{{ total_products }}</th>
              <th>{{ total_value | currency }}</th>
            </tr>
          </tbody>
          <tbody v-else>
            <tr>
              <td colspan="5">No hay resultados</td>
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
import VueApexCharts from "vue-apexcharts";
import downloadExcel from "vue-json-excel";

export default {
  components: {
    apexchart: VueApexCharts,
    downloadExcel
  },
  data() {
    return {
      List: {},
      filter: {
        from: "",
        to: "",
        product: "",
        category: "",
        product_status: "all",
        nro_results: 15
      },
      categoriesList: [],
      json_fields: {
        Producto: { field: "product" },
        Categoría: { field: "category" },
        "Código de barras": { field: "barcode" },
        "Cantidad vendida": { field: "quantity_of_products" },
        Valor: { field: "value" }
      }
    };
  },
  computed: {
    total_products() {
      return (this.List.data || []).reduce(
        (sum, i) => sum + i.quantity_of_products,
        0
      );
    },
    total_value() {
      return (this.List.data || []).reduce((sum, i) => sum + i.value, 0);
    },
    // Top 10 productos por cantidad
    topProducts() {
      return (this.List.data || [])
        .sort((a, b) => b.quantity_of_products - a.quantity_of_products)
        .slice(0, 10);
    },
    topChartSeries() {
      return [
        {
          name: "Cantidad vendida",
          data: this.topProducts.map(i => i.quantity_of_products)
        }
      ];
    },
    topChartOptions() {
      return {
        chart: { toolbar: { show: false } },
        plotOptions: { bar: { distributed: true } },
        xaxis: {
          categories: this.topProducts.map(i => i.product),
          labels: { rotate: -45 }
        },
        dataLabels: { enabled: false },
        colors: [
          "#4CAF50",
          "#2196F3",
          "#FF9800",
          "#9C27B0",
          "#00BCD4",
          "#FF5722",
          "#8BC34A",
          "#03A9F4",
          "#FFC107",
          "#E91E63"
        ]
      };
    },
    // Distribución por categoría
    categoryData() {
      const map = {};
      (this.List.data || []).forEach(i => {
        map[i.category] = (map[i.category] || 0) + i.quantity_of_products;
      });
      return Object.entries(map).map(([cat, qty]) => ({ cat, qty }));
    },
    categoryChartSeries() {
      return this.categoryData.map(d => d.qty);
    },
    categoryChartOptions() {
      return {
        labels: this.categoryData.map(d => d.cat),
        legend: { position: "bottom" },
        chart: { toolbar: { show: false } },
        colors: [
          "#4CAF50",
          "#2196F3",
          "#FF9800",
          "#9C27B0",
          "#00BCD4",
          "#FF5722",
          "#8BC34A",
          "#03A9F4",
          "#FFC107",
          "#E91E63"
        ]
      };
    }
  },
  methods: {
    getOrders(page = 1) {
      const params = new URLSearchParams(this.filter).toString();
      axios
        .get(
          `api/reports/product-sales-report?page=${page}&${params}`,
          this.$root.config
        )
        .then(r => {
          this.List = r.data;
        });
    },
    getCategories() {
      axios
        .get("api/categories?paginate=0", this.$root.config)
        .then(r => {
          this.categoriesList = r.data.categories;
        });
    },
    getTicket() {
      axios.post(
        "api/reports-ticket/product-sales-report",
        { data: this.List.data },
        this.$root.config
      );
    }
  },
  mounted() {
    this.getOrders(1);
    this.getCategories();
  },
  filters: {
    currency(val) {
      return new Intl.NumberFormat("es-CO", {
        style: "currency",
        currency: "COP"
      }).format(val);
    }
  }
};
</script>

<style scoped>
.page-header h3 {
  margin-bottom: 1rem;
}
.card .display-4 {
  font-size: 2.5rem;
}
.barcode {
  font-family: monospace;
  font-size: 0.9rem;
  color: #333;
}
</style>
