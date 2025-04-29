<template>
    <div class="credit-note container mt-4">
      <h2 class="mb-3">Crear Nota Crédito</h2>
      
      <!-- Selección del tipo de Nota Crédito -->
      <div class="mb-3">
        <label class="form-label">Tipo de Nota Crédito</label>
        <div>
          <label class="me-3">
            <input type="radio" value="referenciada" v-model="creditNoteType" />
            Con referencia a factura
          </label>
          <label>
            <input type="radio" value="sin_referencia" v-model="creditNoteType" />
            Sin referencia a factura
          </label>
        </div>
      </div>
  
      <!-- Sección para Nota Crédito Referenciada -->
      <div v-if="creditNoteType === 'referenciada'">
        <!-- Si no se ha asignado bill_id, se muestra botón de búsqueda -->
        <div v-if="!creditNote.bill_id" class="mb-3">
          <button class="btn btn-info" @click="openSearchModal">
            Buscar Factura
          </button>
        </div>
        <!-- Si ya se asignó bill_id se muestra -->
        <div v-else class="form-group mb-3">
          <label for="bill_id">ID de Factura:</label>
          <input type="text" id="bill_id" v-model="creditNote.bill_id" class="form-control" readonly />
          <!-- Intentamos cargar ítems, pero si falla se notifica -->
          <button class="btn btn-secondary btn-sm mt-2" @click="loadBillItems">
            Cargar Ítems de la Factura
          </button>
        </div>
        <!-- Tabla de ítems, si se han cargado (si loadBillItems tuvo éxito) -->
        <div v-if="items.length > 0" class="mb-3">
          <h5>Ítems de la Factura</h5>
          <table class="table table-sm table-bordered">
            <thead>
              <tr>
                <th>Código</th>
                <th>Producto/Servicio</th>
                <th>Cantidad</th>
                <th>Precio c/IVA</th>
                <th>IVA (%)</th>
                <th>Desc. (%)</th>
                <th>Acciones</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(item, index) in items" :key="index">
                <td>{{ item.code_reference }}</td>
                <td>{{ item.name }}</td>
                <td>
                  <input type="number" min="0" class="form-control form-control-sm" v-model="item.quantity" />
                </td>
                <td>
                  <input type="number" step="any" class="form-control form-control-sm" v-model="item.price" />
                </td>
                <td>
                  <input type="text" class="form-control form-control-sm" v-model="item.tax_rate" />
                </td>
                <td>
                  <input type="number" step="any" class="form-control form-control-sm" v-model="item.discount_rate" />
                </td>
                <td>
                  <button class="btn btn-danger btn-sm" @click="removeItem(index)">Eliminar</button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
  
      <!-- Sección para Nota Crédito Sin Referencia -->
      <div v-else>
        <button class="btn btn-info mb-3" @click="openAddItemModal">
          Agregar Ítem
        </button>
        <div v-if="items.length > 0" class="mb-3">
          <h5>Ítems Agregados</h5>
          <table class="table table-sm table-bordered">
            <thead>
              <tr>
                <th>Código</th>
                <th>Producto/Servicio</th>
                <th>Cantidad</th>
                <th>Precio c/IVA</th>
                <th>IVA (%)</th>
                <th>Desc. (%)</th>
                <th>Acciones</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(item, index) in items" :key="index">
                <td>{{ item.code_reference }}</td>
                <td>{{ item.name }}</td>
                <td>
                  <input type="number" min="0" class="form-control form-control-sm" v-model="item.quantity" />
                </td>
                <td>
                  <input type="number" step="any" class="form-control form-control-sm" v-model="item.price" />
                </td>
                <td>
                  <input type="text" class="form-control form-control-sm" v-model="item.tax_rate" />
                </td>
                <td>
                  <input type="number" step="any" class="form-control form-control-sm" v-model="item.discount_rate" />
                </td>
                <td>
                  <button class="btn btn-danger btn-sm" @click="removeItem(index)">Eliminar</button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
  
      <!-- Campos comunes para ambas modalidades -->
      <div class="form-group mb-3">
        <label for="correction_concept_code">Motivo de Ajuste:</label>
        <select id="correction_concept_code" v-model="creditNote.correction_concept_code" required class="form-control">
          <option disabled value="">Seleccione un motivo</option>
          <option v-for="reason in adjustmentNoteReasons" :key="reason.id" :value="reason.code">
            {{ reason.description }}
          </option>
        </select>
      </div>
  
      <div class="form-group mb-3">
        <label for="customization_id">Tipo de Operación:</label>
        <select id="customization_id" v-model="creditNote.customization_id" required class="form-control">
          <option disabled value="">Seleccione un tipo</option>
          <option v-for="type in operationTypes" :key="type.id" :value="type.code">
            {{ type.description }}
          </option>
        </select>
      </div>
  
      <div class="form-group mb-3">
        <label for="observation">Observación (opcional):</label>
        <textarea id="observation" v-model="creditNote.observation" class="form-control" rows="2"></textarea>
      </div>
  
      <div class="form-group mb-3" v-if="numberingRanges.length > 1">
        <label for="numbering_range_id">Rango de Numeración:</label>
        <select id="numbering_range_id" v-model="creditNote.numbering_range_id" required class="form-control">
          <option disabled value="">Seleccione un rango</option>
          <option v-for="range in numberingRanges" :key="range.id" :value="range.id">
            {{ range.prefix }} ({{ range.current }})
          </option>
        </select>
      </div>
  
      <button type="button" class="btn btn-primary" @click="submitCreditNote">
        Enviar Nota Crédito
      </button>
  
      <div v-if="loading" class="mt-3 text-center">
        <p>Cargando...</p>
      </div>
  
      <div v-if="message" class="mt-3 alert" :class="{'alert-success': success, 'alert-danger': !success}">
        {{ message }}
      </div>
  
      <!-- Modal de búsqueda de factura (scrollable) -->
      <div class="modal fade" id="searchModal" tabindex="-1" aria-labelledby="searchModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" id="searchModalLabel">Buscar Factura</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
              <div class="mb-3">
                <label for="modalSearchQuery" class="form-label">Criterio de búsqueda:</label>
                <input type="text" id="modalSearchQuery" class="form-control" placeholder="Ingrese número, identificación o nombre" v-model="searchQuery" />
              </div>
              <button class="btn btn-info mb-3" @click="searchBill">Buscar</button>
              <div v-if="searchResults.length">
                <h6>Resultados:</h6>
                <ul class="list-group">
                  <li class="list-group-item" v-for="bill in searchResults" :key="bill.id" @click="selectBill(bill)" style="cursor: pointer;">
                    {{ bill.number }} - {{ bill.names }} (ID: {{ bill.id }})
                  </li>
                </ul>
              </div>
              <div v-else-if="searchQuery">
                <p>No se encontraron resultados.</p>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                Cerrar
              </button>
            </div>
          </div>
        </div>
      </div>
  
      <!-- Modal para agregar ítem manualmente (solo para sin_referencia) -->
      <div class="modal fade" id="addItemModal" tabindex="-1" aria-labelledby="addItemModalLabel" aria-hidden="true">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" id="addItemModalLabel">Agregar Ítem</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
              <div class="mb-3">
                <label for="itemCodeRef" class="form-label">Código de referencia</label>
                <input type="text" id="itemCodeRef" class="form-control" v-model="newItem.code_reference" />
              </div>
              <div class="mb-3">
                <label for="itemName" class="form-label">Nombre</label>
                <input type="text" id="itemName" class="form-control" v-model="newItem.name" />
              </div>
              <div class="mb-3">
                <label for="itemQuantity" class="form-label">Cantidad</label>
                <input type="number" id="itemQuantity" class="form-control" v-model="newItem.quantity" />
              </div>
              <div class="mb-3">
                <label for="itemPrice" class="form-label">Precio (c/IVA)</label>
                <input type="number" step="any" id="itemPrice" class="form-control" v-model="newItem.price" />
              </div>
              <div class="mb-3">
                <label for="itemTax" class="form-label">IVA (%)</label>
                <input type="text" id="itemTax" class="form-control" v-model="newItem.tax_rate" />
              </div>
              <div class="mb-3">
                <label for="itemDiscount" class="form-label">Descuento (%)</label>
                <input type="number" step="any" id="itemDiscount" class="form-control" v-model="newItem.discount_rate" />
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
              <button type="button" class="btn btn-primary" @click="addItem">Agregar</button>
            </div>
          </div>
        </div>
      </div>
  
    </div>
  </template>
  
  <script>
  import axios from "axios";
  import { Modal } from "bootstrap";
  
  export default {
    name: "CreateCreditNote",
    data() {
      return {
        loading: false,
        message: "",
        success: false,
        // Modal de búsqueda de factura
        searchModalInstance: null,
        searchQuery: "",
        searchResults: [],
        // Modal para agregar ítem manualmente
        addItemModalInstance: null,
        // Tipo de Nota Crédito: "referenciada" o "sin_referencia"
        creditNoteType: "referenciada",
        // Datos de la Nota Crédito
        creditNote: {
          bill_id: "",
          correction_concept_code: "",
          customization_id: "",
          observation: "",
          numbering_range_id: ""
        },
        // Ítems que se enviarán
        items: [],
        // Catálogos para selects
        adjustmentNoteReasons: [],
        operationTypes: [],
        numberingRanges: [],
        // Datos para nuevo ítem (modal de agregar ítem)
        newItem: {
          code_reference: "",
          name: "",
          quantity: 1,
          price: 0,
          tax_rate: "19.00",
          discount_rate: 0,
          unit_measure_id: 70,
          standard_code_id: 1,
          is_excluded: 0,
          tribute_id: 1,
          withholding_taxes: []
        }
      };
    },
    mounted() {
      this.loadAdjustmentNoteReasons();
      this.loadOperationTypes();
      this.loadNumberingRanges();
      // Inicializar modal de búsqueda
      const modalSearchEl = document.getElementById("searchModal");
      this.searchModalInstance = new Modal(modalSearchEl);
      // Inicializar modal de agregar ítem
      const modalAddItemEl = document.getElementById("addItemModal");
      this.addItemModalInstance = new Modal(modalAddItemEl);
      // Si viene bill_id por query, se asigna y se intenta cargar ítems
      if (this.$route.query.bill_id) {
        this.creditNote.bill_id = this.$route.query.bill_id;
        this.loadBillItems();
      }
    },
    methods: {
      loadAdjustmentNoteReasons() {
        axios
          .get("api/credit-notes/adjustment-note-reasons")
          .then((response) => {
            this.adjustmentNoteReasons = response.data.adjustment_note_reasons;
          })
          .catch((error) => {
            console.error("Error al cargar motivos de ajuste", error);
          });
      },
      loadOperationTypes() {
        axios
          .get("api/credit-notes/operation-types")
          .then((response) => {
            this.operationTypes = response.data.operation_types;
          })
          .catch((error) => {
            console.error("Error al cargar tipos de operación", error);
          });
      },
      loadNumberingRanges() {
        axios
          .get("api/credit-notes/numbering-ranges")
          .then((response) => {
            this.numberingRanges = response.data.numbering_ranges;
            if (this.numberingRanges.length === 1) {
              this.creditNote.numbering_range_id = this.numberingRanges[0].id;
            }
          })
          .catch((error) => {
            console.error("Error al cargar rangos de numeración", error);
          });
      },
      openSearchModal() {
        this.searchQuery = "";
        this.searchResults = [];
        this.searchModalInstance.show();
      },
      searchBill() {
        if (!this.searchQuery) {
          this.message = "Ingrese un criterio de búsqueda.";
          this.success = false;
          return;
        }
        axios
          .get("api/factus-invoice/getBills", {
            params: { query: this.searchQuery },
            headers: this.$root.config.headers
          })
          .then((response) => {
            // Según la respuesta, la data se encuentra en response.data.data.data.data
            const result = response.data.data.data;
            this.searchResults = result.data || [];
            if (!this.searchResults.length) {
              this.message = "No se encontraron resultados.";
              this.success = false;
            } else {
              this.message = "";
            }
          })
          .catch((error) => {
            console.error("Error al buscar factura", error);
            this.message = "Error al buscar factura.";
            this.success = false;
          });
      },
      selectBill(bill) {
        this.creditNote.bill_id = bill.id;
        this.searchModalInstance.hide();
        // Intentar cargar ítems; si falla, se notifica y se deja vacío para que el usuario agregue manualmente
        this.loadBillItems();
      },
      loadBillItems() {
        if (!this.creditNote.bill_id) return;
        axios
          .get(`api/orders/${this.creditNote.bill_id}`, this.$root.config)
          .then((resp) => {
            const detailOrders = resp.data.order_details || [];
            this.items = detailOrders.map((d) => ({
              code_reference: d.barcode,
              name: d.product,
              quantity: d.quantity,
              discount_rate: d.discount_percentage || 0,
              price: d.price_tax_inc,
              tax_rate: d.tax_rate ? d.tax_rate.toString() : "19.00",
              unit_measure_id: 70,
              standard_code_id: 1,
              is_excluded: 0,
              tribute_id: 1,
              withholding_taxes: []
            }));
          })
          .catch((error) => {
            console.error("Error al cargar ítems de la factura", error);
            this.message = "La factura seleccionada no se encontró en el sistema local. Agregue los ítems manualmente.";
            this.success = false;
            this.items = []; // Se limpia para que el usuario pueda agregar manualmente
          });
      },
      openAddItemModal() {
        this.newItem = {
          code_reference: "",
          name: "",
          quantity: 1,
          price: 0,
          tax_rate: "19.00",
          discount_rate: 0,
          unit_measure_id: 70,
          standard_code_id: 1,
          is_excluded: 0,
          tribute_id: 1,
          withholding_taxes: []
        };
        this.addItemModalInstance.show();
      },
      addItem() {
        this.items.push({ ...this.newItem });
        this.addItemModalInstance.hide();
      },
      removeItem(index) {
        this.items.splice(index, 1);
      },
      submitCreditNote() {
        if (!this.creditNote.correction_concept_code || !this.creditNote.customization_id) {
          this.message = "Complete los campos obligatorios (Motivo y Tipo de Operación).";
          this.success = false;
          return;
        }
        if (this.creditNoteType === "referenciada" && !this.creditNote.bill_id) {
          this.message = "Seleccione una factura para la Nota Crédito referenciada.";
          this.success = false;
          return;
        }
        if (!this.items.length) {
          this.message = "Debe agregar al menos un ítem a la Nota Crédito.";
          this.success = false;
          return;
        }
        this.loading = true;
        this.message = "";
        this.success = false;
        const payload = {
          numbering_range_id: this.creditNote.numbering_range_id || null,
          correction_concept_code: this.creditNote.correction_concept_code,
          customization_id: this.creditNote.customization_id,
          bill_id: this.creditNoteType === "referenciada" ? this.creditNote.bill_id : null,
          reference_code: this.generateUniqueReferenceCode(),
          observation: this.creditNote.observation || "",
          payment_method_code: "10",
          items: this.items
        };
        axios
          .post("api/credit-notes/validate", payload, this.$root.config)
          .then((response) => {
            this.message = "Nota Crédito enviada y validada correctamente.";
            this.success = true;
            this.resetForm();
          })
          .catch((error) => {
            console.error("Error al enviar Nota Crédito", error);
            const errMsg =
              error.response && error.response.data && error.response.data.message
                ? error.response.data.message
                : "Error al enviar la Nota Crédito.";
            this.message = errMsg;
            this.success = false;
          })
          .finally(() => {
            this.loading = false;
          });
      },
      generateUniqueReferenceCode() {
        return "NC-" + Date.now();
      },
      resetForm() {
        this.creditNoteType = "referenciada";
        this.creditNote.bill_id = "";
        this.creditNote.correction_concept_code = "";
        this.creditNote.customization_id = "";
        this.creditNote.observation = "";
        this.creditNote.numbering_range_id = "";
        this.items = [];
      }
    }
  };
  </script>
  
  <style scoped>
  .credit-note {
    max-width: 800px;
    margin: auto;
    padding: 1rem;
  }
  </style>
  