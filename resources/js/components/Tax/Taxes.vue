<template>
  <div class="col-12">
    <h3 class="page-header">Impuestos</h3>
    <moon-loader class="m-auto" :loading="isLoading" :color="'#032F6C'" :size="100" />

    <section v-if="!isLoading">
      <div class="row justify-content-end mx-4">
        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#taxModal"
          @click="$refs.CreateEditTax.ResetData(), (edit = false)" v-if="$root.validatePermission('tax.store')">
          Crear Impuesto
        </button>
      </div>
      <div class="card-body">
        <table class="table table-sm table-bordered table-responsive-sm">
          <thead class="thead-primary">
            <tr>
              <th scope="col">#</th>
              <th>Impuesto (Code)</th>
              <th>Descripción</th>
              <th scope="col">Porcentaje</th>
              <th v-if="$root.validatePermission('tax.active')">Estado</th>
              <th v-if="$root.validatePermission('tax.update')">Opciones</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(tax, index) in taxListing.data" :key="tax.id">
              <td scope="row">{{ index + 1 }}</td>
              <td>{{ tax.code }}</td>
              <td>{{ tax.description }}</td>
              <td>{{ tax.percentage }}</td>
              <td v-if="$root.validatePermission('tax.active')">
                <button class="btn" :class="tax.active == '1' ? 'btn-success' : 'btn-danger'" @click="changeState(tax.id)">
                  <i class="bi bi-check-circle-fill" v-if="tax.active == 1"></i>
                  <i class="bi bi-x-circle" v-if="tax.active == 0"></i>
                </button>
              </td>
              <td v-if="$root.validatePermission('tax.update')">
                <button class="btn btn-outline-success" @click="ShowData(tax), (edit = true)">
                  <i class="bi bi-pen"></i>
                </button>
              </td>
            </tr>
          </tbody>
        </table>
        <pagination :align="'center'" :data="taxListing" @pagination-change-page="listTaxes">
          <span slot="prev-nav">&lt; Previous</span>
          <span slot="next-nav">Next &gt;</span>
        </pagination>
      </div>
    </section>
    <!-- Modal para creación y edición de impuestos -->
    <div class="modal fade" id="taxModal" tabindex="-1" aria-labelledby="taxModalLabel" aria-hidden="true" data-backdrop="static">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="taxModalLabel">Impuesto</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <create-edit-tax ref="CreateEditTax" @list-taxes="listTaxes(1)" />
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-outline-secondary" @click="closeModal()">
              Cerrar
            </button>
            <button type="button" class="btn btn-outline-primary" @click="SaveTax()">
              Guardar
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import CreateEditTax from "./CreateEditTax.vue";
export default {
  components: { CreateEditTax },
  data() {
    return {
      taxListing: {},
      edit: false,
      isLoading: false,
    };
  },
  created() {
    this.$root.validateToken();
    this.listTaxes(1);
  },
  methods: {
    listTaxes(page = 1) {
      let me = this;
      me.isLoading = true;
      axios
        .get("api/taxes?page=" + page, this.$root.config)
        .then(function (response) {
          me.taxListing = response.data.taxes;
        })
        .finally(() => {
          me.isLoading = false;
        });
    },
    SaveTax() {
      if (this.edit == false) {
        this.$refs.CreateEditTax.CreateTax();
      } else {
        this.$refs.CreateEditTax.EditTax();
      }
    },
    ShowData(tax) {
      this.$refs.CreateEditTax.OpenEditTax(tax);
    },
    closeModal() {
      this.$refs.CreateEditTax.ResetData();
      this.listTaxes(1);
    },
    changeState(id) {
      axios
        .post("api/taxes/" + id + "/activate", null, this.$root.config)
        .then(() => {
          this.listTaxes(1);
        });
    },
  },
  mounted() {
    console.log("Component mounted.");
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
