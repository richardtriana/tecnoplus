<template>
  <div class="modal fade" id="addServiceModal" tabindex="-1" aria-labelledby="addServiceModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="addServiceModalLabel">Servicios</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <div class="input-group">
            <input
              type="text"
              class="form-control"
              placeholder="Nombre del servicio"
              v-model="filters.service"
              @keyup.enter="searchService"
            />
            <div class="input-group-append">
              <button class="btn btn-outline-secondary" type="button" @click="searchService">
                Buscar Servicio
              </button>
            </div>
          </div>
          <section class="table-responsive mt-3">
            <table class="table table-sm table-bordered">
              <thead class="thead-primary">
                <tr>
                  <th>#</th>
                  <th>Código</th>
                  <th>Servicio</th>
                  <th>Estado</th>
                  <th>Añadir</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="service in serviceList" :key="service.id">
                  <td>{{ service.id }}</td>
                  <td>{{ service.codigo }}</td>
                  <td>{{ service.name }}</td>
                  <td>
                    <span v-if="service.active">Activo</span>
                    <span v-else>Inactivo</span>
                  </td>
                  <td>
                    <button class="btn btn-success" @click="$emit('add-service', service)">
                      <i class="bi bi-plus-circle"></i>
                    </button>
                  </td>
                </tr>
                <tr v-if="serviceList.length === 0">
                  <td colspan="5" class="text-center">No se encontraron servicios</td>
                </tr>
              </tbody>
            </table>
          </section>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">
            Cerrar
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: "AddService",
  data() {
    return {
      filters: {
        service: ""
      },
      serviceList: [] // Lista de servicios obtenida de la API
    };
  },
  created() {
    this.loadServices();
  },
  methods: {
    loadServices() {
      axios
        .get("api/services", {
          params: { page: 1, search: this.filters.service || "" },
          ...this.$root.config
        })
        .then(response => {
          // Se asume que la respuesta tiene la estructura: response.data.services.data
          this.serviceList = response.data.services.data || [];
        })
        .catch(error => {
          console.error("Error al listar servicios:", error);
        });
    },
    searchService() {
      // Si el campo está vacío, se vuelve a cargar la lista completa
      this.loadServices();
    }
  }
};
</script>
