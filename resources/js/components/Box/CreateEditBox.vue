<template>
  <div>
    <div
      class="modal fade"
      id="boxModal"
      tabindex="-1"
      aria-labelledby="boxModalLabel"
      aria-hidden="true"
      data-backdrop="static"
    >
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <!-- Encabezado del Modal -->
          <div class="modal-header">
            <h5 class="modal-title" id="boxModalLabel">Caja</h5>
            <button
              type="button"
              class="close"
              data-dismiss="modal"
              aria-label="Cerrar"
              @click="ResetData"
            >
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <!-- Cuerpo del Modal -->
          <div class="modal-body">
            <form id="formBox">
              <!-- Nombre o Número -->
              <div class="form-group">
                <label for="name">Nombre o Número</label>
                <input
                  type="text"
                  class="form-control"
                  id="name"
                  placeholder="Ingresar nombre o número"
                  v-model="formBox.name"
                />
                <small class="form-text text-danger">{{ formErrors.name }}</small>
              </div>
              <!-- Impresora POS -->
              <div class="form-group">
                <label for="printer">Impresora POS</label>
                <input
                  type="text"
                  class="form-control"
                  id="printer"
                  placeholder="Ingresar nombre de la impresora POS"
                  v-model="formBox.printer"
                />
                <small class="form-text text-danger">{{ formErrors.printer }}</small>
              </div>
              <!-- Aquí se pueden agregar otros campos si fuera necesario -->
            </form>
          </div>
          <!-- Pie del Modal -->
          <div class="modal-footer">
            <button
              type="button"
              class="btn btn-secondary"
              data-dismiss="modal"
              @click="ResetData"
            >
              Cerrar
            </button>
            <button
              type="button"
              class="btn btn-primary"
              @click="formBox.id ? EditBox() : CreateBox()"
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
  name: "CreateEditBox",
  data() {
    return {
      formBox: {
        name: "",
        printer: ""
      },
      formErrors: {
        name: "",
        printer: ""
      }
    };
  },
  methods: {
    CreateBox() {
      let me = this;
      me.assignErrors(false);
      axios
        .post("api/boxes", this.formBox, this.$root.config)
        .then(function () {
          me.ResetData();
          me.$emit("list-boxes");
        })
        .catch((response) => {
          me.assignErrors(response);
        });
    },
    OpenEditBox(box) {
      let me = this;
      me.ResetData();
      $("#boxModal").modal("show");
      me.formBox = { ...box };
    },
    EditBox() {
      let me = this;
      me.assignErrors(false);
      axios
        .put("api/boxes/" + this.formBox.id, this.formBox, this.$root.config)
        .then(function () {
          me.ResetData();
          me.$emit("list-boxes");
        })
        .catch((response) => {
          me.assignErrors(response);
        });
    },
    ResetData() {
      $("#boxModal").modal("hide");
      this.formBox = {
        name: "",
        printer: ""
      };
      this.assignErrors(false);
    },
    assignErrors(response) {
      if (response) {
        var errors = response.response.data.errors;
        this.formErrors.name = errors.name ? errors.name[0] : "";
        this.formErrors.printer = errors.printer ? errors.printer[0] : "";
      } else {
        this.formErrors = {
          name: "",
          printer: ""
        };
      }
    }
  }
};
</script>

<style scoped>
/* Estilos personalizados (ajusta según tu tema) */
</style>
