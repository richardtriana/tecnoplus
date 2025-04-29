<template>
  <div>
    <!-- Modal para Crear/Editar Órdenes de Porciones -->
    <div
      class="modal fade"
      id="orderModal"
      tabindex="-1"
      aria-labelledby="orderModalLabel"
      aria-hidden="true"
      data-backdrop="static"
    >
      <div class="modal-dialog modal-xl">
        <div class="modal-content">
          <!-- Encabezado del Modal -->
          <div class="modal-header">
            <h5 class="modal-title" id="orderModalLabel">
              {{ formOrder.id ? "Editar Orden" : "Crear Orden" }}
            </h5>
            <button
              type="button"
              class="close"
              data-dismiss="modal"
              aria-label="Close"
              @click="resetForm"
            >
              <span aria-hidden="true">&times;</span>
            </button>
          </div>

          <!-- Cuerpo del Modal -->
          <div class="modal-body">
            <form>
              <!-- Campo hidden para enviar user_id -->
              <input type="hidden" v-model="formOrder.user_id" />

              <div class="form-row">
                <!-- Detalle de la Orden -->
                <div class="form-group col-md-6">
                  <label for="detailInput">Detalle</label>
                  <textarea
                    class="form-control"
                    id="detailInput"
                    placeholder="Detalle de la orden"
                    v-model="formOrder.detail"
                  ></textarea>
                  <small v-if="formErrors.detail" class="text-danger">
                    {{ formErrors.detail }}
                  </small>
                </div>

                <!-- Usuario: mostramos el nombre, enviamos user_id -->
                <div class="form-group col-md-3">
                  <label for="userName">Usuario</label>
                  <input
                    type="text"
                    class="form-control"
                    id="userName"
                    :value="currentUser.name"
                    disabled
                  />
                </div>

                <!-- Fecha -->
                <div class="form-group col-md-3">
                  <label for="dateInput">Fecha</label>
                  <input
                    type="datetime-local"
                    class="form-control"
                    id="dateInput"
                    v-model="formOrder.date"
                  />
                  <small v-if="formErrors.date" class="text-danger">
                    {{ formErrors.date }}
                  </small>
                </div>
              </div>

              <div class="form-row">
                <!-- Movimiento de la Orden -->
                <div class="form-group col-md-3">
                  <label for="movementInput">Movimiento</label>
                  <select
                    class="form-control"
                    id="movementInput"
                    v-model="formOrder.movement"
                    @change="syncDetailsMovement"
                  >
                    <option value="">-- Seleccione --</option>
                    <option value="ingreso">Ingreso</option>
                    <option value="salida">Salida</option>
                  </select>
                  <small v-if="formErrors.movement" class="text-danger">
                    {{ formErrors.movement }}
                  </small>
                </div>
              </div>

              <hr />
              <h5>Detalles de la Orden</h5>

              <!-- Input para buscar porciones -->
              <div class="form-row mb-2">
                <div class="col-md-6">
                  <label for="searchPortion">Buscar Porción</label>
                  <input
                    type="text"
                    id="searchPortion"
                    class="form-control"
                    v-model="portionSearch"
                    @input="searchPortions"
                    placeholder="Ingresa el nombre de la porción"
                  />
                  <!-- Dropdown con resultados filtrados -->
                  <div
                    class="dropdown-menu show"
                    v-if="portionSearch && filteredPortions.length"
                    style="max-height: 200px; overflow-y: auto;"
                  >
                    <button
                      class="dropdown-item"
                      v-for="p in filteredPortions"
                      :key="p.id"
                      @click="addPortionToDetails(p)"
                    >
                      {{ p.description }}
                    </button>
                  </div>
                </div>
              </div>

              <!-- Tabla de Detalles -->
              <table class="table table-bordered table-sm">
                <thead class="thead-light">
                  <tr>
                    <th>Porción</th>
                    <th>Movimiento</th>
                    <th>Cantidad</th>
                    <th class="text-center">Acción</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="(detail, index) in formOrderDetails" :key="index">
                    <td>
                      <select class="form-control" v-model="detail.portion_id" disabled>
                        <option value="">-- Seleccione --</option>
                        <option
                          v-for="portion in portionsList"
                          :key="portion.id"
                          :value="portion.id"
                        >
                          {{ portion.description }}
                        </option>
                      </select>
                      <small
                        v-if="formErrors[`details.${index}.portion_id`]"
                        class="text-danger"
                      >
                        {{ formErrors[`details.${index}.portion_id`] }}
                      </small>
                    </td>
                    <td>
                      <input
                        type="text"
                        class="form-control"
                        :value="detail.movement"
                        disabled
                      />
                    </td>
                    <td>
                      <input
                        type="number"
                        class="form-control"
                        v-model.number="detail.quantity"
                        min="1"
                      />
                      <small
                        v-if="formErrors[`details.${index}.quantity`]"
                        class="text-danger"
                      >
                        {{ formErrors[`details.${index}.quantity`] }}
                      </small>
                    </td>
                    <td class="text-center">
                      <button
                        type="button"
                        class="btn btn-danger btn-sm"
                        @click="removeDetail(index)"
                      >
                        Eliminar
                      </button>
                    </td>
                  </tr>
                  <tr v-if="formOrderDetails.length === 0">
                    <td colspan="4" class="text-center">
                      No hay detalles en esta orden.
                    </td>
                  </tr>
                </tbody>
              </table>
            </form>
          </div>

          <!-- Pie del Modal -->
          <div class="modal-footer">
            <button
              type="button"
              class="btn btn-secondary"
              data-dismiss="modal"
              @click="resetForm"
            >
              Cerrar
            </button>
            <button
              type="button"
              class="btn btn-primary"
              @click="formOrder.id ? updateOrder() : createOrder()"
            >
              Guardar
            </button>
          </div>
        </div>
      </div>
    </div>
    <!-- Fin del Modal -->
  </div>
