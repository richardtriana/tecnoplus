<template>
  <div class="create-adjustment-note container-fluid">
    <!-- Overlay de Loading -->
    <div v-if="loading" class="loading-overlay">
      <div class="spinner-border text-info" role="status">
        <span class="sr-only">Cargando...</span>
      </div>
      <p>Cargando, por favor espere...</p>
    </div>

    <!-- ENCABEZADO -->
    <div class="row bg-info text-white mb-3 p-3 align-items-center">
      <div class="col-8">
        <!-- Se muestra el reference_code del documento soporte si está cargado -->
        <h2 class="m-0">
          Nota de Ajuste al Documento Soporte
          <span v-if="adjustmentNote.reference_code"> - {{ adjustmentNote.reference_code }}</span>
        </h2>
      </div>
      <div class="col-4 text-right">
        <h1 class="m-0">Total $ {{ formattedTotal }}</h1>
      </div>
    </div>

    <!-- Contenido Principal -->
    <div class="row">
      <!-- Sección de Datos Generales y Items -->
      <div class="col-9">
        <!-- Datos Generales -->
        <div class="form-group">
          <label for="supportDocumentId">ID del Documento Soporte</label>
          <input type="number" id="supportDocumentId" v-model="adjustmentNote.support_document_id" class="form-control" readonly />
        </div>
        <!-- Select para Código de Motivo del Ajuste -->
        <div class="form-group">
          <label for="correctionConceptCode">Código de Motivo del Ajuste</label>
          <select id="correctionConceptCode" v-model="adjustmentNote.correction_concept_code" class="form-control">
            <option value="" disabled>Seleccione un motivo</option>
            <option v-for="reason in adjustmentNoteReasons" :key="reason.id" :value="reason.code">
              {{ reason.code }} - {{ reason.description }}
            </option>
          </select>
        </div>
        <div class="form-group">
          <label for="observation">Observación</label>
          <textarea id="observation" v-model="adjustmentNote.observation" class="form-control" maxlength="250" placeholder="Observación (máximo 250 caracteres)"></textarea>
        </div>

        <!-- Sección de Items -->
        <h4>Productos / Servicios</h4>
        <button class="btn btn-secondary mb-2" @click="openAddItemModal">Agregar Item</button>
        <table class="table table-bordered">
          <thead>
            <tr>
              <th>Código</th>
              <th>Nombre</th>
              <th>Cantidad</th>
              <th>Desc (%)</th>
              <th>Precio</th>
              <th>Unidad</th>
              <th>Código Estándar</th>
              <th>Total</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(item, index) in items" :key="item.tempId">
              <td>{{ item.code_reference }}</td>
              <td>{{ item.name }}</td>
              <td>
                <input type="number" v-model.number="item.quantity" class="form-control" />
              </td>
              <td>
                <input type="number" v-model.number="item.discount_rate" class="form-control" />
              </td>
              <td>
                <input type="number" v-model.number="item.price" class="form-control" />
              </td>
              <td>
                <select v-model="item.unit_measure_id" class="form-control">
                  <option v-for="mu in measurementUnits" :key="mu.id" :value="mu.id">{{ mu.name }}</option>
                </select>
              </td>
              <!-- Select para Código Estándar obtenido de productIdentificationStandards -->
              <td>
                <select v-model="item.standard_code_id" class="form-control">
                  <option v-for="standard in productIdentificationStandards" :key="standard.id" :value="standard.id">
                    {{ standard.name }}
                  </option>
                </select>
              </td>
              <td class="text-right">{{ (item.quantity * item.price).toFixed(2) }}</td>
              <td>
                <button class="btn btn-danger btn-sm" @click="removeItem(index)">Eliminar</button>
              </td>
            </tr>
            <tr v-if="items.length === 0">
              <td colspan="9" class="text-center">No se han añadido items</td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Panel Lateral: Resumen y Opciones -->
      <div class="col-3">
        <div class="card mb-3">
          <div class="card-header bg-info text-white">
            <strong>Resumen</strong>
          </div>
          <div class="card-body">
            <p>Subtotal: $ {{ formattedSubtotal }}</p>
            <p>Total: $ {{ formattedTotal }}</p>
          </div>
        </div>

        <!-- Selección de Numbering Range -->
        <div class="form-group">
          <label>Numbering Range</label>
          <select class="form-control" v-model="adjustmentNote.numbering_range_id">
            <option v-for="nr in numberingRanges" :key="nr.id" :value="nr.id">
              {{ nr.document }} - {{ nr.prefix }}
            </option>
          </select>
        </div>

        <!-- Selección de Payment Method -->
        <div class="form-group">
          <label>Payment Method</label>
          <select class="form-control" v-model="adjustmentNote.payment_method_code">
            <option v-for="pm in paymentMethods" :key="pm.id" :value="pm.code">
              {{ pm.name }}
            </option>
          </select>
        </div>

        <!-- Botones de Acción -->
        <button class="btn btn-primary btn-block mb-2" @click="saveAdjustmentNote">
          Guardar Nota de Ajuste
        </button>
        <button class="btn btn-secondary btn-block" @click="cancel">
          Cancelar
        </button>
      </div>
    </div>
  </div>
