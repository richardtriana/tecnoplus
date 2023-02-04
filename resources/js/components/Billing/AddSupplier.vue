<template>
  <div class="modal fade" id="addSupplierModal" tabindex="-1" aria-labelledby="addSupplierModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <div class="row w-100">
            <h5 class="modal-title col-8" id="addSupplierModalLabel">Proveedores</h5>
            <div class="col-3">
              <button type="reset" class="btn btn-primary" data-toggle="modal" data-target="#supplierModal"
                @click="edit = false" v-if="$root.validatePermission('supplier.store')">
                Crear Proveedor
              </button>
            </div>
          </div>
          <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <div class="input-group">
            <input type="text" class="form-control" placeholder="Documento | Nombre de proveedor"
              aria-label=" with two button addons" aria-describedby="button-addon4" v-model="filters.supplier"
              @keyup="searchSupplier()" />
            <div class="input-group-append" id="button-addon4">
              <button class="btn btn-outline-secondary" type="button" @click="searchSupplier()">
                Buscar Suppliere
              </button>
            </div>
          </div>
          <table class="table table-bordered table-sm table-responsive">
            <thead>
              <tr>
                <th scope="col">#</th>
                <th scope="col">Nombres</th>
                <th>Documento</th>
                <th scope="col">Direccion</th>
                <th>Telefono</th>
                <th>Correo</th>
                <th>Contacto</th>
                <th>Opciones</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="supplier in supplierList.data" v-bind:key="supplier.id">
                <th scope="row">{{ supplier.code }}</th>
                <td>{{ supplier.name }}</td>
                <td>{{ supplier.document }}</td>
                <td>{{ supplier.address }}</td>
                <td>{{ supplier.mobile }}</td>
                <td>{{ supplier.email }}</td>
                <td>
                  {{ supplier.contact }}
                </td>

                <td>
                  <button class="btn btn-outline-secondary" @click="$emit('add-supplier', supplier)"
                    data-dismiss="modal">
                    <i class="bi bi-plus-circle"></i>
                  </button>
                </td>
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
     <create-edit-supplier
      ref="CreateEditSupplier"
      @list-suppliers="listSuppliers(1)"
    />
  </div>
</template>

<script>
import CreateEditSupplier from "../Supplier/CreateEditSupplier.vue";

export default {
  components: { CreateEditSupplier },
  name: "add-supplier",
  data() {
    return {
      supplierList: {},
      filters: {
        supplier: "",
      },
    };
  },
  created() {
    this.listSuppliers();
  },
  methods: {
    listSuppliers() {
      let me = this;
      axios
        .post("api/suppliers/filter-supplier-list", null, this.$root.config)
        .then(function (response) {
          me.supplierList = response;
        });
    },
    searchSupplier() {
      let me = this;
      if (me.filters.supplier == "") {
        return false;
      }
      var url = "api/suppliers/filter-supplier-list?supplier=" + me.filters.supplier;
      if (me.filters.supplier.length >= 3) {
        axios
          .post(url, null, me.$root.config)
          .then(function (response) {
            me.supplierList = response;
          })
          .catch(function (error) {
            $("#no-results").toast("show");

            console.log(error);
          });
      }
    },
  },
};
</script>