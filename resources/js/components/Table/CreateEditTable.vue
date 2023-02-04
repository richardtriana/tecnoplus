<template>
  <div class="container">
    <div class="row justify-content-center">
      <form>
        <div class="form-group">
          <label for="table">Mesa</label>
          <input
            type="text"
            class="form-control"
            id="table"
            placeholder=""
            v-model="formTable.table"
          />
          <small class="form-text text-danger">{{ formErrors.table }}</small>
        </div>
        <div class="form-group">
          <label for="observations">Observaciones</label>
          <input
            type="text"
            class="form-control"
            id="observations"
            placeholder="Observaciones"
            v-model="formTable.observations"
          />
          <small class="form-text text-danger">{{
            formErrors.observations
          }}</small>
        </div>
        <div class="form-group">
          <label for="state">Estado</label>
          <select name="state" id="state" class="custom-select" v-model="formTable.state">
            <option value="free">Libre</option>
            <option value="occupied">Ocupada</option>
          </select>         
          <small class="form-text text-danger">{{
            formErrors.state
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
      formTable: {
        observations: 0,
        table: 0,
        state: 'free',
      },
      formErrors: {
        errors: "",
      },
    };
  },
  methods: {
    CreateTable() {
      let me = this;
      this.assignErrors(false);

      axios
        .post("api/tables", this.formTable, this.$root.config)
        .then(function () {
          $("#tableModal").modal("hide");
          me.formTable = {};
          me.$emit('list-tables');
        })
        .catch((response) => {
          this.assignErrors(response);
        });
    },
    OpenEditTable(table) {
      let me = this;
      $("#tableModal").modal("show");
      me.formTable = table;
    },

    EditTable() {
      let me = this;
      this.assignErrors(false);

      axios
        .put("api/tables/" + this.formTable.id, this.formTable, this.$root.config)
        .then(function () {
          $("#tableModal").modal("hide");
          me.formTable = {};

          me.$emit('list-tables');
        })
        .catch((response) => {
          this.assignErrors(response);
        });
    },

    ResetData() {
      let me = this;
      $("#tableModal").modal("hide");
      me.formTable = {};
      this.assignErrors(false);
    },
    closeModal() {
      this.edit = false;
      this.ResetData();
      this.$emit("list-tables");
    },
    assignErrors(response) {
      if (response) {
        var errors = response.response.data.errors;
        if (errors.observations != "undefined") {
          this.formErrors.observations = errors.observations[0];
        }
      } else {
        this.formErrors.observations = "";
      }
    },
  },
  mounted() {
    console.log("Component mounted.");
  },
};
</script>
