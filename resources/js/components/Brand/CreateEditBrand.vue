<template>
  <div>
    <div
      class="modal fade"
      id="brandModal"
      tabindex="-1"
      aria-labelledby="brandModalLabel"
      aria-hidden="true"
      data-backdrop="static"
    >
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="brandModalLabel">Marca</h5>
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
            <form>
              <div class="form-group">
                <label for="formGroupExampleInput">Marca</label>
                <input
                  type="text"
                  class="form-control"
                  id="formGroupExampleInput"
                  placeholder="Marca"
                  v-model="formBrand.name"
                />
              </div>
            </form>
          </div>
          <div class="modal-footer">
            <button
              type="button"
              class="btn btn-secondary"
              data-dismiss="modal"
            >
              Cerrar
            </button>
            <button
              type="button"
              class="btn btn-primary"
              @click="formBrand.id ? EditBrand() : CreateBrand()"
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
export default {
  data() {
    return {
      formBrand: {
        name: "",
      },
      formErrors: {
        name: "",
      },
    };
  },
  created() {},
  methods: {
    CreateBrand() {
      let me = this;
      this.assignErrors(false);

      axios
        .post("api/brands", this.formBrand, this.$root.config)
        .then(function () {
          $("#brandModal").modal("hide");
          me.formBrand = {};
          me.$emit("list-brands");
        })
        .catch((response) => {
          this.assignErrors(response);
        });
    },
    OpenEditBrand(product) {
      let me = this;
      me.ResetData();
      $("#brandModal").modal("show");
      me.formBrand = product;
    },

    EditBrand() {
      let me = this;
      this.assignErrors(false);
      axios
        .put(
          "api/brands/" + this.formBrand.id,
          this.formBrand,
          this.$root.config
        )
        .then(function () {
          $("#brandModal").modal("hide");
          me.formBrand = {};
          me.$emit("list-brands");
        })
        .catch((response) => {
          this.assignErrors(response);
        });
    },
    ResetData() {
      let me = this;
      $("#brandModal").modal("hide");
      me.formBrand = {};
      me.formErrors.name = "";
    },
    assignErrors(response) {
      if (response) {
        var errors = response.response.data.errors;
        if (errors.name != "undefined") {
          this.formErrors.name = errors.name[0];
        }
      } else {
        this.formErrors.name = "";
      }
    },
  },
  mounted() {},
};
</script>