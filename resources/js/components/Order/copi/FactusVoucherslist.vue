<template>
  <div class="w-100 px-4 pt-3">
    <!-- Encabezado -->
    <header class="d-flex justify-content-between align-items-center mb-3 header-bg">
      <h3 class="fw-bold mb-0 text-white">Comprobantes Dian</h3>
      <router-link
        class="btn btn-primary btn-sm"
        :to="{ name: 'create-factus-voucher' }"
        v-if="$root.validatePermission('factus.create')"
      >
        <i class="bi bi-plus-circle me-1"></i>
        Nuevo Comprobante
      </router-link>
    </header>

    <!-- Selección de Tipo de Comprobante -->
    <section class="card shadow-sm mb-4">
      <div class="card-body">
        <div class="row align-items-center">
          <div class="col-md-4">
            <label for="voucherType" class="form-label">Tipo de Comprobante</label>
            <select
              id="voucherType"
              class="form-select form-select-sm"
              v-model="voucherType"
              @change="getFactusVouchers(1)"
            >
              <option value="invoice">Factura</option>
              <option value="adjustment">Nota de Ajuste</option>
              <option value="credit">Nota de Crédito</option>
              <option value="support">Documento Soporte</option>
            </select>
          </div>
        </div>
      </div>
    </section>

    <!-- Filtros -->
    <section class="card shadow-sm mb-4">
      <div class="card-body">
        <div class="row g-3 align-items-end">
          <div class="col-12">
            <h6 class="text-secondary fw-bold">Buscar</h6>
          </div>
          <!-- Nro Factura -->
          <div class="col-sm-6 col-md-3">
            <label for="filter_number" class="form-label">Nro Factura</label>
            <input
              type="text"
              id="filter_number"
              class="form-control form-control-sm"
              placeholder="Nro Factura"
              v-model="filter.number"
            />
          </div>
          <!-- Código de Referencia -->
          <div class="col-sm-6 col-md-3">
            <label for="filter_reference" class="form-label">Código de Referencia</label>
            <input
              type="text"
              id="filter_reference"
              class="form-control form-control-sm"
              placeholder="Código de Referencia"
              v-model="filter.reference_code"
            />
          </div>
          <!-- Nombres -->
          <div class="col-sm-6 col-md-3">
            <label for="filter_names" class="form-label">Nombres</label>
            <input
              type="text"
              id="filter_names"
              class="form-control form-control-sm"
              placeholder="Nombres"
              v-model="filter.names"
            />
          </div>
          <!-- Identificación -->
          <div class="col-sm-6 col-md-3">
            <label for="filter_identification" class="form-label">Identificación</label>
            <input
              type="text"
              id="filter_identification"
              class="form-control form-control-sm"
              placeholder="Identificación"
              v-model="filter.identification"
            />
          </div>
          <!-- Email -->
          <div class="col-sm-6 col-md-3">
            <label for="filter_email" class="form-label">Email</label>
            <input
              type="text"
              id="filter_email"
              class="form-control form-control-sm"
              placeholder="Email"
              v-model="filter.email"
            />
          </div>
          <!-- DIAN (Estado) -->
          <div class="col-sm-6 col-md-3">
            <label for="filter_status" class="form-label">DIAN</label>
            <v-select
              :options="statusOptions"
              label="statusText"
              :reduce="s => s.id"
              v-model="filter.status"
            />
          </div>
          <!-- Resultados por página -->
          <div class="col-sm-6 col-md-3">
            <label for="nro_results" class="form-label">Resultados por página</label>
            <input
              type="number"
              step="any"
              id="nro_results"
              class="form-control form-control-sm"
              placeholder="Resultados"
              v-model="filter.per_page"
            />
          </div>
          <!-- Desde -->
          <div class="col-sm-6 col-md-3">
            <label for="from_date" class="form-label">Desde</label>
            <input
              type="datetime-local"
              id="from_date"
              class="form-control form-control-sm"
              v-model="filter.from"
            />
          </div>
          <!-- Hasta -->
          <div class="col-sm-6 col-md-3">
            <label for="to_date" class="form-label">Hasta</label>
            <input
              type="datetime-local"
              id="to_date"
              class="form-control form-control-sm"
              v-model="filter.to"
            />
          </div>
          <!-- Botón Buscar -->
          <div class="col-sm-6 col-md-3 text-end ms-auto">
            <button class="btn btn-success btn-sm w-100" @click="getFactusVouchers(1)">
              <i class="bi bi-search me-1"></i>
              Buscar
            </button>
          </div>
        </div>
      </div>
    </section>

    <!-- Tabla de comprobantes -->
    <section class="card shadow-sm">
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover table-striped align-middle my-custom-table mb-0">
            <thead>
              <tr>
                <th class="text-center">ID</th>
                <th class="text-center">Nro Factura</th>
                <th>Código Ref.</th>
                <th>Nombres</th>
                <th>Identificación</th>
                <th>Email</th>
                <th>Total</th>
                <th>DIAN</th>
                <th>Creado</th>
                <th>PDF</th>
                <th>XML</th>
                <th>Eliminar</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="voucher in voucherList.data" :key="voucher.id">
                <td class="text-center">{{ voucher.id }}</td>
                <td class="text-center">
                  <span class="pill-badge">{{ voucher.number }}</span>
                </td>
                <td>{{ voucher.reference_code }}</td>
                <td>{{ voucher.names }}</td>
                <td>{{ voucher.identification }}</td>
                <td>{{ voucher.email }}</td>
                <td>{{ parseFloat(voucher.total) | currency }}</td>
                <!-- Columna DIAN con colores -->
                <td>
                  <span :class="voucher.status == 1 ? 'text-success fw-bold' : 'text-danger fw-bold'">
                    {{ voucher.status == 1 ? 'Aceptado' : 'Rechazado' }}
                  </span>
                </td>
                <td>{{ voucher.created_at | moment("DD-MM-YYYY h:mm:ss a") }}</td>
                <!-- Botón PDF -->
                <td>
                  <button class="btn btn-outline-danger btn-sm" @click="downloadPdf(voucher.number)">
                    <i class="bi bi-file-earmark-pdf-fill"></i>
                  </button>
                </td>
                <!-- Botón XML -->
                <td>
                  <button class="btn btn-outline-primary btn-sm" @click="downloadXml(voucher.number)">
                    <i class="bi bi-file-earmark-fill"></i>
                  </button>
                </td>
                <!-- Botón Eliminar solo si DIAN es Rechazado -->
                <td>
                  <button
                    v-if="voucher.status != 1"
                    class="btn btn-danger btn-sm"
                    @click="deleteVoucher(voucher.reference_code)"
                  >
                    <i class="bi bi-trash"></i>
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Paginación -->
      <div class="card-footer">
        <pagination
          :align="'center'"
          :data="voucherList"
          :limit="8"
          @pagination-change-page="getFactusVouchers"
        >
          <template #prev-nav>
            <i class="bi bi-chevron-double-left"></i>
          </template>
          <template #next-nav>
            <i class="bi bi-chevron-double-right"></i>
          </template>
        </pagination>
      </div>
    </section>
  </div>
