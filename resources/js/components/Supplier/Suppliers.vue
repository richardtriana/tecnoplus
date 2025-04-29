<template>
  <div class="w-100">
    <header class="page-header">
      <h3>Proveedores</h3>
    </header>
    <moon-loader :loading="isLoading" :color="'#032F6C'" :size="100" />
    
    <div class="row justify-content-end mx-4">
      <button
        type="button"
        class="btn btn-primary"
        data-toggle="modal"
        data-target="#supplierModal"
        @click="$refs.CreateEditSupplier.ResetData(), (edit = false)"
        v-if="$root.validatePermission('supplier.store')"
      >
        Crear Proveedor
      </button>
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
            <th v-if="$root.validatePermission('supplier.active')">Estado</th>
            <th v-if="$root.validatePermission('supplier.update')">Opciones</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="supplier in supplierList.data" :key="supplier.id">
            <td>{{ supplier.id }}</td>
            <td>{{ supplier.first_name }} {{ supplier.second_name || '' }}</td>
            <td>{{ supplier.first_lastname }} {{ supplier.second_lastname || '' }}</td>
            <td>{{ supplier.razon_social }}</td>
            <td>{{ supplier.document }}</td>
            <td>{{ supplier.div_verification || '' }}</td>
            <td>{{ supplier.address || '' }}</td>
            <td>{{ supplier.phone || '' }}</td>
            <td>{{ supplier.email || '' }}</td>
            <td>{{ supplier.municipality ? supplier.municipality.name : '' }}</td>
            <td>{{ supplier.identity_document_type ? supplier.identity_document_type.name : '' }}</td>
            <td>{{ supplier.organization_type ? supplier.organization_type.name : '' }}</td>
            <td>{{ supplier.client_tribute ? supplier.client_tribute.name : '' }}</td>
            <td v-if="$root.validatePermission('supplier.active')">
              <button
                class="btn"
                :class="supplier.active == 1 ? 'btn-success' : 'btn-danger'"
                @click="changeState(supplier.id)"
              >
                <i class="bi bi-check-circle-fill" v-if="supplier.active == 1"></i>
                <i class="bi bi-x-circle" v-else></i>
              </button>
            </td>
            <td v-if="$root.validatePermission('supplier.update')">
              <button class="btn btn-outline-success" @click="ShowData(supplier)">
                <i class="bi bi-pen"></i>
              </button>
            </td>
          </tr>
        </tbody>
      </table>

      <pagination
        :align="'center'"
        :data="supplierList"
        @pagination-change-page="listSuppliers"
      >
        <span slot="prev-nav">&lt; Previous</span>
        <span slot="next-nav">Next &gt;</span>
      </pagination>
    </section>

    <!-- Componente de creación/edición de proveedor -->
    <create-edit-supplier
      ref="CreateEditSupplier"
      @list-suppliers="listSuppliers(1)"
    />
  </div>
</template>

<script>
import CreateEditSupplier from "./CreateEditSupplier.vue";

export default {
  components: { CreateEditSupplier },
  data() {
    return {
      supplierList: {},
      isLoading: false,
      // Puedes agregar un campo para búsqueda si lo deseas
      searchTerm: ""
    };
  },
  created() {
    this.$root.validateToken();
    this.listSuppliers(1);
  },
  methods: {
    listSuppliers(page = 1) {
      this.isLoading = true;
      // Puedes enviar searchTerm como parámetro, similar a clients
      axios
        .get(`api/suppliers?page=${page}&search=${this.searchTerm}`, this.$root.config)
        .then((response) => {
          this.supplierList = response.data.suppliers;
        })
        .finally(() => {
          this.isLoading = false;
        });
    },
    ShowData(supplier) {
      this.$refs.CreateEditSupplier.OpenEditSupplier(supplier);
    },
    changeState(id) {
      axios
        .post(`api/suppliers/${id}/activate`, null, this.$root.config)
        .then(() => {
          this.listSuppliers(1);
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
