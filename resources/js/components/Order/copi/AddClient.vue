<template>
  <div class="modal fade" id="addClientModal" tabindex="-1" aria-labelledby="addClientModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <div class="row w-100">
            <h5 class="modal-title col-8" id="addClientModalLabel">Clientes</h5>
            <button
              type="button"
              class="btn btn-primary col-3"
              data-toggle="modal"
              data-target="#clientModal"
              @click="$refs.CreateEditClient.ResetData()"
              v-if="$root.validatePermission('client.store')"
            >
              Crear Cliente
            </button>
          </div>
          <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <div class="input-group">
            <input
              type="text"
              class="form-control"
              placeholder="Documento | Nombre | Razón Social"
              v-model="filters.client"
              @input="searchClient"
            />
            <div class="input-group-append">
              <button class="btn btn-outline-secondary" type="button" @click="searchClient">
                Buscar Cliente
              </button>
            </div>
          </div>
          <table class="table table-bordered table-sm table-responsive mt-3">
            <thead>
              <tr>
                <th scope="col">#</th>
                <th scope="col">Nombres</th>
                <th scope="col">Apellidos</th>
                <th scope="col">Razón Social</th>
                <th scope="col">Documento</th>
                <th scope="col">Opciones</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="client in clientList" :key="client.id">
                <td>{{ client.id }}</td>
                <td>{{ client.first_name }} {{ client.second_name || '' }}</td>
                <td>{{ client.first_lastname }} {{ client.second_lastname || '' }}</td>
                <td>{{ client.razon_social }}</td>
                <td>{{ client.document }}</td>
                <td>
                  <button class="btn btn-outline-secondary" @click="selectClient(client)" data-dismiss="modal">
                    <i class="bi bi-plus-circle"></i>
                  </button>
                </td>
              </tr>
              <tr v-if="clientList.length === 0">
                <td colspan="6" class="text-center">No se encontraron clientes</td>
              </tr>
            </tbody>
          </table>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">
            Cerrar
          </button>
        </div>
      </div>
    </div>
    <create-edit-client ref="CreateEditClient" @list-clients="loadClients" />
  </div>
</template>

<script>
import CreateEditClient from "../Client/CreateEditClient.vue";

export default {
  name: "AddClient",
  components: { CreateEditClient },
  data() {
    return {
      clientList: [],
      filters: {
        client: ""
      }
    };
  },
  methods: {
    loadClients() {
      axios.get('api/clients/filter-client-list', {
          params: { client: this.filters.client || '' },
          ...this.$root.config
        })
        .then(response => {
          // Se espera un arreglo de clientes
          this.clientList = response.data;
        })
        .catch(error => {
          console.error("Error al cargar clientes:", error);
        });
    },
    searchClient() {
      // Se ejecuta la búsqueda cada vez que se escribe
      this.loadClients();
    },
    selectClient(client) {
      // Emite el cliente seleccionado al componente padre
      this.$emit('add-client', client);
    }
  },
  mounted() {
    // Al montar el componente (cuando se abre el modal) se cargan los clientes
    this.loadClients();
  }
};
</script>