</template>

<script>
import vSelect from "vue-select";
import "vue-select/dist/vue-select.css";
import Pagination from "laravel-vue-pagination";
import moment from "moment";

export default {
  name: "FactusVoucherslist",
  components: { vSelect, Pagination },
  data() {
    return {
      voucherType: "invoice",
      voucherList: {
        data: [],
        total: 0,
        per_page: 10,
        current_page: 1,
        last_page: 1,
        from: 1,
        to: 10,
        links: []
      },
      filter: {
        number: "",
        reference_code: "",
        names: "",
        identification: "",
        email: "",
        status: null,
        per_page: 10,
        from: "",
        to: ""
      },
      statusOptions: [
        { id: 1, statusText: "Aceptado" },
        { id: 0, statusText: "Rechazado" }
      ]
    };
  },
  created() {
    this.$root.validateToken();
    this.getFactusVouchers(1);
  },
  methods: {
    getFactusVouchers(page = 1) {
      let endpoint = "";
      // Asignamos el endpoint según el tipo de comprobante
      switch (this.voucherType) {
        case "invoice":
          endpoint = "api/factus-invoice/getBills";
          break;
        case "adjustment":
          endpoint = "api/factus-adjustment-note/getAdjustmentNotes";
          break;
        case "credit":
          endpoint = "api/factus-credit-note/getCreditNotes";
          break;
        case "support":
          endpoint = "api/factus-support-document/getSupportDocuments";
          break;
        default:
          endpoint = "api/factus-invoice/getBills";
      }
      const params = {
        filter: JSON.stringify({
          number: this.filter.number,
          reference_code: this.filter.reference_code,
          names: this.filter.names,
          identification: this.filter.identification,
          email: this.filter.email,
          status: this.filter.status
        }),
        page: page,
        per_page: this.filter.per_page,
        from: this.filter.from,
        to: this.filter.to
      };
      axios
        .get(endpoint, {
          params: params,
          headers: this.$root.config.headers
        })
        .then((response) => {
          const result = response.data.data.data;
          this.voucherList = {
            data: result.data,
            total: result.pagination.total,
            per_page: result.pagination.per_page,
            current_page: result.pagination.current_page,
            last_page: result.pagination.last_page,
            from: result.pagination.from,
            to: result.pagination.to,
            links: result.pagination.links
          };
        })
        .catch((error) => {
          console.error("Error fetching vouchers:", error);
          Swal.fire({
            icon: "error",
            title: "Error",
            text: "No se pudo obtener los comprobantes."
          });
        });
    },
    getStatusText(status) {
      return status == 1 ? "Aceptado" : "Rechazado";
    },
    downloadPdf(number) {
      let endpoint = "";
      // Seleccionamos el endpoint de descarga de PDF según el tipo
      if (this.voucherType === "invoice") {
        endpoint = `api/factus-invoice/download-pdf/${number}`;
      } else if (this.voucherType === "adjustment") {
        endpoint = `api/factus-adjustment-note/download-pdf/${number}`;
      } else if (this.voucherType === "credit") {
        endpoint = `api/factus-credit-note/download-pdf/${number}`;
      } else if (this.voucherType === "support") {
        endpoint = `api/factus-support-document/download-pdf/${number}`;
      }
      axios
        .get(endpoint, this.$root.config)
        .then((response) => {
          const pdf = response.data.data.pdf_base_64_encoded;
          const fileName = response.data.data.file_name || `documento-${number}.pdf`;
          const a = document.createElement("a");
          a.href = "data:application/pdf;base64," + pdf;
          a.download = fileName;
          a.click();
        })
        .catch((error) => {
          Swal.fire({
            icon: "error",
            title: "Error",
            text: "No se pudo descargar el PDF."
          });
        });
    },
    downloadXml(number) {
      let endpoint = "";
      // Seleccionamos el endpoint de descarga de XML según el tipo
      if (this.voucherType === "invoice") {
        endpoint = `api/factus-invoice/download-xml/${number}`;
      } else if (this.voucherType === "adjustment") {
        endpoint = `api/factus-adjustment-note/download-xml/${number}`;
      } else if (this.voucherType === "credit") {
        endpoint = `api/factus-credit-note/download-xml/${number}`;
      } else if (this.voucherType === "support") {
        endpoint = `api/factus-support-document/download-xml/${number}`;
      }
      axios
        .get(endpoint, this.$root.config)
        .then((response) => {
          const xml = response.data.data.xml_base_64_encoded;
          const fileName = response.data.data.file_name || `documento-${number}.xml`;
          const a = document.createElement("a");
          a.href = "data:application/xml;base64," + xml;
          a.download = fileName;
          a.click();
        })
        .catch((error) => {
          Swal.fire({
            icon: "error",
            title: "Error",
            text: "No se pudo descargar el XML."
          });
        });
    },
    deleteVoucher(referenceCode) {
      let endpoint = "";
      // Seleccionamos el endpoint para eliminar según el tipo de comprobante
      if (this.voucherType === "invoice") {
        endpoint = `api/factus-invoice/deleteBill/${referenceCode}`;
      } else if (this.voucherType === "adjustment") {
        endpoint = `api/factus-adjustment-note/deleteBill/${referenceCode}`;
      } else if (this.voucherType === "credit") {
        endpoint = `api/factus-credit-note/deleteBill/${referenceCode}`;
      } else if (this.voucherType === "support") {
        endpoint = `api/factus-support-document/delete/${referenceCode}`;
      }
      axios
        .delete(endpoint, this.$root.config)
        .then((response) => {
          Swal.fire({
            icon: "success",
            title: "Éxito",
            text: response.data.message || "Comprobante eliminado correctamente."
          });
          this.getFactusVouchers(1);
        })
        .catch((error) => {
          Swal.fire({
            icon: "error",
            title: "Error",
            text: "No se pudo eliminar el comprobante."
          });
        });
    }
  },
  filters: {
    currency(value) {
      if (typeof value !== "number") {
        const num = parseFloat(value);
        if (isNaN(num)) return value;
        value = num;
      }
      return new Intl.NumberFormat("es-CO", {
        style: "currency",
        currency: "COP"
      }).format(value);
    },
    moment(value, formatStr) {
      return moment(value, "DD-MM-YYYY hh:mm:ss A").format(formatStr);
    }
  }
};
</script>

<style scoped>
.header-bg {
  background-color: #34BF9B;
  padding: 0.75rem 1rem;
  border-radius: 0.25rem;
}
.pill-badge {
  display: inline-block;
  padding: 0.25rem 0.75rem;
  border-radius: 50px;
  background-color: #34BF9B;
  color: #fff;
  font-weight: 600;
}
.my-custom-table tbody tr:hover {
  background-color: #f8f9fa;
}
.my-custom-table th,
.my-custom-table td {
  vertical-align: middle !important;
}
.form-label {
  font-weight: 600;
  font-size: 0.875rem;
}
</style>
