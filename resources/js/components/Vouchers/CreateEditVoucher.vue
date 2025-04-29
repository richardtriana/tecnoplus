<template>
    <div>
      <div
        class="modal fade"
        id="voucherModal"
        tabindex="-1"
        aria-labelledby="voucherModalLabel"
        aria-hidden="true"
        data-backdrop="static"
      >
        <div class="modal-dialog">
          <div class="modal-content">
            <!-- Encabezado del modal -->
            <div class="modal-header">
              <h5 class="modal-title" id="voucherModalLabel">
                {{ form.id ? "Editar Comprobante" : "Crear Comprobante" }}
              </h5>
              <button
                type="button"
                class="close"
                data-dismiss="modal"
                aria-label="Close"
                @click="resetForm"
              >
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
  
            <!-- Cuerpo del modal -->
            <div class="modal-body">
              <form>
                <!-- Documento: solo lectura -->
                <div class="form-group">
                  <label for="documentInput">Documento</label>
                  <input
                    type="text"
                    id="documentInput"
                    class="form-control"
                    v-model="form.document"
                    disabled
                  />
                </div>
                <!-- Prefijo -->
                <div class="form-group">
                  <label for="prefixInput">Prefijo</label>
                  <input
                    type="text"
                    id="prefixInput"
                    class="form-control"
                    v-model="form.prefix"
                    placeholder="Prefijo"
                  />
                  <small v-if="errors.prefix" class="text-danger">
                    {{ errors.prefix }}
                  </small>
                </div>
                <!-- Desde -->
                <div class="form-group">
                  <label for="fromInput">Desde</label>
                  <input
                    type="number"
                    id="fromInput"
                    class="form-control"
                    v-model.number="form.from"
                    placeholder="Desde"
                  />
                  <small v-if="errors.from" class="text-danger">
                    {{ errors.from }}
                  </small>
                </div>
                <!-- Hasta -->
                <div class="form-group">
                  <label for="toInput">Hasta</label>
                  <input
                    type="number"
                    id="toInput"
                    class="form-control"
                    v-model.number="form.to"
                    placeholder="Hasta"
                  />
                  <small v-if="errors.to" class="text-danger">
                    {{ errors.to }}
                  </small>
                </div>
                <!-- Actual -->
                <div class="form-group">
                  <label for="currentInput">Actual</label>
                  <input
                    type="number"
                    id="currentInput"
                    class="form-control"
                    v-model.number="form.current"
                    placeholder="Actual"
                  />
                  <small v-if="errors.current" class="text-danger">
                    {{ errors.current }}
                  </small>
                </div>
  
                <!-- Toggle switch para "Enviado DIAN" -->
                <div class="form-group">
                  <label for="enviadoDianToggle">Enviado DIAN</label>
                  <br />
                  <label class="switch">
                    <input
                      type="checkbox"
                      id="enviadoDianToggle"
                      v-model="form.enviado_dian"
                    />
                    <span class="slider round"></span>
                  </label>
                </div>
              </form>
            </div>
  
            <!-- Pie del modal -->
            <div class="modal-footer">
              <button
                type="button"
                class="btn btn-secondary"
                data-dismiss="modal"
                @click="resetForm"
              >
                Cerrar
              </button>
              <button
                type="button"
                class="btn btn-primary"
                @click="form.id ? updateVoucher() : createVoucher()"
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
  import axios from "axios";
  
  export default {
    name: "CreateEditVoucher",
    data() {
      return {
        form: {
          id: null,
          document: "",
          prefix: "",
          from: "",
          to: "",
          current: "",
          enviado_dian: false,
        },
        errors: {},
      };
    },
    methods: {
      // Abre el modal en modo edición
      openEdit(voucher) {
        this.resetForm();
        // Se copian todos los campos, incluyendo enviado_dian
        this.form = { ...voucher };
        $("#voucherModal").modal("show");
      },
      // Abre el modal en modo creación y asigna el tipo de documento
      openCreate(voucherType) {
        this.resetForm();
        this.form.document = voucherType;
        $("#voucherModal").modal("show");
      },
      createVoucher() {
        axios
          .post("api/numbering_ranges", this.form)
          .then(() => {
            $("#voucherModal").modal("hide");
            this.resetForm();
            this.$emit("refreshList");
          })
          .catch((error) => {
            this.errors = error.response.data.errors || {};
          });
      },
      updateVoucher() {
        axios
          .put("api/numbering_ranges/" + this.form.id, this.form)
          .then(() => {
            $("#voucherModal").modal("hide");
            this.resetForm();
            this.$emit("refreshList");
          })
          .catch((error) => {
            this.errors = error.response.data.errors || {};
          });
      },
      resetForm() {
        this.form = {
          id: null,
          document: "",
          prefix: "",
          from: "",
          to: "",
          current: "",
          enviado_dian: false,
        };
        this.errors = {};
      },
    },
  };
  </script>
  
  <style scoped>
  /* Estilos para el toggle switch (botón deslizable) */
  .switch {
    position: relative;
    display: inline-block;
    width: 46px;
    height: 24px;
    vertical-align: middle;
  }
  .switch input {
    opacity: 0;
    width: 0;
    height: 0;
  }
  .slider {
    cursor: pointer;
    background-color: #ccc;
    border-radius: 24px;
    position: absolute;
    top: 0;
    bottom: 0;
    left: 0;
    right: 0;
    transition: .4s;
  }
  .slider:before {
    position: absolute;
    content: "";
    height: 18px;
    width: 18px;
    left: 3px;
    bottom: 3px;
    background-color: #fff;
    transition: .4s;
    border-radius: 50%;
  }
  input:checked + .slider {
    background-color: #28a745; /* Verde */
  }
  input:focus + .slider {
    box-shadow: 0 0 1px #28a745;
  }
  input:checked + .slider:before {
    transform: translateX(22px);
  }
  </style>
  