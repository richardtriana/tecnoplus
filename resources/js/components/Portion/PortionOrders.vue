<template>
  <div class="page">
    <div class="page-header">
      <div class="row">
        <div class="col">
          <h3 class="page-title">Órdenes de Porciones</h3>
        </div>
        <div class="col text-right">
          <!-- Botón para abrir el modal de Crear Orden -->
          <button
            type="button"
            class="btn btn-primary"
            @click="openCreateOrder"
          >
            <i class="bi bi-plus-circle"></i> Crear Orden
          </button>
        </div>
      </div>
    </div>

    <!-- Filtros para el listado de órdenes -->
    <div class="card p-3 mb-3">
      <div class="form-row">
        <div class="form-group col-md-3">
          <label for="filterFrom">Desde</label>
          <input type="date" id="filterFrom" class="form-control" v-model="filters.from" />
        </div>
        <div class="form-group col-md-3">
          <label for="filterTo">Hasta</label>
          <input type="date" id="filterTo" class="form-control" v-model="filters.to" />
        </div>
        <div class="form-group col-md-3">
          <label for="filterMovement">Movimiento</label>
          <select id="filterMovement" class="form-control" v-model="filters.movement">
            <option value="">Todos</option>
            <option value="ingreso">Ingreso</option>
            <option value="salida">Salida</option>
          </select>
        </div>
        <div class="form-group col-md-3 d-flex align-items-end">
          <button class="btn btn-outline-primary w-100" @click="listOrders(1)">
            <i class="bi bi-search"></i> Buscar
          </button>
        </div>
      </div>
    </div>

    <!-- Listado de Órdenes -->
    <div class="page-content">
      <moon-loader
        class="m-auto"
        :loading="isLoading"
        :color="'#032F6C'"
        :size="100"
      />
      <div v-show="!isLoading">
        <section class="my-4">
          <table class="table table-sm table-bordered table-hover table-responsive-sm">
            <thead class="thead-light">
              <tr>
                <th>ID</th>
                <th>Fecha</th>
                <th>Movimiento</th>
                <th>Usuario</th>
                <th>Detalle</th>
                <th class="text-center">Opciones</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="order in orderList.data" :key="order.id">
                <td>{{ order.id }}</td>
                <td>{{ order.date }}</td>
                <td>{{ order.movement }}</td>
                <td>{{ getUserName(order.user_id) }}</td>
                <td>{{ order.detail }}</td>
                <td class="text-center">
                  <button
                    class="btn btn-outline-secondary btn-sm ml-1"
                    @click="openDetailsModal(order)"
                  >
                    Ver Detalles
                  </button>
                </td>
              </tr>
              <tr v-if="orderList.data && orderList.data.length === 0">
                <td colspan="6" class="text-center">
                  No se encontraron órdenes.
                </td>
              </tr>
            </tbody>
          </table>

          <!-- Paginación -->
          <pagination
            :align="'center'"
            :data="orderList"
            @pagination-change-page="listOrders"
          >
            <span slot="prev-nav">&lt; Anterior</span>
            <span slot="next-nav">Siguiente &gt;</span>
          </pagination>
        </section>
      </div>
    </div>

    <!-- Modal para Crear/Editar Órdenes -->
    <create-edit-portion-order
      ref="CreateEditPortionOrder"
      @list-orders="listOrders(1)"
    />

    <!-- Modal para ver Detalles de la Orden -->
    <div class="modal fade" id="detailsModal" tabindex="-1" aria-labelledby="detailsModalLabel" aria-hidden="true" data-backdrop="static">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="detailsModalLabel">
              Detalles de la Orden {{ selectedOrder.id }}
            </h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close" @click="resetSelectedOrder">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <!-- Puedes personalizar la vista de detalles -->
            <p><strong>Fecha:</strong> {{ selectedOrder.date }}</p>
            <p><strong>Movimiento:</strong> {{ selectedOrder.movement }}</p>
            <p><strong>Usuario:</strong> {{ getUserName(selectedOrder.user_id) }}</p>
            <p><strong>Detalle:</strong> {{ selectedOrder.detail }}</p>
            <h6>Detalles:</h6>
            <table class="table table-sm table-bordered">
              <thead class="thead-light">
                <tr>
                  <th>Porción</th>
                  <th>Movimiento</th>
                  <th>Cantidad</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(det, idx) in selectedOrder.details" :key="idx">
                  <td>{{ getPortionName(det.portion_id) }}</td>
                  <td>{{ det.movement }}</td>
                  <td>{{ det.quantity }}</td>
                </tr>
              </tbody>
            </table>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal" @click="resetSelectedOrder">Cerrar</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from "axios";
import CreateEditPortionOrder from "./CreateEditPortionOrder.vue";

export default {
  components: {
    CreateEditPortionOrder,
  },
  data() {
    return {
      isLoading: false,
      orderList: {},
      userList: [],
      // Filtros adicionales
      filters: {
        from: "",
        to: "",
        movement: "",
      },
      selectedOrder: {}, // Para mostrar detalles en modal
    };
  },
  created() {
    this.$root.validateToken();
    this.listOrders(1);
    this.loadUsers();
  },
  methods: {
    listOrders(page = 1) {
      this.isLoading = true;
      // Construir parámetros de filtrado (fecha y movimiento)
      const params = {
        page: page,
        from: this.filters.from,
        to: this.filters.to,
        movement: this.filters.movement,
      };
      axios
        .get(`api/portion_orders`, {
          params: params,
          headers: this.$root.config.headers,
        })
        .then((response) => {
          this.orderList = response.data.orders;
        })
        .catch((error) => console.error(error))
        .finally(() => {
          this.isLoading = false;
        });
    },
    openCreateOrder() {
      this.$refs.CreateEditPortionOrder.openCreateOrder();
    },
    openEditOrder(order) {
      this.$refs.CreateEditPortionOrder.openEditOrder(order);
    },
    // Abre el modal de detalles y carga la orden seleccionada
    openDetailsModal(order) {
      this.selectedOrder = order;
      $("#detailsModal").modal("show");
    },
    resetSelectedOrder() {
      this.selectedOrder = {};
    },
    loadUsers() {
      axios
        .get("api/users", this.$root.config)
        .then((res) => {
          this.userList = res.data.users.data || res.data.users;
        })
        .catch((err) => console.error(err));
    },
    getUserName(userId) {
      const user = this.userList.find((u) => u.id === userId);
      return user ? user.name : "Desconocido";
    },
    getPortionName(portionId) {
      // Si tienes una lista de porciones en CreateEditPortionOrder, podrías compartirla o
      // hacer otra petición. Aquí se asume que cada orden ya trae su detalle con información mínima.
      const portion = this.$refs.CreateEditPortionOrder.portionsList.find((p) => p.id === portionId);
      return portion ? portion.description : portionId;
    },
  },
};
</script>

<style scoped>
/* Puedes agregar estilos personalizados */
</style>
