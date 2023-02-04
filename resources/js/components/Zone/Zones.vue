<template>
  <div class="w-100">
    <div class="page-header text-center">
      <h3 class="">Zonas</h3>
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
            data-target="#zoneModal"
            @click="$refs.CreateEditZone.ResetData(), (edit = false)"
            v-if="$root.validatePermission('zone.store')"
          >
            Crear Zona
          </button>
        </div>
        <table class="table table-sm table-bordered table-responsive-sm">
          <thead class="thead-primary">
            <tr>
              <th scope="col">#</th>
              <th scope="col">Zona</th>
              <th v-if="$root.validatePermission('zone.update')">
                Opciones
              </th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="(zone, index) in zoneList.data"
              :key="zone.id"
            >
              <th scope="row">{{ index + 1 }}</th>
              <td>{{ zone.zone }}</td>
              <td v-if="$root.validatePermission('zone.update')">
                <button
                  class="btn btn-outline-success"
                  @click="ShowData(zone), (edit = true)"
                >
                  <i class="bi bi-pen"></i>
                </button>
              </td>
            </tr>
          </tbody>
        </table>
        <pagination
          :align="'center'"
          :data="zoneList"
          @pagination-change-page="listZones"
        >
          <span slot="prev-nav">&lt; Previous</span>
          <span slot="next-nav">Next &gt;</span></pagination
        >
      </section>
    </div>

    <!-- Modal para creacion y edicion de zones -->
    <div
      class="modal fade"
      id="zoneModal"
      tabindex="-1"
      aria-labelledby="zoneModalLabel"
      aria-hidden="true"
      data-backdrop="static"
    >
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="zoneModalLabel">Categoria</h5>
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
            <create-edit-zone
              ref="CreateEditZone"
              @list-zones="listZones(1)"
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
              @click="SaveZone()"
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
import CreateEditZone from "./CreateEditZone.vue";

export default {
  components: { CreateEditZone },
  data() {
    return {
      isLoading: false,
      zoneList: {},
      edit: false,
    };
  },
  created() {
    this.$root.validateToken();
    this.listZones(1);
  },
  methods: {
    listZones(page = 1) {
      this.isLoading = true;
      let me = this;

      axios
        .get("api/zones?page=" + page, this.$root.config)
        .then(function (response) {
          me.zoneList = response.data.zones;
        })

        .finally(() => (this.isLoading = false));
    },
    SaveZone: function () {
      let me = this;
      if (this.edit == false) {
        this.$refs.CreateEditZone.CreateZone();
      } else {
        this.$refs.CreateEditZone.EditZone();
      }
      me.listZones(1);
    },

    ShowData: function (zone) {
      this.$refs.CreateEditZone.OpenEditZone(zone);
    },
    closeModal: function () {
      let me = this;
      this.$refs.CreateEditZone.ResetData();
      me.listZones(1);
    },
    changeState: function (id) {
      let me = this;
      axios
        .post("api/zones/" + id + "/activate", null, me.$root.config)
        .then(function () {
          me.listZones(1);
        });
    },
  },
  mounted() {},
};
</script>
