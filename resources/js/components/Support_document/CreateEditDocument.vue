<template>
  <div class="create-edit-document container-fluid">
    <!-- Overlay de Loading -->
    <div v-if="loading" class="loading-overlay">
      <div class="spinner-border text-primary" role="status">
        <span class="sr-only">Cargando...</span>
      </div>
      <p>Cargando, por favor espere...</p>
    </div>

    <!-- ENCABEZADO AZUL -->
    <div class="row bg-primary text-white mb-3 p-3 align-items-center">
      <div class="col-8">
        <h2 class="m-0">
          Documento Soporte
          <span v-if="selectedNumberingRange">
            - {{ selectedNumberingRange.prefix }}{{ selectedNumberingRange.current }}
          </span>
        </h2>
      </div>
      <div class="col-4 text-right">
        <h1 class="m-0">TOTAL $ {{ formattedTotal }}</h1>
      </div>
    </div>

    <!-- Estado de envío a DIAN (opcional) -->
    <div class="row">
      <div class="col-12 text-right mb-2">
        <span class="badge badge-info p-2 h5">
          Estado de envío a DIAN:
          <strong v-if="selectedNumberingRange && selectedNumberingRange.enviado_dian">
            Se enviará como Documento Soporte
          </strong>
          <strong v-else>No se enviará a DIAN</strong>
        </span>
      </div>
    </div>

    <!-- CONTENIDO PRINCIPAL -->
    <div class="row">
      <!-- Sección de Servicios y Proveedor -->
      <div class="col-9">
        <!-- Proveedor -->
        <div class="d-flex justify-content-between align-items-center mb-2">
          <div>
            <h5 class="mb-0">Proveedor seleccionado</h5>
            <p v-if="!selectedSupplier" class="text-muted mb-1">(No hay proveedor seleccionado)</p>
            <button class="btn btn-outline-secondary btn-sm" @click="openSelectSupplierModal">
              <i class="bi bi-arrow-repeat"></i> Agregar Proveedor
            </button>
          </div>
        </div>

        <!-- Detalle del Proveedor -->
        <div v-if="selectedSupplier" class="card mb-3">
          <div class="card-body p-2">
            <h5 class="card-title mb-2">Datos del Proveedor</h5>
            <table class="table table-sm table-bordered mb-0">
              <tbody>
                <tr>
                  <th style="width: 150px;">Razón Social</th>
                  <td>{{ selectedSupplier.razon_social || '...' }}</td>
                </tr>
                <tr>
                  <th>Documento</th>
                  <td>
                    {{ selectedSupplier.document }}
                    <span v-if="selectedSupplier.div_verification">
                      - {{ selectedSupplier.div_verification }}
                    </span>
                  </td>
                </tr>
                <tr>
                  <th>Dirección</th>
                  <td>{{ selectedSupplier.address || '...' }}</td>
                </tr>
                <tr>
                  <th>Teléfono</th>
                  <td>{{ selectedSupplier.phone || '...' }}</td>
                </tr>
                <tr>
                  <th>Email</th>
                  <td>{{ selectedSupplier.email || '...' }}</td>
                </tr>
                <tr>
                  <th>Ciudad</th>
                  <td>
                    {{ selectedSupplier.municipality?.name || '...' }}
                    <span v-if="selectedSupplier.municipality?.department">
                      - {{ selectedSupplier.municipality.department }}
                    </span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Buscador de Servicio -->
        <div class="row mb-2">
          <div class="col-8">
            <div class="input-group">
              <input
                type="text"
                class="form-control"
                placeholder="Buscar Servicio por código"
                v-model="filters.service"
                @keypress.enter="searchService"
              />
              <div class="input-group-append">
                <button class="btn btn-outline-secondary" type="button" @click="searchService">
                  Buscar
                </button>
              </div>
            </div>
          </div>
          <div class="col-4 text-right">
            <button class="btn btn-primary" @click="openAddServiceModal">
              F10 Añadir Servicio
            </button>
          </div>
        </div>

        <!-- Tabla de Servicios agregados -->
        <section>
          <table class="table table-sm table-bordered table-hover">
            <thead class="bg-secondary text-white">
              <tr>
                <th style="width: 50px;"></th>
                <th>Código</th>
                <th>Servicio</th>
                <th>Cantidad</th>
                <th>Precio</th>
                <th>Unidad</th>
                <th>Impuesto</th>
                <th>Total</th>
              </tr>
            </thead>
            <tbody v-if="servicesList.length > 0">
              <tr v-for="(s, index) in servicesList" :key="s.tempId">
                <td>
                  <button class="btn text-danger" @click="removeService(index)">
                    <i class="bi bi-trash"></i>
                  </button>
                  <button class="btn text-primary" @click="toggleObservations(index)">
                    <i class="bi bi-pencil-square"></i>
                  </button>
                </td>
                <td>{{ s.codigo || '-' }}</td>
                <td>{{ s.name }}</td>
                <td style="max-width: 70px;">
                  <input type="number" step="any" class="form-control form-control-sm" v-model.number="s.quantity" />
                </td>
                <td style="max-width: 100px;">
                  <input type="number" step="any" class="form-control form-control-sm" v-model.number="s.price" />
                </td>
                <!-- Columna Unidad de Medida -->
                <td>
                  <select class="form-control form-control-sm" v-model="s.measurement_unit_id">
                    <option v-for="mu in measurementUnits" :key="mu.id" :value="mu.id">
                      {{ mu.name }}
                    </option>
                  </select>
                </td>
                <!-- Columna Impuesto -->
                <td>
                  <select class="form-control form-control-sm" v-model="s.tax_id">
                    <option v-for="tax in taxes" :key="tax.id" :value="tax.id">
                      {{ tax.name }} ({{ tax.percentage }}%)
                    </option>
                  </select>
                </td>
                <td class="text-right">
                  {{ (s.quantity * s.price).toFixed(2).toLocaleString('es-CO') }}
                </td>
              </tr>
              <!-- Fila para observaciones -->
              <tr v-for="(s, i) in servicesList" :key="'obs-' + s.tempId" v-if="s.showObservations">
                <td colspan="8">
                  <input type="text" class="form-control" placeholder="Observaciones del servicio" v-model="s.observations" />
                </td>
              </tr>
            </tbody>
            <tbody v-else>
              <tr>
                <td colspan="8" class="text-center">No se han añadido servicios</td>
              </tr>
            </tbody>
          </table>
        </section>
      </div>

      <!-- Panel lateral -->
      <div class="col-3">
        <div class="card mb-3">
          <div class="card-header bg-primary text-white">
            <strong>Resumen</strong>
          </div>
          <div class="card-body p-2">
            <table class="table table-sm m-0 text-right">
              <tbody>
                <tr>
                  <th>Subtotal:</th>
                  <td>$ {{ formattedSubtotal }}</td>
                </tr>
                <tr>
                  <th>Total:</th>
                  <td>$ {{ formattedTotal }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Selección de Numbering Range -->
        <div class="form-group">
          <label>Numbering Range</label>
          <select class="form-control" v-model="document.numbering_range_id">
            <option v-for="nr in numberingRanges" :key="nr.id" :value="nr.id">
              {{ nr.document }} - {{ nr.prefix }}
            </option>
          </select>
        </div>

        <!-- Selección de Payment Method -->
        <div class="form-group">
          <label>Payment Method</label>
          <select class="form-control" v-model="document.payment_method_code">
            <option v-for="pm in paymentMethods" :key="pm.id" :value="pm.code">
              {{ pm.name }}
            </option>
          </select>
        </div>

        <!-- Botón de acción: Guardar Documento -->
        <button class="btn btn-primary btn-block mb-2" @click="saveDocument">
          <i class="bi bi-file-earmark-check"></i> Guardar Documento
        </button>
        <router-link to="/support-documents" class="btn btn-secondary btn-block">
          <i class="bi bi-x-circle"></i> Cancelar
        </router-link>
      </div>
    </div>

    <!-- Componentes adicionales -->
    <add-service @add-service="addService" />
    <select-supplier-modal ref="SelectSupplierModal" @supplier-selected="onSupplierSelected" />
  </div>
</template>

<script>
import AddService from "./AddService.vue";
import SelectSupplierModal from "./SelectSupplierModal.vue";
import axios from "axios";
import Swal from "sweetalert2";

export default {
  name: "CreateEditDocument",
  components: {
    AddService,
    SelectSupplierModal,
  },
  props: ["document_id"],
  data() {
    return {
      loading: false,
      filters: {
        service: "",
      },
      servicesList: [],
      document: {
        provider_id: null, // Se relaciona con la tabla de proveedores
        supplier: "Sin Proveedor", // Solo para mostrar el nombre en la vista
        reference: "",
        observations: "",
        total: 0.0,
        services: [],
        numbering_range_id: null,
        payment_method_code: "10",
      },
      selectedSupplier: null,
      disabled: false,
      numberingRanges: [],
      paymentMethods: [],
      taxes: [],
      measurementUnits: [],
      savedDocument: null, // Aquí se guardará la respuesta del API
    };
  },
  computed: {
    subtotal() {
      return this.servicesList.reduce((acc, s) => acc + (s.quantity * s.price), 0);
    },
    total() {
      return this.subtotal;
    },
    formattedSubtotal() {
      return this.subtotal.toLocaleString("es-CO", { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    },
    formattedTotal() {
      return this.total.toLocaleString("es-CO", { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    },
    selectedNumberingRange() {
      return this.numberingRanges.find((nr) => nr.id === this.document.numbering_range_id) || null;
    },
    // Genera el reference_code a partir del prefix y current del comprobante seleccionado
    generatedReferenceCode() {
      if (this.selectedNumberingRange && this.selectedNumberingRange.prefix && this.selectedNumberingRange.current) {
        return this.selectedNumberingRange.prefix + this.selectedNumberingRange.current;
      }
      return "";
    },
  },
  mounted() {
    if (this.document_id && this.document_id != 0) {
      this.loadDocument();
    }
    this.commands();
    this.loadNumberingRanges();
    this.loadPaymentMethods();
    this.loadTaxes();
    this.loadMeasurementUnits();
  },
  methods: {
    loadNumberingRanges() {
      axios
        .get("api/numbering_ranges", this.$root.config)
        .then((res) => {
          this.numberingRanges = res.data.filter((nr) => nr.document === "Documento Soporte");
          if (!this.document.numbering_range_id && this.numberingRanges.length) {
            this.document.numbering_range_id = this.numberingRanges[0].id;
          }
        })
        .catch((err) => console.error("Error al cargar numbering ranges:", err));
    },
    loadPaymentMethods() {
      axios
        .get("api/payment-methods", this.$root.config)
        .then((res) => {
          this.paymentMethods = res.data.payment_methods || [];
          if (!this.document.payment_method_code && this.paymentMethods.length) {
            this.document.payment_method_code = this.paymentMethods[0].code;
          }
        })
        .catch((err) => console.error("Error al cargar payment methods:", err));
    },
    loadTaxes() {
      axios
        .get("api/taxes", this.$root.config)
        .then((res) => {
          this.taxes = (res.data.taxes && res.data.taxes.data) || [];
        })
        .catch((err) => console.error("Error al cargar taxes:", err));
    },
    loadMeasurementUnits() {
      axios
        .get("api/measurement-units", this.$root.config)
        .then((res) => {
          this.measurementUnits = res.data || [];
        })
        .catch((err) => console.error("Error al cargar measurement units:", err));
    },
    openSelectSupplierModal() {
      this.$refs.SelectSupplierModal.openModal();
    },
    onSupplierSelected(supplier) {
      this.selectedSupplier = supplier;
      this.document.provider_id = supplier.id;
      this.document.supplier = supplier.razon_social || supplier.first_name || "Proveedor sin nombre";
    },
    searchService() {
      if (!this.filters.service) return;
      axios
        .get("api/services", {
          params: { page: 1, search: this.filters.service },
          ...this.$root.config,
        })
        .then((response) => {
          const services = response.data.services.data;
          if (services && services.length > 0) {
            this.addService(services[0]);
          } else {
            Swal.fire({
              icon: "warning",
              title: "Servicio no encontrado",
              showConfirmButton: false,
              timer: 1500,
            });
          }
          this.filters.service = "";
        })
        .catch((err) => {
          console.error("Error al buscar servicio:", err);
          Swal.fire({
            icon: "error",
            title: "Error al buscar servicio",
            text: err.message,
          });
        });
    },
    openAddServiceModal() {
      this.filters.service = "";
      $("#addServiceModal").modal("show");
    },
    addService(new_service) {
      const tempId = Date.now() + Math.random();
      this.servicesList.push({
        tempId: tempId,
        service_id: new_service.id,
        codigo: new_service.codigo || "",
        name: new_service.name,
        quantity: 1,
        price: new_service.price || 0,
        measurement_unit_id: this.measurementUnits.length ? this.measurementUnits[0].id : null,
        tax_id: this.taxes.length ? this.taxes[0].id : null,
        showObservations: false,
        observations: "",
      });
    },
    removeService(index) {
      this.servicesList.splice(index, 1);
    },
    toggleObservations(index) {
      this.servicesList[index].showObservations = !this.servicesList[index].showObservations;
    },
    saveDocument() {
      if (this.disabled) return;
      if (!this.servicesList.length) {
        Swal.fire({
          icon: "warning",
          title: "Debes añadir servicios al documento",
        });
        return;
      }
      if (!this.selectedSupplier) {
        Swal.fire({
          icon: "warning",
          title: "Debes seleccionar un proveedor",
        });
        return;
      }
      // Asignar el reference_code generado si no se envía
      if (!this.document.reference_code) {
        this.document.reference_code = this.generatedReferenceCode;
      }
      this.disabled = true;
      this.loading = true;
      this.document.services = this.servicesList;
      this.document.total = this.total;
      axios
        .post("api/support-document", this.document, this.$root.config)
        .then((response) => {
          Swal.fire({
            icon: "success",
            title: "Documento guardado correctamente",
          });
          // Guardar la respuesta en una propiedad para conservarla en la vista
          this.savedDocument = response.data.document;
          // Opcional: actualizar this.document con la respuesta del API
          this.document = response.data.document;
        })
        .catch((err) => {
          console.error(err);
          Swal.fire({
            icon: "error",
            title: "Error al guardar el documento",
            text: err.message,
          });
        })
        .finally(() => {
          this.resetForm();
        });
    },
    loadDocument() {
      axios
        .get(`api/support-document/${this.document_id}`, this.$root.config)
        .then((response) => {
          const doc = response.data.document;
          this.document = {
            ...this.document,
            provider_id: doc.provider_id,
            supplier: doc.supplier || "Sin Proveedor",
            reference: doc.reference,
            observations: doc.observations,
            numbering_range_id: doc.numbering_range_id || null,
            payment_method_code: doc.payment_method_code || "10",
          };
          if (doc.supplierData) {
            this.selectedSupplier = doc.supplierData;
          }
          if (doc.services && Array.isArray(doc.services)) {
            this.servicesList = doc.services.map((s) => ({
              tempId: Date.now() + Math.random(),
              service_id: s.service_id,
              codigo: s.codigo,
              name: s.name,
              quantity: s.quantity,
              price: s.price,
              measurement_unit_id:
                s.measurement_unit_id || (this.measurementUnits.length ? this.measurementUnits[0].id : null),
              tax_id: s.tax_id || (this.taxes.length ? this.taxes[0].id : null),
              showObservations: false,
              observations: s.observations || "",
            }));
          }
        })
        .catch((err) => console.error(err));
    },
    resetForm() {
    setTimeout(() => {
      this.loading = false;
      this.disabled = false;
      window.location.reload();
    }, 300);
  },
    commands() {
      if (typeof shortcut !== "undefined") {
        shortcut.add("F10", () => {
          this.openAddServiceModal();
        });
      }
    },
  },
};
</script>

<style scoped>
.create-edit-document {
  font-size: 1.1rem;
}

.container-fluid {
  padding: 0 1rem;
}

.loading-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(255, 255, 255, 0.8);
  z-index: 5000;
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
}

.table thead th {
  white-space: nowrap;
}
</style>
