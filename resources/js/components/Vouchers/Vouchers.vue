<template>
  <div class="page">
    <!-- Encabezado de la página -->
    <div class="page-header mb-3">
      <div class="row">
        <div class="col">
          <h3 class="page-title">Comprobantes</h3>
        </div>
        <div class="col text-right">
          <!-- Botón para crear un nuevo voucher -->
          <button
            type="button"
            class="btn btn-outline-primary"
            @click="openCreateModal"
            :disabled="!selectedVoucherType"
          >
            <i class="bi bi-plus-circle"></i> Crear Comprobante
          </button>
        </div>
      </div>
    </div>

    <!-- Tarjeta de filtros -->
    <div class="card p-3 mb-3">
      <h5 class="mb-3">Filtro por Tipo de Comprobantes</h5>
      <div class="form-row">
        <div class="col-md-4 mb-2">
          <label for="voucherTypeSelect">Tipo de Comprobante</label>
          <select
            id="voucherTypeSelect"
            class="form-control"
            v-model="selectedVoucherType"
            @change="fetchVouchers"
          >
            <option value="">Seleccione un tipo</option>
            <option
              v-for="type in voucherTypes"
              :key="type.id"
              :value="type.description"
            >
              {{ type.description }}
            </option>
          </select>
        </div>
      </div>
    </div>

    <!-- Listado de vouchers -->
    <div class="card p-3">
      <h5 class="mb-3">Listado de Comprobantes</h5>
      <div class="table-responsive">
        <table class="table table-sm table-bordered table-hover">
          <thead class="thead-light">
            <tr>
              <th>#</th>
              <th>Documento</th>
              <th>Prefijo</th>
              <th>Desde</th>
              <th>Hasta</th>
              <th>Actual</th>
              <th>Enviado DIAN</th>
              <th>Opciones</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="voucher in vouchers" :key="voucher.id">
              <td>{{ voucher.id }}</td>
              <td>{{ voucher.document }}</td>
              <td>{{ voucher.prefix }}</td>
              <td>{{ voucher.from }}</td>
              <td>{{ voucher.to }}</td>
              <td>{{ voucher.current }}</td>
              <td class="align-middle">
                <!-- Toggle deslizable para Enviado DIAN -->
                <label class="switch">
                  <input
                    type="checkbox"
                    :checked="voucher.enviado_dian"
                    @change="toggleEnviadoDian(voucher)"
                  />
                  <span class="slider round"></span>
                </label>
              </td>
              <td class="align-middle">
                <button
                  class="btn btn-outline-info btn-sm"
                  @click="openEditModal(voucher)"
                >
                  <i class="bi bi-pencil-square"></i> Editar
                </button>
              </td>
            </tr>
            <tr v-if="vouchers.length === 0">
              <td colspan="8" class="text-center">No se encontraron registros.</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Componente Modal para crear/editar voucher -->
    <CreateEditVoucher ref="createEditVoucher" @refreshList="fetchVouchers" />
  </div>
</template>

<script>
import axios from "axios";
import CreateEditVoucher from "./CreateEditVoucher.vue";

export default {
  name: "Vouchers",
  components: {
    CreateEditVoucher,
  },
  data() {
    return {
      voucherTypes: [],
      selectedVoucherType: "",
      vouchers: [],
    };
  },
  created() {
    this.fetchVoucherTypes();
  },
  methods: {
    // Obtiene la lista de tipos de voucher
    fetchVoucherTypes() {
      axios
        .get("api/numbering_range_document_types")
        .then((response) => {
          this.voucherTypes = response.data;
        })
        .catch((error) => console.error(error));
    },
    // Consulta los vouchers (numbering_ranges) filtrados por el tipo seleccionado
    fetchVouchers() {
      if (!this.selectedVoucherType) {
        this.vouchers = [];
        return;
      }
      axios
        .get("api/numbering_ranges", {
          params: { document: this.selectedVoucherType },
        })
        .then((response) => {
          this.vouchers = response.data;
        })
        .catch((error) => console.error(error));
    },
    // Abre el modal en modo edición
    openEditModal(voucher) {
      this.$refs.createEditVoucher.openEdit(voucher);
    },
    // Abre el modal en modo creación
    openCreateModal() {
      this.$refs.createEditVoucher.openCreate(this.selectedVoucherType);
    },
    // Alterna el valor de "enviado_dian"
    toggleEnviadoDian(voucher) {
      const updatedData = {
        document: voucher.document,
        prefix: voucher.prefix,
        from: voucher.from,
        to: voucher.to,
        current: voucher.current,
        enviado_dian: !voucher.enviado_dian, // Cambia el valor booleano
      };
      axios
        .put(`api/numbering_ranges/${voucher.id}`, updatedData)
        .then((response) => {
          voucher.enviado_dian = response.data.data.enviado_dian;
        })
        .catch((error) => console.error(error));
    },
  },
};
</script>

<style scoped>
/* Toggle Switch (deslizable) */
.switch {
  position: relative;
  display: inline-block;
  width: 46px;
  height: 24px;
  vertical-align: middle;
  margin: 0;
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
  background-color: #28a745;
}
input:focus + .slider {
  box-shadow: 0 0 1px #28a745;
}
input:checked + .slider:before {
  transform: translateX(22px);
}
</style>
