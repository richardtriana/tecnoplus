<template>
  <div class="w-100">
    <header class="page-header">
      <h3>Clientes</h3>
    </header>
    <moon-loader :loading="isLoading" :color="'#032F6C'" :size="100" />

    <!-- Filtros de búsqueda -->
    <div class="row mx-4 mb-2">
      <div class="col-12 col-sm-6 col-md-4">
        <input
          type="text"
          class="form-control"
          placeholder="Buscar... (Nombre, Razón Social, Documento)"
          v-model="searchTerm"
          @keyup.enter="listClients(1)" 
        />
      </div>
      <div class="col-12 col-sm-2 mt-2 mt-sm-0">
        <button
          class="btn btn-secondary"
          @click="listClients(1)"
        >
          Buscar
        </button>
      </div>
      <div class="col-12 col-sm-4 mt-2 mt-sm-0 text-sm-right">
        <button
          type="button"
          class="btn btn-primary"
          data-toggle="modal"
          data-target="#clientModal"
          @click="$refs.CreateEditClient.ResetData()"
          v-if="$root.validatePermission('client.store')"
        >
          Crear Cliente
        </button>
      </div>
    </div>

    <section class="card-body" v-if="!isLoading">
      <table class="table table-bordered table-sm">
        <thead>
          <tr>
            <th>#</th>
            <th>Nombres</th>
            <th>Apellidos</th>
            <th>Razón Social</th>
            <th>Documento</th>
            <th>Div. Verificación</th>
            <th>Dirección</th>
            <th>Teléfono</th>
            <th>Correo</th>
            <th>Municipio</th>
            <th>Tipo Doc.</th>
            <th>Tipo Org.</th>
            <th>Tributo</th>
            <th v-if="$root.validatePermission('client.active')">Estado</th>
            <th v-if="$root.validatePermission('client.update')">Opciones</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="client in clientList.data" :key="client.id">
            <td>{{ client.id }}</td>
            <td>{{ client.first_name }} {{ client.second_name || '' }}</td>
            <td>{{ client.first_lastname }} {{ client.second_lastname || '' }}</td>
            <td>{{ client.razon_social }}</td>
            <td>{{ client.document }}</td>
            <td>{{ client.div_verification || '' }}</td>
            <td>{{ client.address || '' }}</td>
            <td>{{ client.phone || '' }}</td>
            <td>{{ client.email || '' }}</td>
            <td>{{ client.municipality ? client.municipality.name : '' }}</td>
            <td>{{ client.identity_document_type ? client.identity_document_type.name : '' }}</td>
            <td>{{ client.organization_type ? client.organization_type.name : '' }}</td>
            <td>{{ client.client_tribute ? client.client_tribute.name : '' }}</td>
            <td v-if="$root.validatePermission('client.active')">
              <button
                class="btn"
                :class="client.active == 1 ? 'btn-success' : 'btn-danger'"
                @click="changeState(client.id)"
              >
                <i class="bi bi-check-circle-fill" v-if="client.active == 1"></i>
                <i class="bi bi-x-circle" v-else></i>
              </button>
            </td>
            <td v-if="$root.validatePermission('client.update')">
              <button class="btn btn-outline-success" @click="ShowData(client)">
                <i class="bi bi-pen"></i>
              </button>
            </td>
          </tr>
        </tbody>
      </table>

      <pagination
        :align="'center'"
        :data="clientList"
        @pagination-change-page="listClients"
      >
        <span slot="prev-nav">&lt; Previous</span>
        <span slot="next-nav">Next &gt;</span>
      </pagination>
    </section>

    <!-- Componente de creación/edición de cliente -->
    <create-edit-client
      ref="CreateEditClient"
      @list-clients="listClients(1)"
    />
  </div>
</template>

<script>
import CreateEditClient from "./CreateEditClient.vue"; // Ajusta la ruta si es necesario

export default {
  components: { CreateEditClient },
  data() {
    return {
      clientList: {},
      isLoading: false,
      searchTerm: "", // Campo para almacenar el texto de búsqueda
    };
  },
  created() {
    // Validamos el token antes de listar clientes (si usas validación de token)
    this.$root.validateToken();
    this.listClients(1);
  },
  methods: {
    listClients(page = 1) {
      this.isLoading = true;
      // Incluimos el parámetro "search" con this.searchTerm
      axios
        .get(`api/clients?page=${page}&search=${this.searchTerm}`, this.$root.config)
        .then((response) => {
          this.clientList = response.data.clients;
        })
        .finally(() => {
          this.isLoading = false;
        });
    },
    ShowData(client) {
      this.$refs.CreateEditClient.OpenEditClient(client);
    },
    changeState(id) {
      axios
        .post(`api/clients/${id}/activate`, null, this.$root.config)
        .then(() => {
          this.listClients(1);
        });
    },
  },
};
</script>

<style scoped>
.w-100 {
  width: 100%;
}
.page-header {
  margin-bottom: 20px;
}
.table {
  margin-top: 20px;
}
</style>
