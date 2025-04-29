<template>
  <div class="page">
    <div class="page-header">
      <div class="row">
        <div class="col">
          <h3 class="page-title">Porciones</h3>
        </div>
        <div class="col text-right">
          <!-- Botón Exportar Excel de Porciones -->
          <download-excel
            class="btn btn-outline-success mr-2"
            :data="portionList.data"
            :fields="json_fields"
            name="porciones.xls"
            type="xls"
          >
            <i class="bi bi-file-earmark-arrow-down-fill"></i> Exportar Selección
          </download-excel>
          <!-- Botón para abrir el modal de Crear Porción -->
          <button
            type="button"
            class="btn btn-outline-primary mr-2"
            data-toggle="modal"
            data-target="#portionModal"
          >
            <i class="bi bi-plus-circle"></i> Crear Porción
          </button>
          <!-- Botón para ir a Órdenes -->
          <button
            type="button"
            class="btn btn-outline-secondary"
            @click="goToOrders"
          >
            <i class="bi bi-arrow-right-square"></i> Órdenes
          </button>
        </div>
      </div>
    </div>
    
    <!-- Filtros -->
    <div class="card p-3 mb-3">
      <div class="form-row">
        <div class="col-md-3 mb-2">
          <label for="filterDescription">Descripción</label>
          <input
            type="text"
            id="filterDescription"
            class="form-control"
            placeholder="Descripción"
            v-model="filters.description"
          />
        </div>
        <div class="col-md-3 mb-2">
          <label for="filterType">Tipo</label>
          <select id="filterType" class="form-control" v-model="filters.type">
            <option value="">Todos</option>
            <option value="bodega">Bodega</option>
            <option value="alacena">Alacena</option>
          </select>
        </div>
        <div class="col-md-2 mb-2">
          <label for="filterStatus">Estado</label>
          <select id="filterStatus" class="form-control" v-model="filters.status">
            <option value="">Todos</option>
            <option value="1">Activo</option>
            <option value="0">Inactivo</option>
          </select>
        </div>
        <div class="col-md-2 mb-2">
          <label for="filterPerPage">Resultados por página</label>
          <select id="filterPerPage" class="form-control" v-model.number="filters.perPage">
            <option v-for="size in [5, 10, 20, 50, 100]" :key="size" :value="size">
              {{ size }}
            </option>
          </select>
        </div>
        <div class="col-md-2 d-flex align-items-end">
          <button class="btn btn-outline-primary w-100" @click="listPortions(1)">
            <i class="bi bi-search"></i> Buscar
          </button>
        </div>
      </div>
    </div>
    
    <!-- Listado de Porciones -->
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
                <th>#</th>
                <th>Descripción</th>
                <th>Cantidad</th>
                <th>Tipo</th>
                <th>Estado</th>
                <th>Opciones</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="portion in portionList.data" :key="portion.id">
                <td>{{ portion.id }}</td>
                <td>{{ portion.description }}</td>
                <td>{{ portion.quantity }}</td>
                <td>{{ portion.type }}</td>
                <td>
                  <button
                    :class="[
                      'btn',
                      'btn-sm',
                      portion.status == 1 ? 'btn-success' : 'btn-danger'
                    ]"
                    @click="changeStatus(portion)"
                  >
                    {{ portion.status == 1 ? 'Activo' : 'Inactivo' }}
                  </button>
                </td>
                <td>
                  <!-- Botón de Historial: abre el modal para ver el historial de esta porción -->
                  <button
                    class="btn btn-outline-info btn-sm"
                    @click="openHistory(portion)"
                  >
                    <i class="bi bi-clock-history"></i> Historial
                  </button>
                </td>
              </tr>
              <tr v-if="portionList.data && portionList.data.length === 0">
                <td colspan="6" class="text-center">
                  No se encontraron registros.
                </td>
              </tr>
            </tbody>
          </table>
          <pagination
            :align="'center'"
            :data="portionList"
            @pagination-change-page="listPortions"
          >
            <span slot="prev-nav">&lt; Anterior</span>
            <span slot="next-nav">Siguiente &gt;</span>
          </pagination>
        </section>
      </div>
    </div>
    
    <!-- Componente Modal para crear/editar porciones -->
    <create-edit-portion
      ref="CreateEditPortion"
      @list-portions="listPortions(1)"
    />

    <!-- Modal emergente para Historial -->
    <div
      class="modal fade"
      id="historyModal"
      tabindex="-1"
      role="dialog"
      aria-labelledby="historyModalLabel"
      aria-hidden="true"
      data-backdrop="static"
    >
      <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="historyModalLabel">
              Historial de Porción: {{ selectedPortion ? selectedPortion.description : '' }}
            </h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close" @click="resetHistory">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <!-- Filtros para el Historial -->
            <div class="form-row mb-3">
              <div class="col-md-4">
                <label for="historyFrom">Desde</label>
                <input type="datetime-local" id="historyFrom" class="form-control" v-model="historyFilters.from" />
              </div>
              <div class="col-md-4">
                <label for="historyTo">Hasta</label>
                <input type="datetime-local" id="historyTo" class="form-control" v-model="historyFilters.to" />
              </div>
              <div class="col-md-4 d-flex align-items-end">
                <button class="btn btn-outline-primary w-100" @click="getHistory">
                  <i class="bi bi-search"></i> Buscar
                </button>
              </div>
            </div>
            <!-- Botón para Exportar Historial -->
            <div class="mb-3">
              <download-excel
                class="btn btn-outline-success"
                :data="historyList"
                :fields="history_fields"
                name="historial_porcion.xls"
                type="xls"
              >
                <i class="bi bi-file-earmark-arrow-down-fill"></i> Exportar Historial
              </download-excel>
            </div>
            <!-- Tabla del Historial -->
            <table class="table table-sm table-bordered table-hover">
              <thead class="thead-light">
                <tr>
                  <th>ID</th>
                  <th>Movimiento</th>
                  <th>Cantidad</th>
                  <th>Fecha</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="item in historyList" :key="item.id">
                  <td>{{ item.id }}</td>
                  <td>{{ item.movement }}</td>
                  <td>{{ item.quantity }}</td>
                  <td>{{ item.created_at }}</td>
                </tr>
                <tr v-if="historyList.length === 0">
                  <td colspan="4" class="text-center">No se encontraron registros.</td>
                </tr>
              </tbody>
            </table>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal" @click="resetHistory">
              Cerrar
            </button>
          </div>
        </div>
      </div>
    </div>
    <!-- Fin Modal Historial -->
  </div>
