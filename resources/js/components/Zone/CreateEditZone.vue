<template>
  <div class="container">
    <div class="row justify-content-center">
      <form>
        <div class="form-group">
          <label for="zone">Zona</label>
          <input
            type="text"
            class="form-control"
            id="zone"
            placeholder=""
            v-model="formZone.zone"
          />
          <small class="form-text text-danger">{{ formErrors.zone }}</small>
        </div>
        <div class="form-group">
          <label for="printer">Impresora</label>
          <input
            type="text"
            class="form-control"
            id="printer"
            placeholder="Nombre de impresora POS"
            v-model="formZone.printer"
          />
          <small class="form-text text-danger">{{
            formErrors.printer
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
      formZone: {
        printer: 0,
        zone: 0,
      },
      formErrors: {
        errors: "",
      },
    };
  },
  methods: {
    CreateZone() {
      let me = this;
      this.assignErrors(false);

      axios
        .post("api/zones", this.formZone, this.$root.config)
        .then(function () {
          $("#zoneModal").modal("hide");
          me.formZone = {};
          me.$emit('list-zones');
        })
        .catch((response) => {
          this.assignErrors(response);
        });
    },
    OpenEditZone(zone) {
      let me = this;
      $("#zoneModal").modal("show");
      me.formZone = zone;
    },

    EditZone() {
      let me = this;
      this.assignErrors(false);

      axios
        .put("api/zones/" + this.formZone.id, this.formZone, this.$root.config)
        .then(function () {
          $("#zoneModal").modal("hide");
          me.formZone = {};

          me.$emit('list-zones');
        })
        .catch((response) => {
          this.assignErrors(response);
        });
    },

    ResetData() {
      let me = this;
      $("#zoneModal").modal("hide");
      me.formZone = {};
      this.assignErrors(false);
    },
    closeModal() {
      this.edit = false;
      this.ResetData();
      this.$emit("list-zones");
    },
    assignErrors(response) {
      if (response) {
        var errors = response.response.data.errors;
        if (errors.printer != "undefined") {
          this.formErrors.printer = errors.printer[0];
        }
      } else {
        this.formErrors.printer = "";
      }
    },
  },
  mounted() {
    console.log("Component mounted.");
  },
};
</script>
