<template>
  <div>
    <!-- Modal para asignar comprobantes -->
    <div
      class="modal fade"
      id="assignVouchersModal"
      tabindex="-1"
      aria-labelledby="assignVouchersModalLabel"
      aria-hidden="true"
      data-backdrop="static"
    >
      <div class="modal-dialog">
        <div class="modal-content">
          <!-- Encabezado -->
          <div class="modal-header">
            <h5 class="modal-title" id="assignVouchersModalLabel">
              Asignar Comprobantes
            </h5>
            <button
              type="button"
              class="close"
              data-dismiss="modal"
              aria-label="Cerrar"
              @click="resetData"
            >
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <!-- Cuerpo del Modal -->
          <div class="modal-body">
            <!-- Selector para elegir el tipo de comprobante -->
            <div class="form-group">
              <label for="voucherType">Tipo de comprobante</label>
              <select
                id="voucherType"
                class="form-control"
                v-model="documentType"
                @change="fetchVouchers"
              >
                <option value="Factura de Venta">Factura de Venta</option>
                <option value="Pedido">Pedido</option>
                <!-- Puedes agregar más opciones según sea necesario -->
              </select>
            </div>
            <form id="formAssignVouchers">
              <h6 class="mb-3">Listado de comprobantes activos</h6>
              <div
                v-for="voucher in vouchers"
                :key="voucher.id"
                class="d-flex align-items-center justify-content-between mb-2"
              >
                <!-- Información del comprobante -->
                <div>
                  {{ voucher.document }} - {{ voucher.prefix }}
                </div>
                <!-- Botón deslizable -->
                <label class="switch">
                  <input
                    type="checkbox"
                    :checked="selectedVouchers.includes(voucher.id)"
                    @change="toggleVoucher(voucher.id)"
                  />
                  <span class="slider round"></span>
                </label>
              </div>
            </form>
          </div>
          <!-- Pie del Modal -->
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
              @click="assignVouchers"
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
  name: "AssignVouchers",
  data() {
    return {
      box: {},
      vouchers: [],
      selectedVouchers: [],
      // Variable para almacenar el tipo de comprobante a solicitar
      documentType: "Factura de Venta"
    };
  },
  methods: {
    /**
     * Abre el modal para asignar comprobantes.
     * Se obtienen los IDs de comprobantes asignados a la caja y luego se carga
     * el listado de comprobantes activos según el tipo seleccionado.
     */
    openAssignVouchers(box) {
      this.box = box;
      this.selectedVouchers = [];
      // Primero obtenemos los comprobantes asignados a la caja
      axios
        .get("api/boxes/" + box.id + "/assigned-vouchers", this.$root.config)
        .then((response) => {
          // Se asume que la respuesta es un array de IDs
          this.selectedVouchers = response.data;
          // Establecemos un valor por defecto en el selector (puede ajustarse según tus requerimientos)
          this.documentType = "Factura de Venta";
          // Obtenemos el listado de comprobantes según el tipo seleccionado
          this.fetchVouchers();
        })
        .catch((error) => {
          console.error(error);
        });
    },
    /**
     * Obtiene el listado de comprobantes activos según el tipo (documentType)
     */
    fetchVouchers() {
      axios
        .get("api/numbering_ranges", {
          params: {
            document: this.documentType,
            is_active: 1
          },
          ...this.$root.config
        })
        .then((response) => {
          this.vouchers = response.data;
          $("#assignVouchersModal").modal("show");
        })
        .catch((error) => {
          console.error(error);
        });
    },
    /**
     * Alterna el ID de un comprobante en el array selectedVouchers.
     */
    toggleVoucher(voucherId) {
      const index = this.selectedVouchers.indexOf(voucherId);
      if (index >= 0) {
        // Si ya está seleccionado, lo removemos
        this.selectedVouchers.splice(index, 1);
      } else {
        // Sino, lo agregamos
        this.selectedVouchers.push(voucherId);
      }
    },
    /**
     * Envía los IDs de comprobantes seleccionados al endpoint de asignación.
     * Se espera que el endpoint sea: POST api/boxes/{id}/assign-vouchers
     */
    assignVouchers() {
      if (!this.box.id) return;
      axios
        .post(
          `api/boxes/${this.box.id}/assign-vouchers`,
          { numbering_ranges_ids: this.selectedVouchers },
          this.$root.config
        )
        .then((response) => {
          $("#assignVouchersModal").modal("hide");
          this.resetData();
          this.$emit("refreshList");
        })
        .catch((error) => {
          console.error(error);
        });
    },
    /**
     * Resetea los datos y cierra el modal.
     */
    resetData() {
      this.box = {};
      this.vouchers = [];
      this.selectedVouchers = [];
      $("#assignVouchersModal").modal("hide");
    }
  }
};
</script>

<style scoped>
/* Estilos para el toggle switch */
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
  transition: 0.4s;
}
.slider:before {
  position: absolute;
  content: "";
  height: 18px;
  width: 18px;
  left: 3px;
  bottom: 3px;
  background-color: #fff;
  transition: 0.4s;
  border-radius: 50%;
}
input:checked + .slider {
  background-color: #28a745;
}
input:focus + .slider {
  box-shadow: 0 0 1px #28a745;
}
input:checked + .slider:before {
  transform: translateX(22px);
}
</style>
