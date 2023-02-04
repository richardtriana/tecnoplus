<template>
  <div class="col-12">
    <div class="w-100 text-center">
      <h3 class="page-header">Usuarios</h3>

      <moon-loader
        class="m-auto"
        :loading="isLoading"
        :color="'#032F6C'"
        :size="100"
      />
    </div>

    <div class="card-body">
      <section v-if="!isLoading">
        <div class="row justify-content-end my-4">
          <button
            type="button"
            class="btn btn-primary"
            data-toggle="modal"
            data-target="#userModal"
            @click="$refs.CreateEditUser.ResetData()"
            v-if="$root.validatePermission('user.store')"
          >
            Crear Usuario
          </button>
        </div>
        <table class="table table-sm table-bordered table-responsive-sm">
          <thead class="thead-primary">
            <tr>
              <th scope="col">#</th>
              <th scope="col">Nombre</th>
              <th scope="col">Email</th>
              <th scope="col">Username</th>
              <th scope="col">Rol</th>
              <th v-if="$root.validatePermission('user.active')">Estado</th>
              <th v-if="$root.validatePermission('user.update')">
                Opciones
              </th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="(user, index) in userList.data"
              :key="user.id"
            >
              <th scope="row">{{ index + 1 }}</th>
              <td>{{ user.name }}</td>
              <td>{{ user.email }}</td>
              <td>{{ user.username }}</td>
              <td v-if=" user.roles.length > 0 " >{{ user.roles[0].name }}</td>
              <td v-else> No definido</td>
              <td v-if="$root.validatePermission('user.active')">
                <button
                  class="btn"
                  :class="user.state ? 'btn-success' : 'btn-danger'"
                  @click="changeState(user.id)"
                >
                  <i v-if="user.state" class="bi bi-check-circle-fill"></i>
                  <i v-else class="bi bi-x-circle"></i>
                </button>
              </td>
              <td v-if="$root.validatePermission('user.update')">
                <button
                  class="btn btn-outline-success"
                  @click="ShowData(user)"
                >
                  <i class="bi bi-pen"></i>
                </button>
              </td>
            </tr>
          </tbody>
        </table>
        <pagination
          :align="'center'"
          :data="userList"
          @pagination-change-page="listUsers"
        >
          <span slot="prev-nav">&lt; Previous</span>
          <span slot="next-nav">Next &gt;</span></pagination
        >
      </section>
    </div>

    <!-- Modal para creacion y edicion de categorys -->
    <create-edit-user ref="CreateEditUser" @list-users="listUsers(1)" />
  </div>
</template>

<script>

import CreateEditUser from "./CreateEditUser.vue";

export default {
  name: "Users",
  components: { CreateEditUser },
  data() {
    return {
      isLoading: false,
      userList: {},
    };
  },
  created() {
    this.listUsers(1);
  },
  methods: {
    listUsers(page = 1) {
      this.isLoading = true;
      let me = this;

      axios
        .get("api/users?page=" + page, this.$root.config)
        .then(function (response) {
          me.userList = response.data.users;
        })

        .finally(() => (this.isLoading = false));
    },
  
    ShowData: function (user) {
      this.$refs.CreateEditUser.OpenEditUser(user);
    },
    
    changeState: function (id) {
      let me = this;
      axios
        .post("api/users/" + id + "/activate", null, me.$root.config)
        .then(function () {
          me.listUsers(1);
        });
    },
  },
  mounted() {
    console.log("Component mounted.");
  },
};
</script>