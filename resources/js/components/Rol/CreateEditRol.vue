<template>
  <div
    class="modal fade"
    id="rolModal"
    tabindex="-1"
    aria-labelledby="rolModalLabel"
    aria-hidden="true"
    data-backdrop="static"
  >
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="rolModalLabel">Rol</h5>
          <button
            type="button"
            class="close"
            data-dismiss="modal"
            aria-label="Close"
            @click="closeModal()"
          >
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <form id="form_rol" @submit.prevent="">
            <div class="form-group">
              <label for="name">Nombre</label>
              <input
                type="text"
                class="form-control"
                id="name"
                name="name"
                placeholder="Ingresar nombre"
                v-model="formRol.name"
                required
              />
              <small id="nameHelp" class="form-text text-danger">{{
                formErrors.name
              }}</small>
            </div>
            <div class="form-row">
              <div
                v-for="(section, index) in listPermissions"
                class="form-group col-6 col-md-4 col-lg-3"
                :key="index"
              >
                <label for="name">{{ index }}</label>
                <div
                  v-for="permission in section"
                  class="form-check"
                  :key="permission.id"
                >
                  <input
                    class="form-check-input check-permissions"
                    type="checkbox"
                    name="permissions_sync[]"
                    :value="permission.id"
                    :id="'permission' + permission.id"
                    :checked="validatePermission(permission.name)"
                  />
                  <label
                    class="form-check-label"
                    :for="'permission' + permission.id"
                  >
                    {{ permission.description }}
                  </label>
                </div>
              </div>
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" @click="closeModal()">
            Close
          </button>
          <button
            type="button"
            class="btn btn-primary"
            @click="formRol.id ? EditRol() : CreateRol()"
          >
            Guardar
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import global from "../../services/global.js";

export default {
  name: "CreateEditRol",
  data() {
    return {
      formRol: {
        name: "",
        permissions: [],
      },
      formErrors: {
        name: "",
      },
      listPermissions: [],
    };
  },
  created() {
    this.getPermissions();
  },
  methods: {
    getPermissions() {
      axios.get("api/permissions", this.$root.config).then((response) => {
        this.listPermissions = response.data.permissions;
        this.listPermissionsDedfault = this.listPermissions;
      });
    },
    validatePermission(permission) {
      return global.validatePermission(
        this.formRol.permissions ? this.formRol.permissions : [],
        permission
      );
    },
    CreateRol() {
      let me = this;
      this.assignErrors(false);
      const formRol = new FormData($("#form_rol")[0]);
      axios
        .post("api/roles", formRol, this.$root.config)
        .then(function () {
          me.ResetData();
          me.$emit("list-roles");
        })
        .catch((response) => {
          this.assignErrors(response);
        });
    },
    OpenEditRol(rol) {
      this.ResetData();
      $("#rolModal").modal("show");
      this.formRol = rol;
    },

    EditRol() {
      let me = this;
      this.assignErrors(false);

      let permissions_sync = [];
      $(".check-permissions:checked").each(function () {
        permissions_sync.push($(this).val());
      });

      this.formRol.permissions_sync = permissions_sync;

      axios
        .put("api/roles/" + this.formRol.id, this.formRol, this.$root.config)
        .then(function () {
          me.ResetData();
          me.$emit("list-roles");
        })
        .catch((response) => {
          this.assignErrors(response);
        });
    },
    closeModal: function () {
      $("#form_rol")[0].reset();
      this.ResetData();
      this.$emit("list-roles");
    },
    ResetData() {
      let me = this;
      $("#rolModal").modal("hide");
      me.formRol = {};
      this.assignErrors(false);
    },
    assignErrors(response) {
      if (response) {
        var errors = response.response.data.errors;
        if (errors.name != undefined) {
          this.formErrors.name = errors.name[0];
        }
      } else {
        this.formErrors.name = "";
      }
    },
  },
  mounted() {
    console.log("Component mounted.");
  },
};
</script>