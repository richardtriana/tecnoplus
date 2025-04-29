<template>
  <div id="login">
    <div id="logo">
      <h1> RESTAPLUS VER 2.1</h1>
    </div>
    <div class="stark-login">
      <form id="form_login" autocomplete="off" @submit.prevent="login">
        <div id="fade-box" class="px-5">
          <div class="form-group">
            <label class="w-100 text-left" for="exampleInputUsername1">Usuario o email</label>
            <input
              type="text"
              class="form-control"
              id="username"
              aria-describedby="usernameHelp"
              name="username"
              placeholder="Ingresar username"
              required
              v-model="formValues.username"
            />
            <small id="usernameHelp" class="form-text text-danger">
              {{ formErrors.username }}
            </small>
          </div>
          <div class="form-group">
            <label class="w-100 text-left" for="exampleInputPassword1">Contraseña</label>
            <input
              type="password"
              class="form-control"
              id="password"
              aria-describedby="passwordHelp"
              name="password"
              placeholder="Ingresar contraseña"
              required
              v-model="formValues.password"
            />
            <small id="passwordHelp" class="form-text text-danger">
              {{ formErrors.password }}
            </small>
          </div>
          <button type="submit" class="btn btn-primary">Acceder</button>
        </div>
      </form>
      <!-- ... resto del template ... -->
    </div>
  </div>
</template>

<script>
import global from "./../services/global.js";
import axios from "axios";

export default {
  name: "Login",
  data() {
    return {
      data: "Login",
      // Suponiendo que global.api = "http://192.168.100.64/restaplus/public"
      api: global.api,
      formValues: {
        username: "",
        password: "",
      },
      formErrors: {
        username: "",
        password: "",
      },
    };
  },
  methods: {
    login() {
      let config = {
        headers: {
          "Content-Type": "multipart/form-data",
        },
      };

      this.formErrors.username = "";
      this.formErrors.password = "";

      const formLogin = document.getElementById("form_login");

      // Importante: Agregar el "/api" antes de "/login"
      axios
        .post(`${this.api}/api/login`, new FormData(formLogin), config)
        .then((response) => {
          response = response.data;
          if (
            response.status == "success" &&
            typeof response.user === "object"
          ) {
            localStorage.setItem("token", response.user.api_token);
            localStorage.setItem("user", JSON.stringify(response.user));
            this.$router.push("/");
          }
        })
        .catch((error) => {
          // Manejo de errores
          if (error.response) {
            var errors = error.response.data.errors;
            if (typeof errors != "undefined") {
              if (typeof errors.username != "undefined") {
                this.formValues.username = "";
                this.formValues.password = "";
                this.formErrors.username = errors.username[0];
              }
              this.formValues.password = "";
            } else {
              this.formValues.password = "";
              this.formErrors.password = error.response.data.message;
            }
            console.log(error.response);
          }
        });
    },
  },
};
</script>

<style src="./../../sass/_login.scss" scoped lang="scss">
</style>
