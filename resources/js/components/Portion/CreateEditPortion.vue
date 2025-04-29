<template>
  <div>
    <div
      class="modal fade"
      id="portionModal"
      tabindex="-1"
      aria-labelledby="portionModalLabel"
      aria-hidden="true"
      data-backdrop="static"
    >
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="portionModalLabel">
              {{ formPortion.id ? "Editar Porción" : "Crear Porción" }}
            </h5>
            <button
              type="button"
              class="close"
              data-dismiss="modal"
              aria-label="Close"
              @click="resetData"
            >
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <form>
              <!-- Descripción -->
              <div class="form-group">
                <label for="descriptionInput">Descripción</label>
                <input
                  type="text"
                  class="form-control"
                  id="descriptionInput"
                  placeholder="Descripción"
                  v-model="formPortion.description"
                />
                <small v-if="formErrors.description" class="text-danger">
                  {{ formErrors.description }}
                </small>
              </div>
              <!-- Cantidad: editable solo al crear -->
              <div class="form-group">
                <label for="quantityInput">Cantidad</label>
                <input
                  type="number"
                  class="form-control"
                  id="quantityInput"
                  placeholder="Cantidad"
                  v-model.number="formPortion.quantity"
                  :disabled="formPortion.id ? true : false"
                />
                <small v-if="formErrors.quantity" class="text-danger">
                  {{ formErrors.quantity }}
                </small>
              </div>
              <!-- Tipo: select -->
              <div class="form-group">
                <label for="typeInput">Tipo</label>
                <select class="form-control" id="typeInput" v-model="formPortion.type">
                  <option value="">Seleccione</option>
                  <option value="bodega">Bodega</option>
                  <option value="alacena">Alacena</option>
                </select>
                <small v-if="formErrors.type" class="text-danger">
                  {{ formErrors.type }}
                </small>
              </div>
              <!-- Estado: solo al editar, con botón deslizable -->
              <div class="form-group" v-if="formPortion.id">
                <label for="statusToggle">Estado</label>
                <br />
                <label class="switch">
                  <input
                    type="checkbox"
                    id="statusToggle"
                    v-model="formPortion.status"
                    true-value="1"
                    false-value="0"
                  />
                  <span class="slider round"></span>
                </label>
              </div>
            </form>
          </div>
          <div class="modal-footer">
            <button
              type="button"
              class="btn btn-secondary"
              data-dismiss="modal"
              @click="resetData"
            >
              Cerrar
            </button>
            <button
              type="button"
              class="btn btn-primary"
              @click="formPortion.id ? editPortion() : createPortion()"
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
      formPortion: {
        id: null,
        description: "",
        quantity: "",
        type: "",
        status: "1",
      },
      formErrors: {
        description: "",
        quantity: "",
        type: "",
      },
    };
  },
  methods: {
    createPortion() {
      this.assignErrors(false);
      axios
        .post("api/portions", this.formPortion, this.$root.config)
        .then(() => {
          $("#portionModal").modal("hide");
          this.resetData();
          this.$emit("list-portions");
        })
        .catch((response) => {
          this.assignErrors(response);
        });
    },
    openEditPortion(portion) {
      this.resetData();
      $("#portionModal").modal("show");
      // Clonar el objeto para evitar referencia directa
      this.formPortion = Object.assign({}, portion);
      // Asegurarse que status sea cadena para el toggle
      this.formPortion.status = portion.status.toString();
    },
    editPortion() {
      this.assignErrors(false);
      axios
        .put("api/portions/" + this.formPortion.id, this.formPortion, this.$root.config)
        .then(() => {
          $("#portionModal").modal("hide");
          this.resetData();
          this.$emit("list-portions");
        })
        .catch((response) => {
          this.assignErrors(response);
        });
    },
    resetData() {
      $("#portionModal").modal("hide");
      this.formPortion = {
        id: null,
        description: "",
        quantity: "",
        type: "",
        status: "1",
      };
      this.formErrors = {
        description: "",
        quantity: "",
        type: "",
      };
    },
    assignErrors(response) {
      if (response) {
        var errors = response.response.data.errors;
        this.formErrors.description = errors.description ? errors.description[0] : "";
        this.formErrors.quantity = errors.quantity ? errors.quantity[0] : "";
        this.formErrors.type = errors.type ? errors.type[0] : "";
      } else {
        this.formErrors = {
          description: "",
          quantity: "",
          type: "",
        };
      }
    },
  },
};
</script>

<style scoped>
/* Estilos para el toggle switch */
.switch {
  position: relative;
  display: inline-block;
  width: 50px;
  height: 24px;
}
.switch input {
  opacity: 0;
  width: 0;
  height: 0;
}
.slider {
  position: absolute;
  cursor: pointer;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: #ccc;
  transition: .4s;
  border-radius: 24px;
}
.slider:before {
  position: absolute;
  content: "";
  height: 18px;
  width: 18px;
  left: 3px;
  bottom: 3px;
  background-color: white;
  transition: .4s;
  border-radius: 50%;
}
input:checked + .slider {
  background-color: #28a745;
}
input:focus + .slider {
  box-shadow: 0 0 1px #28a745;
}
input:checked + .slider:before {
  transform: translateX(26px);
}
</style>
