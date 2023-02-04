<template>
  <div class="container">
    <div class="row justify-content-center">
      <form>
        <div class="form-group">
          <label for="name">Nombre Impuesto</label>
          <input
            type="text"
            class="form-control"
            id="name"
            placeholder=""
            v-model="formTax.name"
          />
          <small class="form-text text-danger">{{ formErrors.name }}</small>
        </div>
        <div class="form-group">
          <label for="percentage">Porcentaje</label>
          <input
            type="number"
            class="form-control"
            id="percentage"
            placeholder="Ingresar porcentaje"
            v-model="formTax.percentage"
          />
          <small class="form-text text-danger">{{
            formErrors.percentage
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
      formTax: {
        percentage: 0,
        name: 0,
      },
      formErrors: {
        errors: "",
      },
    };
  },
  methods: {
    CreateTax() {
      let me = this;
      this.assignErrors(false);

      axios
        .post("api/taxes", this.formTax, this.$root.config)
        .then(function () {
          $("#taxModal").modal("hide");
          me.formTax = {};
          me.$emit('list-taxes');
        })
        .catch((response) => {
          this.assignErrors(response);
        });
    },
    OpenEditTax(tax) {
      let me = this;
      $("#taxModal").modal("show");
      me.formTax = tax;
    },

    EditTax() {
      let me = this;
      this.assignErrors(false);

      axios
        .put("api/taxes/" + this.formTax.id, this.formTax, this.$root.config)
        .then(function () {
          $("#taxModal").modal("hide");
          me.formTax = {};

          me.$emit('list-taxes');
        })
        .catch((response) => {
          this.assignErrors(response);
        });
    },

    ResetData() {
      let me = this;
      $("#taxModal").modal("hide");
      me.formTax = {};
      this.assignErrors(false);
    },
    closeModal() {
      this.edit = false;
      this.ResetData();
      this.$emit("list-taxes");
    },
    assignErrors(response) {
      if (response) {
        var errors = response.response.data.errors;
        if (errors.percentage != "undefined") {
          this.formErrors.percentage = errors.percentage[0];
        }
      } else {
        this.formErrors.percentage = "";
      }
    },
  },
  mounted() {
    console.log("Component mounted.");
  },
};
</script>
