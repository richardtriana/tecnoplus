<template>
  <div class="container">
    <div class="row justify-content-center">
      <form>
        <div class="form-group">
          <label for="name">Categoria</label>
          <input
            type="text"
            class="form-control"
            id="name"
            placeholder="Ingresar nombre"
            v-model="formCategory.name"
          />
          <small id="nameHelp" class="form-text text-danger">{{
            formErrors.name
          }}</small>
        </div>
      </form>
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      formCategory: {
        name: "",
      },
      formErrors: {
        name: "",
      },
    };
  },
  methods: {
    CreateCategory() {
      let me = this;
      this.assignErrors(false);

      axios
        .post("api/categories", this.formCategory, this.$root.config)
        .then(function () {
          $("#categoryModal").modal("hide");
          me.formCategory = {};
          me.$emit('list-categories');
        })
        .catch((response) => {
          this.assignErrors(response);
        });
    },
    OpenEditCategory(product) {
      let me = this;
      me.ResetData();
      $("#categoryModal").modal("show");
      me.formCategory = product;
    },

    EditCategory() {
      let me = this;
      this.assignErrors(false);      
      axios
        .put(
          "api/categories/" + this.formCategory.id,
          this.formCategory,
          this.$root.config
        )
        .then(function () {
          $("#categoryModal").modal("hide");
          me.formCategory = {};
          me.$emit('list-categories');
        })
        .catch((response) => {
          this.assignErrors(response);
        });
    },
    ResetData() {
      let me = this;
      $("#categoryModal").modal("hide");
      me.formCategory = {};
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
  },
};
</script>