</template>

<script>
export default {
  data() {
    return {
      formOrder: {
        id: null,
        detail: "",
        user_id: null, // Se enviará en el payload
        date: "",
        movement: "",
      },
      formOrderDetails: [],
      formErrors: {},
      currentUser: {},
      portionsList: [],
      portionSearch: "",
    };
  },
  computed: {
    filteredPortions() {
      if (!this.portionSearch) return [];
      const term = this.portionSearch.toLowerCase();
      return this.portionsList.filter((p) =>
        p.description.toLowerCase().includes(term)
      );
    },
  },
  created() {
    // Forzar asignación del usuario desde localStorage
    this.currentUser = localStorage.getItem("user")
      ? JSON.parse(localStorage.getItem("user"))
      : {};
    console.log("Current user:", this.currentUser);
    // Importante: usamos "sub" ya que el objeto usuario no tiene "id"
    this.formOrder.user_id = this.currentUser.sub || null;
    console.log("Assigned user_id:", this.formOrder.user_id);
    // Asignar la fecha actual en formato datetime-local
    this.formOrder.date = new Date().toISOString().slice(0, 16);
    // Cargar la lista de porciones
    this.loadPortions();
  },
  methods: {
    loadPortions() {
      axios
        .get("api/portions?per_page=9999", this.$root.config)
        .then((response) => {
          const data = response.data.portions;
          this.portionsList = data.data || data;
        })
        .catch((error) => console.error(error));
    },
    searchPortions() {
      // La búsqueda se filtra localmente en el computed filteredPortions
    },
    addPortionToDetails(portion) {
      // Al seleccionar una porción, se limpia el input y se agrega el detalle
      this.portionSearch = "";
      this.formOrderDetails.push({
        portion_id: portion.id,
        movement: this.formOrder.movement || "ingreso",
        quantity: 1,
      });
    },
    syncDetailsMovement() {
      // Sincroniza el movimiento de la orden con cada detalle
      this.formOrderDetails.forEach((d) => {
        d.movement = this.formOrder.movement;
      });
    },
    removeDetail(index) {
      this.formOrderDetails.splice(index, 1);
    },
    createOrder() {
      this.clearErrors();
      const payload = {
        ...this.formOrder,
        details: this.formOrderDetails,
      };
      axios
        .post("api/portion_orders", payload, this.$root.config)
        .then(() => {
          $("#orderModal").modal("hide");
          this.resetForm();
          this.$emit("list-orders");
        })
        .catch((error) => {
          if (
            error.response &&
            error.response.data &&
            error.response.data.errors
          ) {
            this.formErrors = error.response.data.errors;
          }
        });
    },
    updateOrder() {
      this.clearErrors();
      const payload = {
        ...this.formOrder,
        details: this.formOrderDetails,
      };
      axios
        .put(`api/portion_orders/${this.formOrder.id}`, payload, this.$root.config)
        .then(() => {
          $("#orderModal").modal("hide");
          this.resetForm();
          this.$emit("list-orders");
        })
        .catch((error) => {
          if (
            error.response &&
            error.response.data &&
            error.response.data.errors
          ) {
            this.formErrors = error.response.data.errors;
          }
        });
    },
    openCreateOrder() {
      this.resetForm();
      $("#orderModal").modal("show");
    },
    openEditOrder(order) {
      this.resetForm();
      axios
        .get(`api/portion_orders/${order.id}`, this.$root.config)
        .then((response) => {
          const data = response.data.order;
          this.formOrder = {
            id: data.id,
            detail: data.detail,
            user_id: data.user_id,
            date: data.date,
            movement: data.movement,
          };
          this.formOrderDetails = data.details.map((d) => ({
            portion_id: d.portion_id,
            movement: d.movement,
            quantity: d.quantity,
          }));
          $("#orderModal").modal("show");
        })
        .catch((error) => console.error(error));
    },
    resetForm() {
      $("#orderModal").modal("hide");
      // Forzar reasignación del usuario desde localStorage
      this.currentUser = localStorage.getItem("user")
        ? JSON.parse(localStorage.getItem("user"))
        : {};
      this.formOrder = {
        id: null,
        detail: "",
        user_id: this.currentUser.sub || null, // Usamos "sub" porque ese es el identificador
        date: new Date().toISOString().slice(0, 16),
        movement: "",
      };
      this.formOrderDetails = [];
      this.formErrors = {};
      this.portionSearch = "";
      console.log("Form reset, user_id:", this.formOrder.user_id);
    },
    clearErrors() {
      this.formErrors = {};
    },
  },
};
</script>

<style scoped>
.dropdown-menu.show {
  display: block;
  position: absolute;
  background-color: #fff;
  margin-top: 0;
  width: 100%;
  z-index: 999;
  border: 1px solid #ccc;
}
</style>
