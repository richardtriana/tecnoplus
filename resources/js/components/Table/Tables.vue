<template>
  <div class="w-100">
    <div class="page-header text-center">
      <h3 class="">Mesas</h3>
    </div>
    <moon-loader
      class="m-auto"
      :loading="isLoading"
      :color="'#032F6C'"
      :size="100"
    />
    <div class="card-body">
      <section v-if="!isLoading">
        <div class="row justify-content-end my-4">
          <button
            type="button"
            class="btn btn-primary"
            data-toggle="modal"
            data-target="#tableModal"
            @click="$refs.CreateEditTable.ResetData(), (edit = false)"
            v-if="$root.validatePermission('table.store')"
          >
            Crear Mesa
          </button>
        </div>
        <table class="table table-sm table-bordered table-responsive-sm">
          <thead class="thead-primary">
            <tr>
              <th scope="col">#</th>
              <th scope="col">Mesa</th>
              <th scope="col">Observaciones</th>
              <th v-if="$root.validatePermission('table.update')">
                Opciones
              </th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="(table, index) in tableList.data"
              :key="table.id"
            >
              <th scope="row">{{ index + 1 }}</th>
              <td>{{ table.table }}</td>
              <td>{{ table.observations }}</td>
              <td v-if="$root.validatePermission('table.update')">
                <button
                  class="btn btn-outline-success"
                  @click="ShowData(table), (edit = true)"
                >
                  <i class="bi bi-pen"></i>
                </button>
              </td>
            </tr>
          </tbody>
        </table>
        <pagination
          :align="'center'"
          :data="tableList"
          @pagination-change-page="listTables"
        >
          <span slot="prev-nav">&lt; Previous</span>
          <span slot="next-nav">Next &gt;</span></pagination
        >
      </section>
    </div>

    <!-- Modal para creacion y edicion de tables -->
    <div
      class="modal fade"
      id="tableModal"
      tabindex="-1"
      aria-labelledby="tableModalLabel"
      aria-hidden="true"
      data-backdrop="static"
    >
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="tableModalLabel">Mesa</h5>
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
            <create-edit-table
              ref="CreateEditTable"
              @list-tables="listTables(1)"
            />
          </div>
          <div class="modal-footer">
            <button
              type="button"
              class="btn btn-secondary"
              @click="closeModal()"
            >
              Close
            </button>
            <button
              type="button"
              class="btn btn-primary"
              @click="SaveTable()"
            >
              Guardar
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import CreateEditTable from "./CreateEditTable.vue";

export default {
  components: { CreateEditTable },
  data() {
    return {
      isLoading: false,
      tableList: {},
      edit: false,
    };
  },
  created() {
    this.$root.validateToken();
    this.listTables(1);
  },
  methods: {
    listTables(page = 1) {
      this.isLoading = true;
      let me = this;

      axios
        .get("api/tables?page=" + page, this.$root.config)
        .then(function (response) {
          me.tableList = response.data.tables;
        })

        .finally(() => (this.isLoading = false));
    },
    SaveTable: function () {
      let me = this;
      if (this.edit == false) {
        this.$refs.CreateEditTable.CreateTable();
      } else {
        this.$refs.CreateEditTable.EditTable();
      }
      me.listTables(1);
    },

    ShowData: function (table) {
      this.$refs.CreateEditTable.OpenEditTable(table);
    },
    closeModal: function () {
      let me = this;
      this.$refs.CreateEditTable.ResetData();
      me.listTables(1);
    },
    changeState: function (id) {
      let me = this;
      axios
        .post("api/tables/" + id + "/activate", null, me.$root.config)
        .then(function () {
          me.listTables(1);
        });
    },
  },
  mounted() {},
};
</script>
