<template>
  <div>
    <div
      class="modal fade"
      id="assignUserModal"
      tabindex="-1"
      aria-labelledby="assignUserModalLabel"
      aria-hidden="true"
      data-backdrop="static"
    >
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="assignUserModalLabel">
              Asignar Usuarios
            </h5>
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
            <form id="formAssignUser">
              <div class="row">
                <div class="col border-bottom">
                  <p>
                    <span class="font-weight-bold"> Caja: </span
                    >{{ formBox.name }}
                  </p>
                </div>
                <div class="col border-bottom">
                  <p>
                    <span class="font-weight-bold"> Prefijo: </span
                    >{{ formBox.prefix }}
                  </p>
                </div>
              </div>
              <div class="form-row mt-3">
                <div
                  class="form-group col-6"
                  v-for="user in assignments"
                  :key="user.id"
                >
                  <div class="form-check">
                    <input
                      class="form-check-input"
                      type="checkbox"
                      name ="assignments[]"
                      :value="user.id"
                      :id="`check_assign_${user.id}`"
                      :checked="user.assign > 0"
                    />
                    <label
                      class="form-check-label"
                      :for="`check_assign_${user.id}`"
                    >
                      {{ user.name }}
                    </label>
                  </div>
                </div>
              </div>
            </form>
          </div>
          <div class="modal-footer">
            <button
              type="button"
              class="btn btn-secondary"
              data-dismiss="modal"
            >
              Cerrar
            </button>
            <button
              type="button"
              class="btn btn-primary"
              @click="assignUsers()"
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
export default {
  name: "AssignUser",
  data() {
    return {
      formBox: {},
      assignments: [],
    };
  },
  created() {},
  methods: {
    getConsecutiveAllByBox(box) {
      axios
        .get("api/boxes/" + box + "/getAssignUserByBox", this.$root.config)
        .then((response) => {
          this.assignments = response.data.assignments;
        })
        .catch((response) => {});
    },
    assignUsers() {
      let me = this;

        const formAssignments = new FormData($("#formAssignUser")[0]);

      axios
        .post("api/boxes/"+me.formBox.id+"/toAssignUserByBox", formAssignments, me.$root.config)
        .then(function () {
          me.ResetData();
        })
        .catch((response) => {
          
        });
    },
    OpenAssignUser(box) {
      let me = this;
      me.ResetData();
      $("#assignUserModal").modal("show");
      me.formBox = box;
      this.getConsecutiveAllByBox(box.id);
    },

    ResetData() {
      let me = this;
      $("#formAssignUser")[0].reset();
      $("#assignUserModal").modal("hide");
      me.formBox = {
        name: "",
        prefix: "",
      };
      me.assignments = [];
      
    }
  },
  mounted() {},
};
</script>