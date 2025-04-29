<template>
  <div class="w-100">
    <div class="page-header text-center">
      <h3>Servicios</h3>
    </div>
    <!-- Loader -->
    <moon-loader
      class="m-auto"
      :loading="isLoading"
      :color="'#032F6C'"
      :size="100"
    />
    <div class="card-body">
      <section v-if="!isLoading">
        <!-- Botón Crear Servicio -->
        <div class="row justify-content-end my-4">
          <button
            type="button"
            class="btn btn-primary"
            data-toggle="modal"
            data-target="#serviceModal"
            @click="openCreateService"
          >
            Crear Servicio
          </button>
        </div>

        <!-- Tabla de Servicios -->
        <table class="table table-sm table-bordered table-responsive-sm">
          <thead class="thead-primary">
            <tr>
              <th scope="col">#</th>
              <th scope="col">Código</th>
              <th scope="col">Servicio</th>
              <th scope="col">Estado</th>
              <th scope="col">Opciones</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(service, index) in serviceList.data" :key="service.id">
              <th scope="row">{{ index + 1 }}</th>
              <td>{{ service.codigo }}</td>
              <td>{{ service.name }}</td>
              <td>
                <button
                  class="btn"
                  :class="service.active == 1 ? 'btn-success' : 'btn-danger'"
                  @click="changeState(service.id)"
                >
                  <i class="bi bi-check-circle-fill" v-if="service.active == 1"></i>
                  <i class="bi bi-x-circle" v-else></i>
                </button>
              </td>
              <td>
                <button
                  class="btn btn-outline-success"
                  @click="openEditService(service)"
                >
                  <i class="bi bi-pen"></i>
                </button>
              </td>
            </tr>
            <tr v-if="serviceList.data && serviceList.data.length === 0">
              <td colspan="5" class="text-center">No se encontraron servicios</td>
            </tr>
          </tbody>
        </table>

        <!-- Paginación -->
        <pagination
          :align="'center'"
          :data="serviceList"
          @pagination-change-page="listServices"
        >
          <span slot="prev-nav">&lt; Anterior</span>
          <span slot="next-nav">Siguiente &gt;</span>
        </pagination>
      </section>
    </div>

    <!-- Modal para creación y edición de servicios -->
    <div
      class="modal fade"
      id="serviceModal"
      tabindex="-1"
      aria-labelledby="serviceModalLabel"
      aria-hidden="true"
      data-backdrop="static"
    >
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="serviceModalLabel">Servicio</h5>
            <button
              type="button"
              class="close"
              data-dismiss="modal"
              aria-label="Close"
            >
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <!-- Aquí se incluye el componente hijo de forma estática -->
            <create-edit-service
              ref="createEditService"
              @list-services="listServices(1)"
            />
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" @click="closeModal">
              Cerrar
            </button>
            <button type="button" class="btn btn-primary" @click="SaveService">
              Guardar
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
// Importación estática del componente hijo
import CreateEditService from "./CreateEditService.vue";

export default {
  name: "Services",
  components: {
    CreateEditService
  },
  data() {
    return {
      isLoading: false,
      serviceList: {},
      edit: false
    };
  },
  created() {
    this.listServices(1);
  },
  methods: {
    listServices(page = 1) {
      this.isLoading = true;
      axios
        .get("api/services?page=" + page)
        .then((response) => {
          this.serviceList = response.data.services;
        })
        .catch((error) => {
          console.error("Error al listar servicios:", error);
        })
        .finally(() => {
          this.isLoading = false;
        });
    },
    openCreateService() {
      // Resetea el formulario en modo creación
      this.$refs.createEditService.ResetData();
      this.edit = false;
    },
    openEditService(service) {
      // Llama al método del componente hijo para abrir en modo edición
      this.$refs.createEditService.OpenEditService(service);
      this.edit = true;
    },
    SaveService() {
      if (!this.edit) {
        this.$refs.createEditService.CreateService();
      } else {
        this.$refs.createEditService.EditService();
      }
      this.listServices(1);
    },
    closeModal() {
      this.$refs.createEditService.ResetData();
      this.listServices(1);
    },
    changeState(id) {
      axios
        .post("api/services/" + id + "/activate")
        .then(() => {
          this.listServices(1);
        })
        .catch((error) => {
          console.error("Error al cambiar estado del servicio:", error);
        });
    }
  }
};
</script>

<style scoped>
/* Estilos opcionales */
</style>
