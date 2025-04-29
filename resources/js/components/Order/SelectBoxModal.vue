<template>
  <div
    class="modal fade"
    id="selectBoxModal"
    tabindex="-1"
    aria-labelledby="selectBoxModalLabel"
    aria-hidden="true"
    data-backdrop="static"
  >
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="selectBoxModalLabel">
            Seleccionar Caja y Comprobante
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
        <div class="modal-body">
          <!-- Selección de Caja -->
          <div class="form-group">
            <label for="boxSelect">Caja</label>
            <select id="boxSelect" class="form-control" v-model="selectedBoxId" @change="onBoxChange">
              <option value="" disabled>Seleccione una caja</option>
              <option v-for="box in uniqueBoxes" :key="box.id" :value="box.id">
                {{ box.name }}
              </option>
            </select>
          </div>
          <!-- Selección de Comprobante -->
          <div class="form-group" v-if="selectedBox && selectedBox.numbering_ranges && selectedBox.numbering_ranges.length">
            <label for="voucherSelect">Comprobante</label>
            <select id="voucherSelect" class="form-control" v-model="selectedVoucherId">
              <option value="" disabled>Seleccione un comprobante</option>
              <option
                v-for="voucher in selectedBox.numbering_ranges"
                :key="voucher.id"
                :value="voucher.id"
              >
                {{ voucher.document }} - {{ voucher.prefix }}
              </option>
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button
            type="button"
            class="btn btn-secondary"
            data-dismiss="modal"
            @click="resetData"
          >
            Cancelar
          </button>
          <button type="button" class="btn btn-primary" @click="selectBox">
            Guardar
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: "SelectBoxModal",
  data() {
    return {
      selectedBoxId: "",
      selectedVoucherId: ""
    };
  },
  computed: {
    uniqueBoxes() {
      // Se asume que $root.listBoxes ya contiene las cajas (únicas)
      return this.$root.listBoxes || [];
    },
    selectedBox() {
      if (!this.selectedBoxId) return null;
      return this.$root.listBoxes.find(box => box.id == this.selectedBoxId) || null;
    }
  },
  methods: {
    openModal() {
      this.selectedBoxId = "";
      this.selectedVoucherId = "";
      $("#selectBoxModal").modal("show");
    },
    onBoxChange() {
      if (this.selectedBox && this.selectedBox.numbering_ranges && this.selectedBox.numbering_ranges.length) {
        // Se asigna el primer comprobante como default si no se selecciona ninguno manualmente
        this.selectedVoucherId = this.selectedBox.numbering_ranges[0].id;
      } else {
        this.selectedVoucherId = "";
      }
    },
    selectBox() {
      if (!this.selectedBoxId) return;
      if (!this.selectedVoucherId && this.selectedBox && this.selectedBox.numbering_ranges && this.selectedBox.numbering_ranges.length) {
        this.selectedVoucherId = this.selectedBox.numbering_ranges[0].id;
      }
      // Almacenar en localStorage
      localStorage.setItem("box_id", this.selectedBoxId);
      localStorage.setItem("selected_voucher", this.selectedVoucherId);
      // Emitir el evento con ambos valores
      this.$emit("box-selected", { boxId: this.selectedBoxId, voucherId: this.selectedVoucherId });
      $("#selectBoxModal").modal("hide");
    },
    resetData() {
      this.selectedBoxId = "";
      this.selectedVoucherId = "";
      $("#selectBoxModal").modal("hide");
    }
  }
};
</script>

<style scoped>
/* Agrega estilos si lo necesitas */
</style>
