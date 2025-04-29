<template>
  <div class="page">
    <!-- Encabezado de la página -->
    <div class="page-header mb-3">
      <div class="row">
        <div class="col">
          <h3 class="page-title">Cajas</h3>
        </div>
        <div class="col text-right">
          <button
            type="button"
            class="btn btn-outline-primary"
            data-toggle="modal"
            data-target="#boxModal"
            v-if="$root.validatePermission('box.store')"
            @click="$refs.CreateEditBox.ResetData()"
          >
            Crear Caja
          </button>
        </div>
      </div>
    </div>

    <!-- Contenido de la página -->
    <div class="page-content">
      <moon-loader
        class="m-auto"
        :loading="isLoading"
        :color="'#032F6C'"
        :size="100"
      />
      <div v-show="!isLoading">
        <section class="my-4">
          <div class="table-responsive">
            <table class="table table-sm table-bordered">
              <thead>
                <tr>
                  <th>#</th>
                  <th>Nombre</th>
                  <!-- Se omite el prefijo si ya no es necesario para el enlace -->
                  <th>Impresora POS</th>
                  <th v-if="$root.validatePermission('box.store')">
                    Asignar usuarios
                  </th>
                  <th v-if="$root.validatePermission('box.active')">Estado</th>
                  <th>Base</th>
                  <th>Historial Base</th>
                  <th>Comprobantes</th>
                  <th v-if="$root.validatePermission('box.update')">Opciones</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="box in boxList.data" :key="box.id">
                  <td>{{ box.id }}</td>
                  <td>{{ box.name }}</td>
                  <td>{{ box.printer }}</td>
                  <td v-if="$root.validatePermission('box.store')">
                    <button
                      class="btn btn-outline-primary"
                      @click="$refs.AssignUser.OpenAssignUser(box)"
                    >
                      <i class="bi bi-person-plus-fill"></i>
                    </button>
                  </td>
                  <td v-if="$root.validatePermission('box.active')">
                    <button
                      class="btn"
                      :class="box.active == 1 ? 'btn-outline-danger' : 'btn-outline-success'"
                      @click="changeState(box.id)"
                    >
                      <i v-if="box.active == 1" class="bi bi-x-circle"></i>
                      <i v-if="box.active == 0" class="bi bi-check-circle"></i>
                    </button>
                  </td>
                  <td>{{ box.base | currency }}</td>
                  <td class="text-right">
                    <button
                      class="btn btn-primary"
                      data-toggle="modal"
                      data-target="#historyBoxModal"
                      @click="showHistoryBox(box.history)"
                    >
                      <i class="bi bi-clock-history"></i>
                    </button>
                  </td>
                  <td>
                    <!-- Botón para asignar comprobantes -->
                    <button
                      class="btn btn-outline-info btn-sm"
                      @click="openAssignVouchers(box)"
                    >
                      <i class="bi bi-files"></i>
                      Asignar
                    </button>
                  </td>
                  <td v-if="$root.validatePermission('box.update')">
                    <button
                      class="btn btn-outline-success"
                      @click="ShowData(box)"
                    >
                      <i class="bi bi-pen"></i>
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </section>
      </div>
    </div>
    
    <!-- Componentes modales -->
    <create-edit-box ref="CreateEditBox" @list-boxes="listBoxes(1)" />
    <assign-user ref="AssignUser"></assign-user>
    <!-- Modal para asignar comprobantes (asumiendo que exista el componente) -->
    <assign-vouchers ref="AssignVouchers"></assign-vouchers>
    <show-history-box ref="ShowHistoryBox"></show-history-box>
  </div>
</template>

<script>
import CreateEditBox from "./CreateEditBox.vue";
import AssignUser from "./AssignUser.vue";
import ShowHistoryBox from "./ShowHistoryBox.vue";
// Se asume que tendrás un componente para asignar comprobantes
import AssignVouchers from "./AssignVouchers.vue";

export default {
  data() {
    return {
      isLoading: false,
      boxList: {}
    };
  },
  components: {
    CreateEditBox,
    AssignUser,
    ShowHistoryBox,
    AssignVouchers
  },
  created() {
    this.$root.validateToken();
    this.listBoxes(1);
  },
  methods: {
    listBoxes(page = 1) {
      this.isLoading = true;
      axios
        .get("api/boxes?page=" + page, this.$root.config)
        .then(response => {
          this.boxList = response.data.boxes;
        })
        .finally(() => (this.isLoading = false));
    },
    ShowData(box) {
      this.$refs.CreateEditBox.OpenEditBox(box);
    },
    showHistoryBox(history) {
      this.$refs.ShowHistoryBox.convertStringToJson(history);
    },
    changeState(id) {
      axios
        .post("api/boxes/" + id + "/activate", null, this.$root.config)
        .then(() => {
          this.listBoxes(1);
        });
    },
    openAssignVouchers(box) {
      // Abre el modal para asignar comprobantes a la caja
      this.$refs.AssignVouchers.openAssignVouchers(box);
    }
  }
};
</script>

<style scoped>
/* Puedes agregar estilos personalizados para mejorar el diseño */
</style>
