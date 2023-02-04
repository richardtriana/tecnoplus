<template>
  <div class="page">
    <div class="page-header">
      <div class="row">
        <div class="col">
          <h3 class="page-title">Marcas</h3>
        </div>
        <div class="col text-right">
          <button
            type="button"
            class="btn btn-outline-primary"
            data-toggle="modal"
            data-target="#brandModal"
            v-if="$root.validatePermission('brand.store')"
          >
            Crear Marca
          </button>
        </div>
      </div>
    </div>
    <div class="page-content">
      <moon-loader
        class="m-auto"
        :loading="isLoading"
        :color="'#032F6C'"
        :size="100"
      />
      <div v-show="!isLoading">
        <section class="my-4">
          <table class="table table-sm table-bordered table-responsive-sm">
            <thead>
              <tr>
                <th scope="col">#</th>
                <th scope="col">Nombre</th>
                <th scope="col" v-if="$root.validatePermission('brand.active')">
                  Estado
                </th>
                <th v-if="$root.validatePermission('brand.update')">
                  Opciones
                </th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="brand in BrandList.data" :key="brand.id">
                <th scope="row">{{ brand.id }}</th>
                <td>{{ brand.name }}</td>
                <td v-if="$root.validatePermission('brand.active')">
                  <button
                    class="btn"
                    :class="
                      brand.active == 0
                        ? 'btn-outline-danger'
                        : 'btn-outline-success'
                    "
                    @click="changeState(brand.id)"
                  >
                    <i v-if="brand.active == 0" class="bi bi-x-circle"></i>
                    <i v-if="brand.active == 1" class="bi bi-check-circle"></i>
                  </button>
                </td>
                <td v-if="$root.validatePermission('brand.update')">
                  <button
                    class="btn btn-outline-success"
                    @click="ShowData(brand)"
                  >
                    <i class="bi bi-pen"></i>
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
          <pagination
            :align="'center'"
            :data="BrandList"
            @pagination-change-page="listBrands"
          >
            <span slot="prev-nav">&lt; Previous</span>
            <span slot="next-nav">Next &gt;</span></pagination
          >
        </section>
      </div>
    </div>
    <create-edit-brand ref="CreateEditBrand" @list-brands="listBrands(1)" />
  </div>
</template>
<script>
import global from "./../../services/global.js";
import CreateEditBrand from "./CreateEditBrand.vue";
export default {
  data() {
    return {
      isLoading: false,
      BrandList: {},
    };
  },
  components: {
    CreateEditBrand,
  },
  created() {
    this.$root.validateToken();
    this.listBrands(1);
  },
  methods: {
    listBrands(page = 1) {
      this.isLoading = true;
      let me = this;
      axios
        .get("api/brands?page=" + page, this.$root.config)
        .then(function (response) {
          me.BrandList = response.data.brands;
        })
        .finally(() => (this.isLoading = false));
    },
    ShowData: function (brand) {
      this.$refs.CreateEditBrand.OpenEditBrand(brand);
    },
    changeState: function (id) {
      let me = this;
      axios
        .post("api/brands/" + id + "/activate", null, me.$root.config)
        .then(function () {
          me.listBrands(1);
        });
    },
    validatePermission(permission) {
      return global.validatePermission(this.$root.permissions, permission);
    },
  },
};
</script>