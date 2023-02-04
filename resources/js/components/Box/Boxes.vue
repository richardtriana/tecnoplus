<template>
  <div class="page">
    <div class="page-header">
      <div class="row">
        <div class="col">
          <h3 class="page-title">Cajas</h3>
        </div>
        <div class="col text-right">
          <button type="button" class="btn btn-outline-primary" data-toggle="modal" data-target="#boxModal"
            v-if="$root.validatePermission('box.store')" @click="$refs.CreateEditBox.ResetData()">
            Crear Cajas
          </button>
        </div>
      </div>
    </div>
    <div class="page-content">
      <moon-loader class="m-auto" :loading="isLoading" :color="'#032F6C'" :size="100" />
      <div v-show="!isLoading">
        <section class="my-4">
          <table class="table table-sm table-bordered table-responsive-sm">
            <thead>
              <tr>
                <th scope="col">#</th>
                <th scope="col">Nombre</th>
                <th scope="col">Prefijo</th>
                <td scope="col" v-if="$root.validatePermission('box.store')">Asignar usuarios</td>
                <th scope="col" v-if="$root.validatePermission('box.active')">Estado</th>
                <th>Base</th>
                <th>Historial Base</th>
                <th v-if="$root.validatePermission('box.update')">Opciones</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="box in boxList.data" :key="box.id">
                <th scope="row">{{ box.id }}</th>
                <td>{{ box.name }}</td>
                <td>{{ box.prefix }}</td>
                <th v-if="$root.validatePermission('box.store')">
                  <button class="btn btn-outline-primary" @click="$refs.AssignUser.OpenAssignUser(box)">
                    <i class="bi bi-person-plus-fill"></i>
                  </button>
                </th>
                <td v-if="$root.validatePermission('box.active')">
                  <button class="btn" :class="
                    box.active == 1
                      ? 'btn-outline-danger'
                      : 'btn-outline-success'
                  " @click="changeState(box.id)">
                    <i v-if="box.active == 1" class="bi bi-x-circle"></i>
                    <i v-if="box.active == 0" class="bi bi-check-circle"></i>
                  </button>
                </td>
                <td>{{ box.base | currency }}</td>
                <td class="text-right">
                  <button class="btn btn-primary" data-toggle="modal" data-target="#historyBoxModal"
                    @click="showHistoryBox(box.history)">
                    <i class="bi bi-clock-history"></i>
                  </button>
                </td>
                <td v-if="$root.validatePermission('box.update')">
                  <button class="btn btn-outline-success" @click="ShowData(box)">
                    <i class="bi bi-pen"></i>
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </section>
      </div>
    </div>
    <create-edit-box ref="CreateEditBox" @list-boxes="listBoxes(1)" />
    <assign-user ref="AssignUser"> </assign-user>
    <show-history-box ref="ShowHistoryBox"></show-history-box>
  </div>
</template>

<script>
import CreateEditBox from "./CreateEditBox.vue";
import AssignUser from "./AssignUser.vue";
import ShowHistoryBox from './ShowHistoryBox.vue';

export default {
  data() {
    return {
      isLoading: false,
      boxList: {},
    };
  },
  components: {
    CreateEditBox,
    AssignUser,
    ShowHistoryBox
  },
  created() {
    this.$root.validateToken();
    this.listBoxes(1);
  },
  methods: {
    listBoxes(page = 1) {
      this.isLoading = true;
      let me = this;
      axios
        .get("api/boxes?page=" + page, this.$root.config)
        .then(function (response) {
          me.boxList = response.data.boxes;
        })
        .finally(() => (this.isLoading = false));
    },
    ShowData: function (box) {
      this.$refs.CreateEditBox.OpenEditBox(box);
    },
    showHistoryBox(history) {
      this.$refs.ShowHistoryBox.convertStringToJson(history);
    },
    changeState: function (id) {
      let me = this;
      axios
        .post("api/boxes/" + id + "/activate", null, me.$root.config)
        .then(function () {
          me.listBoxes(1);
        });
    },
  },
};
</script>