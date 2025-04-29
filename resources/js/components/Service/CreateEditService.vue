<template>
  <div class="container">
    <div class="row justify-content-center">
      <form>
        <!-- Campo para el código del servicio -->
        <div class="form-group">
          <label for="codigo">Código</label>
          <input
            type="text"
            class="form-control"
            id="codigo"
            placeholder="Ingresar código del servicio"
            v-model="formService.codigo"
          />
          <small class="form-text text-danger">{{ formErrors.codigo }}</small>
        </div>
        <!-- Campo para el nombre del servicio -->
        <div class="form-group">
          <label for="name">Servicio</label>
          <input
            type="text"
            class="form-control"
            id="name"
            placeholder="Ingresar nombre del servicio"
            v-model="formService.name"
          />
          <small class="form-text text-danger">{{ formErrors.name }}</small>
        </div>
        <!-- Campo toggle para el estado activo -->
        <div class="form-group">
          <label for="activeToggle">Activo</label>
          <br />
          <label class="switch">
            <input
              type="checkbox"
              id="activeToggle"
              v-model="formService.active"
            />
            <span class="slider round"></span>
          </label>
        </div>
      </form>
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      formService: {
        id: null,
        codigo: "",
        name: "",
        active: true
      },
      formErrors: {
        codigo: "",
        name: ""
      }
    };
  },
  methods: {
    CreateService() {
      this.assignErrors(false);
      axios
        .post("api/services", this.formService, this.$root.config)
        .then(() => {
          $("#serviceModal").modal("hide");
          this.ResetData();
          this.$emit("list-services");
        })
        .catch((response) => {
          this.assignErrors(response);
        });
    },
    OpenEditService(service) {
      this.ResetData();
      $("#serviceModal").modal("show");
      // Asignamos los datos del servicio, convirtiendo active a booleano
      this.formService = {
        id: service.id,
        codigo: service.codigo,
        name: service.name,
        active: service.active == 1
      };
    },
    EditService() {
      this.assignErrors(false);
      axios
        .put("api/services/" + this.formService.id, this.formService, this.$root.config)
        .then(() => {
          $("#serviceModal").modal("hide");
          this.ResetData();
          this.$emit("list-services");
        })
        .catch((response) => {
          this.assignErrors(response);
        });
    },
    ResetData() {
      $("#serviceModal").modal("hide");
      this.formService = {
        id: null,
        codigo: "",
        name: "",
        active: true
      };
      this.assignErrors(false);
    },
    assignErrors(response) {
      if (response) {
        const errors = response.response?.data?.errors || {};
        this.formErrors.codigo = errors.codigo ? errors.codigo[0] : "";
        this.formErrors.name = errors.name ? errors.name[0] : "";
      } else {
        this.formErrors.codigo = "";
        this.formErrors.name = "";
      }
    }
  }
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