</template>

<script>
import axios from "axios";
import CreateEditPortion from "./CreateEditPortion.vue";
import JsonExcel from "vue-json-excel";

export default {
  components: {
    CreateEditPortion,
    "download-excel": JsonExcel,
  },
  data() {
    return {
      isLoading: false,
      portionList: {},
      filters: {
        description: "",
        type: "",
        status: "",
        perPage: 20,
      },
      // Mapeo de campos para exportar porciones
      json_fields: {
        ID: "id",
        Descripción: "description",
        Cantidad: "quantity",
        Tipo: "type",
        Estado: row => (row.status == 1 ? "Activo" : "Inactivo")
      },
      // Datos para historial
      selectedPortion: null,
      historyList: [],
      historyFilters: {
        from: "",
        to: ""
      },
      history_fields: {
        ID: "id",
        Movimiento: "movement",
        Cantidad: "quantity",
        Fecha: "created_at"
      }
    };
  },
  created() {
    this.$root.validateToken();
    this.listPortions(1);
  },
  methods: {
    listPortions(page = 1) {
      this.isLoading = true;
      const params = {
        page: page,
        per_page: this.filters.perPage,
        description: this.filters.description,
        type: this.filters.type,
        status: this.filters.status,
      };
      axios
        .get("api/portions", {
          params: params,
          headers: this.$root.config.headers,
        })
        .then(response => {
          this.portionList = response.data.portions;
        })
        .catch(error => console.error(error))
        .finally(() => {
          this.isLoading = false;
        });
    },
    changeStatus(portion) {
      const newStatus = portion.status == 1 ? 0 : 1;
      axios
        .post(
          `api/portions/${portion.id}/changeStatus`,
          { status: newStatus },
          this.$root.config
        )
        .then(() => {
          portion.status = newStatus;
        })
        .catch(error => console.error(error));
    },
    goToOrders() {
      this.$router.push({ name: "PortionOrders" });
    },
    // Abre el modal de historial para la porción seleccionada
    openHistory(portion) {
      this.selectedPortion = portion;
      // Reinicia filtros y lista el historial sin filtros (o con valores por defecto)
      this.historyFilters = { from: "", to: "" };
      this.historyList = [];
      $("#historyModal").modal("show");
      this.getHistory();
    },
    // Obtiene el historial de la porción seleccionada filtrado por fechas
    getHistory() {
      if (!this.selectedPortion) return;
      const params = {};
      if (this.historyFilters.from) params.from = this.historyFilters.from;
      if (this.historyFilters.to) params.to = this.historyFilters.to;
      axios
        .get(`api/portions/${this.selectedPortion.id}/histories`, {
          params: params,
          headers: this.$root.config.headers,
        })
        .then(response => {
          // Se asume que la API devuelve un arreglo en response.data.histories
          this.historyList = response.data.histories;
        })
        .catch(error => console.error(error));
    },
    resetHistory() {
      $("#historyModal").modal("hide");
      this.selectedPortion = null;
      this.historyList = [];
      this.historyFilters = { from: "", to: "" };
    }
  }
};
</script>

<style scoped>
/* Estilos personalizados si se requieren */
</style>
