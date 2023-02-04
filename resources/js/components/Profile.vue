<template>
  <div class="col-12">
    <h3 class="text-center page-header">Configuración Perfil</h3>
    <div class="d-flex justify-content-center">
      <div class="list-group w-100">
        <a
          href="#"
          class="list-group-item list-group-item-action"
          data-toggle="modal"
          data-target="#modalInformationBasic"
        >
          1. Información basica
        </a>
        <a
          href="#"
          class="list-group-item list-group-item-action"
          data-toggle="modal"
          data-target="#modalChangePassword"
          >2. Cambiar contraseña</a
        >
      </div>
    </div>
    <!-- Modal Information basic -->
    <div
      class="modal fade"
      id="modalInformationBasic"
      data-backdrop="static"
      data-keyboard="false"
      tabindex="-1"
      aria-labelledby="modalInformationBasicLabel"
      aria-hidden="true"
    >
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="modalInformationBasicLabel">
              Editar Informacion basica
            </h5>
            <button
              type="button"
              class="close"
              data-dismiss="modal"
              aria-label="Close"
            >
              <span aria-hidden="true" @click="resetData(1)">&times;</span>
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
                  formUserErrors.name
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
                  formUserErrors.email
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
                  formUserErrors.username
                }}</small>
              </div>
            </form>
          </div>
          <div class="modal-footer">
            <button
              type="button"
              class="btn btn-secondary"
              data-dismiss="modal"
              @click="resetData(1)"
            >
              Cancelar
            </button>
            <button
              type="button"
              class="btn btn-primary"
              @click="updateProfile()"
            >
              Guardar
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Change password -->
    <div
      class="modal fade"
      id="modalChangePassword"
      data-backdrop="static"
      data-keyboard="false"
      tabindex="-1"
      aria-labelledby="modalInformationBasicLabel"
      aria-hidden="true"
    >
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="modalChangePasswordLabel">
              Cambiar Password
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
            <div class="form-row">
              <div class="form-group col-12">
                <label for="old_password">Contraseña Actual</label>
                <input
                  type="password"
                  class="form-control"
                  id="old_password"
                  name="password_confirmation"
                  placeholder="Ingresar contraseña"
                  v-model="formPassword.old_password"
                  pattern="^(?=\w*\d)(?=\w*[A-Z])(?=\w*[a-z])\S{8,16}$"
                  required
                />
                <small id="old_passwordHelp" class="form-text text-danger">{{
                  formPasswordErrors.old_password
                }}</small>
              </div>
              <div class="form-group col-12 col-md-6">
                <label for="password">Nueva Contraseña</label>
                <input
                  type="password"
                  class="form-control"
                  id="password"
                  name="password"
                  placeholder="Ingresar contraseña"
                  v-model="formPassword.password"
                  pattern="^(?=\w*\d)(?=\w*[A-Z])(?=\w*[a-z])\S{8,16}$"
                  required
                />
                <small id="passwordHelp" class="form-text text-danger">{{
                  formPasswordErrors.password
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
                  v-model="formPassword.password_confirmation"
                  pattern="^(?=\w*\d)(?=\w*[A-Z])(?=\w*[a-z])\S{8,16}$"
                  required
                />
                <small id="passwordHelp" class="form-text text-danger">{{
                  formPasswordErrors.password
                }}</small>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button
              type="button"
              class="btn btn-secondary"
              data-dismiss="modal"
            >
              Cancelar
            </button>
            <button type="button" class="btn btn-primary" @click="updatePassword">Guardar</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
<script>
export default {
  data() {
    return {
      formUser: {
        id: this.$root.user.sub,
        name: this.$root.user.name,
        email: this.$root.user.email,
        username: this.$root.user.username,
        edit_profile: true,
      },
      formPassword: {
        old_password: "",
        password: "",
        password_confirmation: "",
      },
      formUserErrors: {
        name: "",
        email: "",
        username: "",
      },
      formPasswordErrors: {
        old_password: "",
        password: "",
        password_confirmation: "",
      },
    };
  },
  created() {
    this.$root.validateToken();
  },
  methods: {
    updateProfile() {
      let me = this;
      this.assignErrors(false, 1);
      axios
        .put("api/users/" + this.formUser.id, this.formUser, this.$root.config)
        .then(function () {
          me.$root.user.name = me.formUser.name;
          me.$root.user.email = me.formUser.email;
          me.$root.user.username = me.formUser.username;
          $("#modalInformationBasic").modal("hide");
        })
        .catch((response) => {
          this.assignErrors(response, 1);
        });
    },
    updatePassword() {
      this.assignErrors(false, 2);
      let me = this;
      axios
        .put("api/users/changePassword", this.formPassword, this.$root.config)
        .then((response) => {
          me.resetData(2);
        })
        .catch((response) => {
          me.assignErrors(response, 2);
        });
    },
    resetData(optionForm) {
      if (optionForm == 1) {
        this.formUser = {
          id: this.$root.user.sub,
          name: this.$root.user.name,
          email: this.$root.user.email,
          username: this.$root.user.username,
          edit_profile: true,
        };
      }else if(optionForm == 2){
        this.formPassword = {
          old_password: "",
          password: "",
          password_confirmation: "",
        };
        this.assignErrors(false, 2);
        $("#modalChangePassword").modal("hide");
      }
    },

    assignErrors(response, optionForm) {
      if (optionForm == 1) {
        if (response) {
          var errors = response.response.data.errors;

          if (errors.name != undefined) {
            this.formUserErrors.name = errors.name[0];
          }
          if (errors.email != undefined) {
            this.formUserErrors.email = errors.email[0];
          }
          if (errors.username != undefined) {
            this.formUserErrors.username = errors.username[0];
          }
        } else {
          this.formUserErrors.name = "";
          this.formUserErrors.email = "";
          this.formUserErrors.username = "";
        }
      } else if (optionForm == 2) {
        if (response) {
          var errors = response.response.data.errors;

          if (errors.old_password != undefined) {
            this.formPasswordErrors.old_password = errors.old_password[0];
          }
          if (errors.password != undefined) {
            this.formPasswordErrors.password = errors.password[0];
          }
        } else {
          this.formPasswordErrors.old_password = "";
          this.formPasswordErrors.password = "";
        }
      }
    },
  },
};
</script>
