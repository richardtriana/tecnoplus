<template>
  <div class="page w-100">
    <div class="page-header">
      <h3>Reporte general de venta</h3>
    </div>
    <div class="page-search mx-2 my-2 border p-2">
      <div class="form-row">
        <div class="form-group col-md-3">
          <label for="box">Caja</label>
          <v-select :options="boxList" label="name" :reduce="(box) => box.id" v-model="filter.box_id" />
        </div>
        <div class="form-group col-3">
          <label for="category">Estado</label>
          <v-select :options="statusOrders" label="status" :reduce="(status) => status.id" v-model="filter.status" />
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
          <v-select :options="userList" label="name" :reduce="(user) => user.id" v-model="filter.user_id" />
        </div>
        <div class="col my-4 col-4">
          <button class="btn btn-success btn-block" @click="getOrders(1)">
            Buscar <i class="bi bi-search"></i>
          </button>
        </div>
        <div class="col-md-3  my-4">
          <button class="btn btn-outline-success  btn-block" @click="getTicket()">
            Ticket <i class="bi bi-card-text"></i>
          </button>
      </div>
      </div>
    </div>
    <div class="page-content">
      <section class="p-3">
        <table class="table table-sm table-bordered table-hover">
          <thead class="thead-dark">
            <tr>
              <th>Número total de facturas</th>
              <th>Nro. facturas registradas</th>
              <th>Nro. facturas suspendidas</th>
              <th>Nro. facturas cotizadas</th>
              <th>Nro. Creditos</th>
              <th>Total precio de costo</th>
              <th>Total IVA excluido</th>
              <th>Total IVA incluido</th>
              <th>Total Descuento</th>
              <th>Total facturado</th>
              <th>Ganancia del día</th>
            </tr>
          </thead>
          <tbody v-if="List.length > 0">
            <tr v-for="(l, index) in List" :key="index">
              <td>
                {{  l.number_of_orders  }}
              </td>
              <td>
                {{  l.registered  }}
              </td>
              <td>
                {{  l.suspended  }}
              </td>
              <td>
                {{  l.quoted  }}
              </td>
              <td>
                {{  l.credit  }}
              </td>
              <td>
                {{  l.total_cost_price_tax_inc | currency  }}
              </td>
              <td>
                {{  l.total_iva_exc | currency  }}
              </td>
              <td>
                {{  l.total_iva_inc | currency  }}
              </td>
              <td>
                {{  l.total_discount | currency  }}
              </td>
              <td>
                {{  l.total_paid | currency  }}
              </td>
              <td>
                {{  (l.total_iva_exc - l.total_cost_price_tax_inc) | currency  }}
              </td>
            </tr>
          </tbody>
          <tbody v-else>
            <tr>
              <td colspan="3">No hay resultados</td>
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
      List: {},
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
        { id: 4, status: "Facturar e imprimir" },
        { id: 5, status: "Credito" },
        { id: 6, status: "Credito e imprimir" }
      ],
    };
  },
  methods: {
    getOrders(page = 1) {
      let me = this;

      let data = {
        page: page,
        from: me.filter.from,
        to: me.filter.to,
        box: me.filter.box_id,
        user_id: me.filter.user_id,
        status: me.filter.status
      };

      axios
        .get(`api/reports/general-sales-report`, {
          params: data,
          headers: this.$root.config.headers,
        })
        .then(function (response) {
          me.List = response.data;
        })
        .catch((me.List = {}));
    },
    listBoxes() {
      let me = this;
      axios
        .get(`api/boxes/box-list`, this.$root.config)
        .then(function (response) {
          me.boxList = response.data.boxes;
        });
    },
    listUsers() {
      let me = this;
      axios
        .get(`api/users/user-list`, this.$root.config)
        .then(function (response) {
          me.userList = response.data.users;
        });
    },
    getTicket() {
            axios
                .post(
                    `api/reports-ticket/general-sales-report`,
                    { data: this.List },
                    this.$root.config
                )
                .then(function (response) {
                    // me.List = response.data;
                });
        }
  },
  mounted() {
    this.getOrders(1);
    this.listBoxes();
    this.listUsers();
  },
};
</script>
