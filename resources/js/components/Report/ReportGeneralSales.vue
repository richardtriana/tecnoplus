<template>
  <div class="page w-100">
    <div class="page-header">
      <h3>Reporte general de venta</h3>
    </div>

    <!-- Card de Totales Generales -->
    <div class="card mb-3">
      <div class="card-body">
        <div class="row">
          <div class="col-md-6">
            <h5>Total Facturado</h5>
            <p class="display-4">{{ totalFacturado | currency }}</p>
          </div>
          <div class="col-md-6">
            <h5>Ganancia del Día</h5>
            <p class="display-4">{{ totalGanancia | currency }}</p>
          </div>
        </div>
      </div>
    </div>

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
            Ticket <i class="bi bi-card-text"></i>
          </button>
        </div>
      </div>
    </div>

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
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      List: [],
      boxList: [],
      userList: [],
      filter: {
        from: "",
        to: "",
        box_id: 0,
        user_id: "",
        status: "",
      },
      statusOrders: [
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
        (sum, i) => sum + (Number(i.total_iva_exc) - Number(i.total_cost_price_tax_inc)),
        0
      );
    },
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
    },
    listBoxes() {
      axios
        .get('api/boxes/box-list', this.$root.config)
        .then(({ data }) => {
          this.boxList = data.boxes;
        });
    },
    listUsers() {
      axios
        .get('api/users/user-list', this.$root.config)
        .then(({ data }) => {
          this.userList = data.users;
        });
    },
    getTicket() {
      axios.post(
        'api/reports-ticket/general-sales-report',
        { data: this.List },
        this.$root.config
      );
    },
  },
  mounted() {
    this.getOrders(1);
    this.listBoxes();
    this.listUsers();
  },
};
</script>