</template>

<script>
import axios from "axios";
import Swal from "sweetalert2";
export default {
  name: "CreateAdjustmentNote",
  data() {
    return {
      loading: false,
      adjustmentNote: {
        reference_code: "",
        numbering_range_id: null,
        payment_method_code: "10",
        support_document_id: null,
        correction_concept_code: "",
        observation: "",
      },
      items: [],
      numberingRanges: [],
      paymentMethods: [],
      measurementUnits: [],
      // Motivos de nota de ajuste
      adjustmentNoteReasons: [],
      // Códigos estándar del endpoint product-identification-standards
      productIdentificationStandards: [],
      savedAdjustmentNote: null,
    };
  },
  computed: {
    subtotal() {
      return this.items.reduce((acc, item) => acc + (item.quantity * item.price), 0);
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
      return this.numberingRanges.find((nr) => nr.id === this.adjustmentNote.numbering_range_id) || null;
    },
  },
  mounted() {
    this.loadNumberingRanges();
    this.loadPaymentMethods();
    this.loadMeasurementUnits();
    this.loadAdjustmentNoteReasons();
    this.loadProductIdentificationStandards();
    // Si la ruta trae el parámetro support_document_id, cargar los datos del documento soporte
    if (this.$route.params.support_document_id) {
      this.adjustmentNote.support_document_id = this.$route.params.support_document_id;
      this.loadSupportDocument(this.adjustmentNote.support_document_id);
    }
  },
  methods: {
    loadNumberingRanges() {
      axios.get("api/numbering_ranges", this.$root.config)
        .then((res) => {
          // Filtrar para rangos de Nota de Ajuste que tengan document === "Nota de Ajuste Documento Soporte" y estén activos
          this.numberingRanges = res.data.filter((nr) => nr.document === "Nota de Ajuste Documento Soporte" && nr.is_active === 1);
          if (!this.adjustmentNote.numbering_range_id && this.numberingRanges.length) {
            this.adjustmentNote.numbering_range_id = this.numberingRanges[0].id;
          }
        })
        .catch((err) => console.error("Error al cargar numbering ranges:", err));
    },
    loadPaymentMethods() {
      axios.get("api/payment-methods", this.$root.config)
        .then((res) => {
          this.paymentMethods = res.data.payment_methods || [];
          if (!this.adjustmentNote.payment_method_code && this.paymentMethods.length) {
            this.adjustmentNote.payment_method_code = this.paymentMethods[0].code;
          }
        })
        .catch((err) => console.error("Error al cargar payment methods:", err));
    },
    loadMeasurementUnits() {
      axios.get("api/measurement-units", this.$root.config)
        .then((res) => {
          this.measurementUnits = res.data || [];
        })
        .catch((err) => console.error("Error al cargar measurement units:", err));
    },
    loadAdjustmentNoteReasons() {
      axios.get("api/adjustment-note-reasons", this.$root.config)
        .then((res) => {
          this.adjustmentNoteReasons = res.data.reasons || [];
        })
        .catch((err) => console.error("Error al cargar motivos de nota de ajuste:", err));
    },
    loadProductIdentificationStandards() {
      axios.get("api/product-identification-standards", this.$root.config)
        .then((res) => {
          this.productIdentificationStandards = res.data || [];
        })
        .catch((err) => console.error("Error al cargar códigos estándar:", err));
    },
    loadSupportDocument(id) {
      axios.get(`api/support-document/${id}`, this.$root.config)
        .then((response) => {
          const doc = response.data.document;
          if (doc) {
            // Asigna el reference_code sin sufijo adicional
            this.adjustmentNote.reference_code = doc.reference_code;
            // Usar el id real del documento soporte
            this.adjustmentNote.support_document_id = doc.id;
            // Cargar los ítems del comprobante (se asume que vienen en doc.items)
            if (doc.items && Array.isArray(doc.items)) {
              this.items = doc.items.map(s => ({
                tempId: Date.now() + Math.random(),
                code_reference: s.code_reference || s.codigo || "",
                name: s.name,
                quantity: s.quantity,
                discount_rate: s.discount_rate || 0,
                price: s.price,
                unit_measure_id: s.unit_measure_id || (this.measurementUnits.length ? this.measurementUnits[0].id : null),
                // Usar Código Estándar de productIdentificationStandards
                standard_code_id: s.standard_code_id || (this.productIdentificationStandards.length ? this.productIdentificationStandards[0].id : null),
                withholding_taxes: s.withholding_taxes || [],
              }));
            }
          }
        })
        .catch((err) => console.error("Error al cargar el documento soporte:", err));
    },
    openAddItemModal() {
      const tempId = Date.now() + Math.random();
      this.items.push({
        tempId: tempId,
        code_reference: "",
        name: "",
        quantity: 1,
        discount_rate: 0,
        price: 0,
        unit_measure_id: this.measurementUnits.length ? this.measurementUnits[0].id : null,
        standard_code_id: this.productIdentificationStandards.length ? this.productIdentificationStandards[0].id : null,
        withholding_taxes: [],
      });
    },
    removeItem(index) {
      this.items.splice(index, 1);
    },
    saveAdjustmentNote() {
      if (!this.items.length) {
        Swal.fire({
          icon: "warning",
          title: "Debes añadir al menos un item"
        });
        return;
      }
      if (!this.adjustmentNote.support_document_id || !this.adjustmentNote.correction_concept_code) {
        Swal.fire({
          icon: "warning",
          title: "Debes completar los campos obligatorios"
        });
        return;
      }
      // Si no se ingresa reference_code manualmente, se genera a partir del rango seleccionado
      if (!this.adjustmentNote.reference_code && this.selectedNumberingRange) {
        this.adjustmentNote.reference_code = this.selectedNumberingRange.prefix + this.selectedNumberingRange.current;
      }
      this.adjustmentNote.items = this.items;
      this.loading = true;
      axios.post("api/adjustment-notes", this.adjustmentNote, this.$root.config)
        .then((response) => {
          Swal.fire({
            icon: "success",
            title: "Nota de ajuste guardada correctamente"
          });
          this.savedAdjustmentNote = response.data.adjustment_note;
          this.resetForm();
        })
        .catch((err) => {
          console.error(err);
          Swal.fire({
            icon: "error",
            title: "Error al guardar la nota de ajuste",
            text: err.message
          });
        });
    },
    resetForm() {
      setTimeout(() => {
        this.loading = false;
        // Redirigir a la vista de listado de documentos soporte
        this.$router.push({ name: "DocumentList" });
      }, 300);
    },
    cancel() {
      this.$router.push({ name: "DocumentList" });
    },
  },
};
</script>

<style scoped>
.create-adjustment-note {
  font-size: 1.1rem;
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
</style>
