<template>
  <div class="col-12">
    <div class="w-100 text-center">
      <h3 class="page-header">Roles</h3>

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
            data-target="#rolModal"
            @click="$refs.CreateEditRol.ResetData()"
            v-if="$root.validatePermission('rol.store')"
          >
            Crear Rol
          </button>
        </div>
        <table class="table table-sm table-bordered table-responsive-sm">
          <thead class="thead-primary">
            <tr>
              <th scope="col">#</th>
              <th scope="col">Rol</th>
              <th v-if="$root.validatePermission('rol.update')">
                Opciones
              </th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="(rol, index) in roleList.data"
              :key="rol.id"
            >
              <th scope="row">{{ index + 1 }}</th>
              <td>{{ rol.name }}</td>
              <td v-if="$root.validatePermission('rol.update')">
                <button
                  class="btn btn-success"
                  @click="ShowData(rol)"
                >
                  Editar
                </button>
              </td>
            </tr>
          </tbody>
        </table>
        <pagination
          :align="'center'"
          :data="roleList"
          @pagination-change-page="listRoles"
        >
          <span slot="prev-nav">&lt; Previous</span>
          <span slot="next-nav">Next &gt;</span></pagination
        >
      </section>
    </div>

    <!-- Modal para creacion y edicion de categorys -->
    <create-edit-rol ref="CreateEditRol" @list-roles="listRoles(1)" />
  </div>
</template>

<script>

import CreateEditRol from "./CreateEditRol.vue";

export default {
  name: "Roles",
  components: { CreateEditRol },
  data() {
    return {
      isLoading: false,
      roleList: {},
    };
  },
  created() {
    this.$root.validateToken();
    this.listRoles(1);
  },
  methods: {
    listRoles(page = 1) {
      this.isLoading = true;
      let me = this;

      axios
        .get("api/roles?page=" + page, this.$root.config)
        .then(function (response) {
          me.roleList = response.data.roles;
        })

        .finally(() => (this.isLoading = false));
    },
  
    ShowData: function (rol) {
      this.$refs.CreateEditRol.OpenEditRol(rol);
    }
  },
  mounted() {
    console.log("Component mounted.");
  },
};
</script>
