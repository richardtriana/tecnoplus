<template >
  <div id="login">
    <div id="logo">
      <h1> BIENVENIDOS A TECNOPLUS</h1>
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
            <small id="usernameHelp" class="form-text text-danger">{{
              formErrors.username
            }}</small>
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
            <small id="passwordHelp" class="form-text text-danger">{{
              formErrors.password
            }}</small>
          </div>
          <button type="submit" class="btn btn-primary">Acceder</button>
        </div>
      </form>
      <div class="hexagons">
        <span>&#x2B22;</span>
        <span>&#x2B22;</span>
        <span>&#x2B22;</span>
        <span>&#x2B22;</span>
        <span>&#x2B22;</span>
        <span>&#x2B22;</span>
        <span>&#x2B22;</span>
        <span>&#x2B22;</span>
        <span>&#x2B22;</span>
        <span>&#x2B22;</span>
        <span>&#x2B22;</span>
        <span>&#x2B22;</span>
        <br />
        <span>&#x2B22;</span>
        <span>&#x2B22;</span>
        <span>&#x2B22;</span>
        <span>&#x2B22;</span>
        <span>&#x2B22;</span>
        <span>&#x2B22;</span>
        <span>&#x2B22;</span>
        <span>&#x2B22;</span>
        <span>&#x2B22;</span>
        <span>&#x2B22;</span>
        <span>&#x2B22;</span>
        <br />
        <span>&#x2B22;</span>
        <span>&#x2B22;</span>
        <span>&#x2B22;</span>
        <span>&#x2B22;</span>
        <span>&#x2B22;</span>
        <span>&#x2B22;</span>
        <span>&#x2B22;</span>
        <span>&#x2B22;</span>
        <span>&#x2B22;</span>
        <span>&#x2B22;</span>
        <span>&#x2B22;</span>
        <span>&#x2B22;</span>

        <br />
        <span>&#x2B22;</span>
        <span>&#x2B22;</span>
        <span>&#x2B22;</span>
        <span>&#x2B22;</span>
        <span>&#x2B22;</span>
        <span>&#x2B22;</span>
        <span>&#x2B22;</span>
        <span>&#x2B22;</span>
        <span>&#x2B22;</span>
        <span>&#x2B22;</span>
        <span>&#x2B22;</span>
        <br />
        <span>&#x2B22;</span>
        <span>&#x2B22;</span>
        <span>&#x2B22;</span>
        <span>&#x2B22;</span>
        <span>&#x2B22;</span>
        <span>&#x2B22;</span>
        <span>&#x2B22;</span>
        <span>&#x2B22;</span>
        <span>&#x2B22;</span>
        <span>&#x2B22;</span>
        <span>&#x2B22;</span>
        <span>&#x2B22;</span>
      </div>
    </div>
    <div id="circle1">
      <div id="inner-cirlce1">
        <h2></h2>
      </div>
    </div>
  </div>
</template>

<script>
//Services
import global from "./../services/global.js";
// import "./../../sass/_login.scss";

export default {
  name: "Login",
  data() {
    return {
      data: "Login",
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
  created() {},
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
      axios
        .post(this.api + "/login", new FormData(formLogin), config)
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
        });
    },
  },
};
</script>

<style src="./../../sass/_login.scss" scoped lang="scss">
</style>