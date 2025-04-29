<template>
  <div class="modal fade" id="selectSupplierModal" tabindex="-1" aria-labelledby="selectSupplierModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="selectSupplierModalLabel">Proveedores</h5>
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
              v-model="filters.supplier"
              @input="searchSupplier"
            />
            <div class="input-group-append">
              <button class="btn btn-outline-secondary" type="button" @click="searchSupplier">
                Buscar Proveedor
              </button>
            </div>
          </div>
          <table class="table table-bordered table-sm table-responsive mt-3">
            <thead>
              <tr>
                <th>#</th>
                <th>Nombre</th>
                <th>Razón Social</th>
                <th>Documento</th>
                <th>Opciones</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="supplier in supplierList" :key="supplier.id">
                <td>{{ supplier.id }}</td>
                <td>{{ supplier.first_name }} {{ supplier.second_name || '' }}</td>
                <td>{{ supplier.razon_social }}</td>
                <td>{{ supplier.document }}</td>
                <td>
                  <button class="btn btn-outline-secondary" @click="selectSupplier(supplier)" data-dismiss="modal">
                    <i class="bi bi-plus-circle"></i>
                  </button>
                </td>
              </tr>
              <tr v-if="supplierList.length === 0">
                <td colspan="5" class="text-center">No se encontraron proveedores</td>
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
  </div>
</template>

<script>
export default {
  name: "SelectSupplierModal",
  data() {
    return {
      supplierList: [],
      filters: {
        supplier: ""
      }
    };
  },
  methods: {
    loadSuppliers() {
      axios
        .get("api/suppliers", {
          params: { page: 1, search: this.filters.supplier || "" },
          ...this.$root.config
        })
        .then(response => {
          // La respuesta viene en response.data.suppliers.data
          this.supplierList = response.data.suppliers.data || [];
        })
        .catch(error => {
          console.error("Error al cargar proveedores:", error);
        });
    },
    searchSupplier() {
      this.loadSuppliers();
    },
    selectSupplier(supplier) {
      this.$emit("supplier-selected", supplier);
    },
    openModal() {
      $("#selectSupplierModal").modal("show");
    }
  },
  mounted() {
    this.loadSuppliers();
  }
};
</script>
