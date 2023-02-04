<template>
  <div class="w-100">
    <div class="page-header text-center">
      <h3 class="">Categorias</h3>
    </div>
    <moon-loader
      class="m-auto"
      :loading="isLoading"
      :color="'#032F6C'"
      :size="100"
    />
    <div class="card-body">
      <section v-if="!isLoading">
        <div class="row justify-content-end my-4">
          <button
            type="button"
            class="btn btn-primary"
            data-toggle="modal"
            data-target="#categoryModal"
            @click="$refs.CreateEditCategory.ResetData(), (edit = false)"
            v-if="$root.validatePermission('category.store')"
          >
            Crear Categoria
          </button>
        </div>
        <table class="table table-sm table-bordered table-responsive-sm">
          <thead class="thead-primary">
            <tr>
              <th scope="col">#</th>
              <th scope="col">Categoria</th>
              <th v-if="$root.validatePermission('category.active')">Estado</th>
              <th v-if="$root.validatePermission('category.update')">
                Opciones
              </th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="(category, index) in categoryList.data"
              :key="category.id"
            >
              <th scope="row">{{ index + 1 }}</th>
              <td>{{ category.name }}</td>
              <td v-if="$root.validatePermission('category.active')">
                <button
                  class="btn"
                  :class="
                    category.active == '1' ? ' btn-success' : ' btn-danger'
                  "
                  @click="changeState(category.id)"
                >
                  <i
                    class="bi bi-check-circle-fill"
                    v-if="category.active == 1"
                  ></i>
                  <i class="bi bi-x-circle" v-if="category.active == 0"></i>
                </button>
              </td>
              <td v-if="$root.validatePermission('category.update')">
                <button
                  class="btn btn-outline-success"
                  @click="ShowData(category), (edit = true)"
                >
                  <i class="bi bi-pen"></i>
                </button>
              </td>
            </tr>
          </tbody>
        </table>
        <pagination
          :align="'center'"
          :data="categoryList"
          @pagination-change-page="listCategories"
        >
          <span slot="prev-nav">&lt; Previous</span>
          <span slot="next-nav">Next &gt;</span></pagination
        >
      </section>
    </div>

    <!-- Modal para creacion y edicion de categorys -->
    <div
      class="modal fade"
      id="categoryModal"
      tabindex="-1"
      aria-labelledby="categoryModalLabel"
      aria-hidden="true"
      data-backdrop="static"
    >
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="categoryModalLabel">Categoria</h5>
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
            <create-edit-category
              ref="CreateEditCategory"
              @list-categories="listCategories(1)"
            />
          </div>
          <div class="modal-footer">
            <button
              type="button"
              class="btn btn-secondary"
              @click="closeModal()"
            >
              Close
            </button>
            <button
              type="button"
              class="btn btn-primary"
              @click="SaveCategory()"
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
import CreateEditCategory from "./CreateEditCategory.vue";

export default {
  components: { CreateEditCategory },
  data() {
    return {
      isLoading: false,
      categoryList: {},
      edit: false,
    };
  },
  created() {
    this.$root.validateToken();
    this.listCategories(1);
  },
  methods: {
    listCategories(page = 1) {
      this.isLoading = true;
      let me = this;

      axios
        .get("api/categories?page=" + page, this.$root.config)
        .then(function (response) {
          me.categoryList = response.data.categories;
        })

        .finally(() => (this.isLoading = false));
    },
    SaveCategory: function () {
      let me = this;
      if (this.edit == false) {
        this.$refs.CreateEditCategory.CreateCategory();
      } else {
        this.$refs.CreateEditCategory.EditCategory();
      }
      me.listCategories(1);
    },

    ShowData: function (category) {
      this.$refs.CreateEditCategory.OpenEditCategory(category);
    },
    closeModal: function () {
      let me = this;
      this.$refs.CreateEditCategory.ResetData();
      me.listCategories(1);
    },
    changeState: function (id) {
      let me = this;
      axios
        .post("api/categories/" + id + "/activate", null, me.$root.config)
        .then(function () {
          me.listCategories(1);
        });
    },
  },
  mounted() {},
};
</script>
