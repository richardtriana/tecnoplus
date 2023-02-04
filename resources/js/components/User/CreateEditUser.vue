<template>
  <div
    class="modal fade"
    id="userModal"
    tabindex="-1"
    aria-labelledby="userModalLabel"
    aria-hidden="true"
    data-backdrop="static"
  >
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="userModalLabel">Usuario</h5>
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
          <form id="form_rol">
            <div class="form-group">
              <label for="name">Nombre</label>
              <input
                type="text"
                class="form-control"
                id="name"
                name="name"
                placeholder="Ingresar nombre"
                v-model="formUser.name"
                required
              />
              <small id="nameHelp" class="form-text text-danger">{{
                formErrors.name
              }}</small>
            </div>
            <div class="form-group">
              <label for="email">Email</label>
              <input
                type="email"
                class="form-control"
                id="email"
                name="email"
                placeholder="Ingresar email"
                v-model="formUser.email"
                pattern="[a-zA-Z0-9_]+([.][a-zA-Z0-9_]+)*@[a-zA-Z0-9_]+([.][a-zA-Z0-9_]+)*[.][a-zA-Z]{1,5}"
                required
              />
              <small id="emailHelp" class="form-text text-danger">{{
                formErrors.email
              }}</small>
            </div>
            <div class="form-group">
              <label for="name">Username</label>
              <input
                type="text"
                class="form-control"
                id="username"
                name="username"
                placeholder="Ingresar nombre de usuario"
                v-model="formUser.username"
                required
              />
              <small id="usernameHelp" class="form-text text-danger">{{
                formErrors.username
              }}</small>
            </div>
            <div class="form-row">
              <div class="form-group col-12 col-md-6">
                <label for="password">Constraseña</label>
                <input
                  type="password"
                  class="form-control"
                  id="password"
                  name="password"
                  placeholder="Ingresar contraseña"
                  v-model="formUser.password"
                  pattern="^(?=\w*\d)(?=\w*[A-Z])(?=\w*[a-z])\S{8,16}$"
                  required
                />
                <small id="passwordHelp" class="form-text text-danger">{{
                  formErrors.password
                }}</small>
              </div>
              <div class="form-group col-12 col-md-6">
                <label for="password_confirmation">Confirmar contraseña</label>
                <input
                  type="password"
                  class="form-control"
                  id="password_confirmation"
                  name="password_confirmation"
                  placeholder="Ingresar contraseña"
                  v-model="formUser.password_confirmation"
                  pattern="^(?=\w*\d)(?=\w*[A-Z])(?=\w*[a-z])\S{8,16}$"
                  required
                />
                <small id="passwordHelp" class="form-text text-danger">{{
                  formErrors.password
                }}</small>
              </div>
            </div>
            <div class="form-group">
              <label for="rol">Rol</label>
              <select
                name="rol"
                id="rol"
                v-model="formUser.rol"
                class="form-control"
                required
              >
                <option value="" disabled selected>Seleciona el rol</option>
                <option v-for="rol in listRoles" :key="rol.id" :value="rol.id">
                  {{ rol.name }}
                </option>
              </select>
              <small id="rolHelp" class="form-text text-danger">{{
                formErrors.rol
              }}</small>
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
            @click="formUser.id ? EditUser() : CreateUser()"
          >
            Guardar
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: "CreateEditUser",
  data() {
    return {
      formUser: {},
      formErrors: {
        name: "",
        email: "",
        password: "",
        rol: "",
      },
      listRoles: [],
    };
  },
  created() {
    this.getRoles();
  },
  methods: {
    getRoles() {
      axios.get("api/roles/getAllRoles", this.$root.config).then((response) => {
        this.listRoles = response.data.roles;
      });
    },
    CreateUser() {
      let me = this;
      this.assignErrors(false);
      axios
        .post("api/register", this.formUser, this.$root.config)
        .then(function () {
          me.ResetData();
          me.$emit("list-users");
        })
        .catch((response) => {
          console.log(response.response);
          this.assignErrors(response);
        });
    },
    OpenEditUser(user) {
      this.ResetData();
      $("#userModal").modal("show");
      this.formUser = user;
      this.formUser.rol = user.roles.length > 0 ? user.roles[0].id : "";
    },

    EditUser() {
      let me = this;
      this.assignErrors(false);
      axios
        .put("api/users/" + this.formUser.id, this.formUser, this.$root.config)
        .then(function () {
          me.ResetData();
          me.$emit("list-users");
        })
        .catch((response) => {
          this.assignErrors(response);
        });
    },
    closeModal: function () {
      this.ResetData();
      this.$emit("list-roles");
    },
    ResetData() {
      let me = this;
      $("#userModal").modal("hide");
      me.formUser = {};
      me.formUser.rol = "";
      this.assignErrors(false);
    },
    assignErrors(response) {
      if (response) {
        var errors = response.response.data.errors;

        if (errors.name != undefined) {
          this.formErrors.name = errors.name[0];
        }
        if (errors.email != undefined) {
          this.formErrors.email = errors.email[0];
        }
        if (errors.username != undefined) {
          this.formErrors.username = errors.username[0];
        }
        if (errors.password != undefined) {
          this.formErrors.password = errors.password[0];
        }
        if (errors.rol != undefined) {
          this.formErrors.rol = errors.rol[0];
        }
      } else {
        this.formErrors.name = "";
        this.formErrors.email = "";
        this.formErrors.password = "";
        this.formErrors.rol = "";
        this.formErrors.username = "";
      }
    },
  },
  mounted() {
    console.log("Component mounted.");
  },
};
</script>