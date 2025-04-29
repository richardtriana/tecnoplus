<template>
  <div class="row px-2" id="create-credit-note">
    <!-- Overlay de Loading -->
    <div v-if="loading" class="loading-overlay">
      <div class="spinner-border text-purple" role="status">
        <span class="sr-only">Cargando...</span>
      </div>
      <p>Creando nota crédito, por favor espere...</p>
    </div>

    <!-- Sección superior: Información de la Nota Crédito -->
    <div class="col-12 mb-2">
      <div class="d-flex align-items-center justify-content-between">
        <div>
          <!-- Se muestra el comprobante calculado (por ejemplo, "NC" + (current+1)) -->
          <h5 class="m-0">
            <strong>Nota Crédito</strong>
            <span v-if="displayRef"> - {{ displayRef }}</span>
          </h5>
          <!-- Muestra el cliente de la orden si ya fue cargado -->
          <p class="m-0 text-muted" v-if="orderClientName">
            Cliente afectado: <strong>{{ orderClientName }}</strong>
          </p>
        </div>
        <!-- Estado DIAN (solo ilustrativo) -->
        <div class="alert alert-purple m-0" role="alert">
          <strong>Estado de envío a DIAN:</strong>
          <span style="color: purple;">
            Se enviará como Nota Crédito
          </span>
        </div>
      </div>
    </div>

    <!-- Panel principal: Items de la Nota Crédito -->
    <div class="col-9 justify-content-center p-2">
      <!-- Se muestra el buscador de productos si:
           - customization_id === "20" Y ya se cargó bill_id, 
           - ó customization_id === "22" (no requiere bill_id).
      -->
      <div class="mb-3" v-if="showProductSearcher">
        <div class="input-group">
          <input
            type="text"
            class="form-control"
            placeholder="Código de barras o nombre del producto"
            v-model="filters.product"
            @keypress.enter="searchProduct"
          />
          <div class="input-group-append" id="button-add-product">
            <button class="btn btn-outline-secondary" type="button" @click="openAddProductModal">
              <b>F10</b> Añadir Producto
            </button>
            <button class="btn btn-outline-secondary" type="button" @click="searchProduct">
              <i class="bi bi-search"></i>
            </button>
          </div>
        </div>
      </div>

      <!-- Encabezado: Total de la Nota -->
      <div class="sticky-top mb-2 text-uppercase w-50" style="z-index: 1022; left: 100%">
        <table class="table table-borderless">
          <tr class="h1 text-white bg-purple">
            <td class="text-right">Total</td>
            <td>$ {{ total_tax_inc.toFixed(0) }}</td>
          </tr>
        </table>
      </div>

      <!-- Listado de productos/ítems en la Nota Crédito -->
      <section>
        <div>
          <table class="table table-sm table-responsive-sm table-bordered table-hover">
            <thead class="bg-purple text-white position-sticky sticky-top" style="top: 4rem">
              <tr>
                <th></th>
                <th>Código</th>
                <th>Producto</th>
                <th>Cantidad</th>
                <th>Precio (c/IVA)</th>
                <th>IVA %</th>
                <th>Desc. %</th>
                <th>Total</th>
              </tr>
            </thead>
            <tbody v-if="creditNoteItems.length > 0">
              <tr v-for="(item, idx) in creditNoteItems" :key="idx">
                <td>
                  <button class="btn btn-outline-danger btn-sm" @click="removeItem(idx)">
                    <i class="bi bi-trash"></i>
                  </button>
                </td>
                <td>{{ item.code_reference }}</td>
                <td>{{ item.name }}</td>
                <td>
                  <input
                    type="number"
                    min="1"
                    class="form-control form-control-sm"
                    style="max-width: 60px"
                    v-model.number="item.quantity"
                    @change="checkQuantity(idx)"
                  />
                </td>
                <td>
                  <input
                    type="number"
                    step="any"
                    class="form-control form-control-sm"
                    style="max-width: 100px"
                    v-model.number="item.price"
                  />
                </td>
                <td class="text-right">{{ item.tax_rate }}%</td>
                <td>
                  <input
                    type="number"
                    step="any"
                    class="form-control form-control-sm"
                    style="max-width: 60px"
                    v-model.number="item.discount_rate"
                  />
                </td>
                <td class="text-right">{{ itemTotal(item).toFixed(2) }}</td>
              </tr>
            </tbody>
            <tbody v-else>
              <tr>
                <td colspan="8">No se han añadido ítems</td>
              </tr>
            </tbody>
          </table>
        </div>
      </section>
    </div>

    <!-- Panel lateral: Datos de la Nota Crédito -->
    <div class="col-md-3">
      <section class="card">
        <div>
          <table class="table table-sm text-right">
            <tr>
              <th colspan="7">Subtotal:</th>
              <th>$ {{ total_tax_exc.toFixed(0) }}</th>
            </tr>
            <tr>
              <th colspan="7">IVA:</th>
              <th>$ {{ total_iva.toFixed(0) }}</th>
            </tr>
            <tr>
              <th colspan="7">Descuento:</th>
              <th>$ {{ total_discount.toFixed(0) }}</th>
            </tr>
            <tr class="bg-purple h5 text-white">
              <th colspan="7">Total:</th>
              <th>$ {{ total_tax_inc.toFixed(0) }}</th>
            </tr>
          </table>
        </div>
      </section>

      <!-- Campos de la Nota Crédito -->
      <!-- Tipo de Operación -->
      <div class="mt-2">
        <label class="form-label">Tipo de Operación</label>
        <select class="form-control form-control-sm" v-model="creditNote.customization_id">
          <option v-for="op in operationTypes" :key="op.id" :value="op.code">
            {{ op.description }} ({{ op.code }})
          </option>
        </select>
      </div>

      <!-- Concepto -->
      <div class="mt-2">
        <label class="form-label">Concepto</label>
        <select class="form-control form-control-sm" v-model="creditNote.correction_concept_code">
          <option v-for="code in correctionCodes" :key="code.id" :value="code.code">
            {{ code.description }} ({{ code.code }})
          </option>
        </select>
      </div>

      <!-- Comprobante -->
      <div class="mt-2">
        <label class="form-label">Referencia de factura </label>
        <div class="input-group">
          <!-- Se deshabilita si customization_id es '22' -->
          <input
            type="number"
            class="form-control form-control-sm"
            v-model="creditNote.bill_id"
            :disabled="creditNote.customization_id === '22'"
          />
          <button class="btn btn-outline-primary btn-sm" @click="loadOrderByBill">
            Buscar Orden
          </button>
        </div>
      </div>

      <!-- Método de Pago -->
      <div class="mt-2">
        <label class="form-label">Método de Pago</label>
        <select class="form-control form-control-sm" v-model="creditNote.payment_method_code">
          <option v-for="pm in paymentMethods" :key="pm.id" :value="pm.code">
            {{ pm.name }}
          </option>
        </select>
      </div>

      <!-- Rango de Numeración -->
      <div class="mt-2">
        <label class="form-label">Comprobante </label>
        <select class="form-control form-control-sm" v-model="creditNote.numbering_range_id">
          <option v-for="range in numberingRanges" :key="range.id" :value="range.id">
            {{ range.prefix }}
          </option>
        </select>
      </div>

      <!-- Observaciones -->
      <div class="mt-2">
        <label class="form-label">Observaciones:</label>
        <input
          type="text"
          class="form-control form-control-sm"
          v-model="creditNote.observation"
        />
      </div>

      <!-- Botones de acción -->
      <div class="mt-3">
        <button type="button" class="btn btn-purple btn-block" @click="createCreditNote">
          <i class="bi bi-file-earmark-minus"></i> Crear Nota Crédito
        </button>
        <router-link to="/orders" class="btn btn-outline-secondary btn-block">
          <i class="bi bi-arrow-left"></i> Volver
        </router-link>
      </div>
    </div>

    <!-- MODAL: Seleccionar productos para Devolución Parcial -->
    <div
      class="modal fade"
      tabindex="-1"
      role="dialog"
      :class="{ show: showSelectProductsModal }"
      :style="{ display: showSelectProductsModal ? 'block' : 'none' }"
      aria-hidden="true"
    >
      <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
          <div class="modal-header bg-primary text-white">
            <h5 class="modal-title">Productos del comprobante</h5>
            <button type="button" class="btn-close" @click="closeSelectProductsModal"></button>
          </div>
          <div class="modal-body">
            <table class="table table-bordered table-sm">
              <thead>
                <tr>
                  <th>#</th>
                  <th>Código</th>
                  <th>Producto</th>
                  <th>Precio (c/IVA)</th>
                  <th>Cantidad Disponible</th>
                  <th>Añadir</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(prod, idx) in orderProducts" :key="prod.id">
                  <td>{{ idx + 1 }}</td>
                  <td>{{ prod.barcode }}</td>
                  <td>{{ prod.product }}</td>
                  <td>{{ prod.price_tax_inc | currency }}</td>
                  <td>{{ prod.quantity }}</td>
                  <td>
                    <button class="btn btn-success btn-sm" @click="addProductToCreditNote(idx)">
                      <i class="bi bi-plus"></i>
                    </button>
                  </td>
                </tr>
                <tr v-if="orderProducts.length === 0">
                  <td colspan="6">No hay productos disponibles</td>
                </tr>
              </tbody>
            </table>
          </div>
          <div class="modal-footer">
            <button class="btn btn-secondary" @click="closeSelectProductsModal">
              Cerrar
            </button>
          </div>
        </div>
      </div>
    </div>
    <div
      class="modal-backdrop fade"
      :class="{ show: showSelectProductsModal }"
      v-if="showSelectProductsModal"
    ></div>

    <!-- NUEVO: Modal de aviso para la cantidad -->
    <div
      class="modal fade"
      tabindex="-1"
      role="dialog"
      :class="{ show: showQuantityWarning }"
      :style="{ display: showQuantityWarning ? 'block' : 'none' }"
      aria-hidden="true"
    >
      <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
          <div class="modal-header bg-primary text-white">
            <h5 class="modal-title">Aviso</h5>
            <button type="button" class="btn-close" @click="closeQuantityModal"></button>
          </div>
          <div class="modal-body">
            <p>No puedes exceder la cantidad original de la orden.</p>
          </div>
          <div class="modal-footer">
            <button class="btn btn-secondary" @click="closeQuantityModal">
              Cerrar
            </button>
          </div>
        </div>
      </div>
    </div>
    <div
      class="modal-backdrop fade"
      :class="{ show: showQuantityWarning }"
      v-if="showQuantityWarning"
    ></div>

    <!-- Modal de notificación de error -->
    <div
      class="modal fade"
      tabindex="-1"
      role="dialog"
      :class="{ show: showErrorModal }"
      :style="{ display: showErrorModal ? 'block' : 'none' }"
      aria-hidden="true"
    >
      <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
          <div class="modal-header bg-danger text-white">
            <h5 class="modal-title">Error</h5>
            <button type="button" class="btn-close" @click="closeErrorModal"></button>
          </div>
          <div class="modal-body">
            <p>{{ errorMessage }}</p>
          </div>
          <div class="modal-footer">
            <button class="btn btn-secondary" @click="closeErrorModal">
              Cerrar
            </button>
          </div>
        </div>
      </div>
    </div>
    <div
      class="modal-backdrop fade"
      :class="{ show: showErrorModal }"
      v-if="showErrorModal"
    ></div>

    <!-- Modal de notificación de éxito -->
    <div
      class="modal fade"
      tabindex="-1"
      role="dialog"
      :class="{ show: showSuccessModal }"
      :style="{ display: showSuccessModal ? 'block' : 'none' }"
      aria-hidden="true"
    >
      <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
          <div class="modal-header bg-success text-white">
            <h5 class="modal-title">Éxito</h5>
            <button type="button" class="btn-close" @click="closeSuccessModal"></button>
          </div>
          <div class="modal-body">
            <p>{{ successMessage }}</p>
          </div>
          <div class="modal-footer">
            <button class="btn btn-secondary" @click="closeSuccessModal">
              Cerrar
            </button>
          </div>
        </div>
      </div>
    </div>
    <div
      class="modal-backdrop fade"
      :class="{ show: showSuccessModal }"
      v-if="showSuccessModal"
    ></div>

    <!-- Componente AddProduct para búsqueda emergente (se usa cuando la nota es "22") -->
    <add-product ref="AddProduct" @add-product="addProduct" />
  </div>
</template>

<script>
import axios from "axios";
import AddProduct from "./AddProduct.vue";

export default {
  name: "CreateCreditNote",
  components: { AddProduct },
  props: {
    order_id: {
      type: [String, Number],
      default: 0
    }
  },
  data() {
    return {
      loading: false,
      creditNote: {
        numbering_range_id: null,
        correction_concept_code: null,   // "1" => Devolución parcial
        customization_id: null,          // "20" => con factura, "22" => sin factura
        bill_id: null,                   // ID del comprobante (factura)
        reference_code: "",
        observation: "",
        payment_method_code: null,
        order_id: null,    // ID de la orden
        client_id: null    // ID del cliente
      },
      creditNoteItems: [],
      orderProducts: [],
      orderClientName: null,
      numberingRanges: [],
      correctionCodes: [],
      operationTypes: [],
      paymentMethods: [],
      total_tax_exc: 0,
      total_discount: 0,
      total_iva: 0,
      total_tax_inc: 0,
      filters: {
        product: ""
      },
      showSelectProductsModal: false,
      showQuantityWarning: false,
      showErrorModal: false,
      errorMessage: "",
      showSuccessModal: false,
      successMessage: "",
      displayRef: ""
    };
  },
  computed: {
    // Si la nota es con factura ("20"), se muestra buscador solo si hay bill_id;
    // si es sin factura ("22"), se muestra siempre.
    showProductSearcher() {
      if (this.creditNote.customization_id === "20") {
        return !!this.creditNote.bill_id;
      } else if (this.creditNote.customization_id === "22") {
        return true;
      }
      return false;
    }
  },
  mounted() {
    this.creditNote.reference_code = Date.now().toString();
    this.loadCorrectionCodes();
    this.loadOperationTypes();
    this.loadPaymentMethods();
    this.loadNumberingRanges();
    // Si se recibe order_id (prop) se usa para asignar el bill_id y cargar la orden
    if (Number(this.order_id) > 0) {
      this.creditNote.bill_id = this.order_id;
      this.loadOrderByBill();
    }
    // Registrar atajo F10 para abrir el modal de AddProduct
    if (typeof shortcut !== "undefined") {
      shortcut.add("F10", () => {
        if (this.creditNote.customization_id === "20" && !this.creditNote.bill_id) {
          this.errorMessage = "Primero cargue un comprobante";
          this.showErrorModal = true;
          return;
        }
        this.showSelectProductsModal = (this.creditNote.customization_id === "20");
        if (this.creditNote.customization_id === "22") {
          $("#addProductModal").modal("show");
        }
      });
    }
  },
  watch: {
    "creditNote.numbering_range_id"(newVal) {
      const nr = this.numberingRanges.find(r => r.id === newVal);
      if (nr) {
        this.displayRef = nr.prefix + (nr.current + 1);
      } else {
        this.displayRef = "";
      }
    },
    "creditNote.correction_concept_code"(newVal) {
      if (
        this.creditNote.customization_id === "20" &&
        newVal === "1" &&
        this.orderProducts.length > 0 &&
        this.creditNoteItems.length === 0
      ) {
        this.showSelectProductsModal = true;
      }
    },
    creditNoteItems: {
      handler() {
        this.calculateTotals();
      },
      deep: true
    }
  },
  methods: {
    loadCorrectionCodes() {
      axios.get("api/correction-codes", this.$root.config)
        .then(resp => {
          this.correctionCodes = resp.data;
          if (this.correctionCodes.length) {
            this.creditNote.correction_concept_code = this.correctionCodes[0].code;
          }
        })
        .catch(err => console.error("Error al cargar CorrectionCodes:", err));
    },
    loadOperationTypes() {
      axios.get("api/operation-types", this.$root.config)
        .then(resp => {
          this.operationTypes = resp.data;
          if (this.operationTypes.length) {
            this.creditNote.customization_id = this.operationTypes[0].code;
          }
        })
        .catch(err => console.error("Error al cargar OperationTypes:", err));
    },
    loadPaymentMethods() {
      axios.get("api/payment-methods", this.$root.config)
        .then(resp => {
          this.paymentMethods = resp.data.payment_methods;
          if (this.paymentMethods.length) {
            this.creditNote.payment_method_code = this.paymentMethods[0].code;
          }
        })
        .catch(err => console.error("Error al cargar PaymentMethods:", err));
    },
    loadNumberingRanges() {
      axios.get("api/numbering_ranges", this.$root.config)
        .then(resp => {
          this.numberingRanges = resp.data.filter(r => r.document === "Nota Crédito" && r.is_active == 1);
          if (this.numberingRanges.length) {
            this.creditNote.numbering_range_id = this.numberingRanges[0].id;
          }
        })
        .catch(err => console.error("Error al cargar NumberingRanges:", err));
    },
    searchProduct() {
      if (this.creditNote.customization_id === "20" && !this.creditNote.bill_id) {
        this.errorMessage = "Primero cargue un comprobante";
        this.showErrorModal = true;
        return;
      }
      if (!this.filters.product) return;
      if (this.creditNote.customization_id === "20" && this.creditNote.correction_concept_code === "1") {
        const filtro = this.filters.product.toLowerCase();
        const filtrados = this.orderProducts.filter(prod =>
          prod.barcode.toLowerCase().includes(filtro) ||
          prod.product.toLowerCase().includes(filtro)
        );
        if (filtrados.length === 0) {
          this.errorMessage = "No se encontró el producto en la orden";
          this.showErrorModal = true;
        } else if (filtrados.length === 1) {
          const index = this.orderProducts.findIndex(prod => prod.id === filtrados[0].id);
          this.addProductToCreditNote(index);
        } else {
          this.showSelectProductsModal = true;
        }
        this.filters.product = "";
        return;
      }
      if (this.creditNote.customization_id === "22") {
        $("#addProductModal").modal("show");
        return;
      }
      this.showSelectProductsModal = true;
      this.filters.product = "";
    },
    addProduct(new_product) {
      let exists = false;
      this.creditNoteItems.forEach(prod => {
        if (prod.code_reference === new_product.barcode) {
          exists = true;
          prod.quantity += 1;
        }
      });
      if (!exists) {
        this.creditNoteItems.push({
          code_reference: new_product.barcode,
          name: new_product.product,
          quantity: 1,
          discount_rate: 0,
          price: new_product.sale_price_tax_inc,
          tax_rate: "19.00",
          withholding_taxes: []
        });
      }
      $("#addProductModal").modal("hide");
    },
    openAddProductModal() {
      if (this.creditNote.customization_id === "20" && !this.creditNote.bill_id) {
        this.errorMessage = "Primero cargue un comprobante";
        this.showErrorModal = true;
        return;
      }
      if (this.creditNote.customization_id === "20") {
        this.showSelectProductsModal = true;
      } else {
        $("#addProductModal").modal("show");
      }
    },
    removeItem(idx) {
      this.creditNoteItems.splice(idx, 1);
    },
    checkQuantity(idx) {
      const item = this.creditNoteItems[idx];
      if (item.maxQuantity && item.quantity > item.maxQuantity) {
        item.quantity = item.maxQuantity;
        this.showQuantityWarning = true;
      }
    },
    closeQuantityModal() {
      this.showQuantityWarning = false;
    },
    closeErrorModal() {
      this.showErrorModal = false;
      this.errorMessage = "";
    },
    closeSuccessModal() {
      this.showSuccessModal = false;
      this.successMessage = "";
      this.refreshForm();
    },
    refreshForm() {
      // Reinicia los datos del formulario
      this.creditNote = {
        numbering_range_id: this.numberingRanges.length ? this.numberingRanges[0].id : null,
        correction_concept_code: this.correctionCodes.length ? this.correctionCodes[0].code : null,
        customization_id: this.operationTypes.length ? this.operationTypes[0].code : null,
        bill_id: null,
        reference_code: Date.now().toString(),
        observation: "",
        payment_method_code: this.paymentMethods.length ? this.paymentMethods[0].code : null,
        order_id: null,
        client_id: null
      };
      this.creditNoteItems = [];
      this.orderProducts = [];
      this.orderClientName = null;
      this.filters.product = "";
    },
    itemTotal(item) {
      const discountVal = (item.discount_rate / 100) * item.price;
      const netPrice = item.price - discountVal;
      return netPrice * item.quantity;
    },
    calculateTotals() {
      let subtotal = 0, discount = 0, iva = 0, total = 0;
      this.creditNoteItems.forEach(item => {
        const discountVal = (item.discount_rate / 100) * item.price;
        const netPrice = item.price - discountVal;
        const lineTotal = netPrice * item.quantity;
        const taxRateNum = parseFloat(item.tax_rate) || 0;
        subtotal += (item.price / (1 + taxRateNum / 100)) * item.quantity;
        discount += discountVal * item.quantity;
        iva += (item.price - (item.price / (1 + taxRateNum / 100))) * item.quantity;
        total += lineTotal;
      });
      this.total_tax_exc = subtotal;
      this.total_discount = discount;
      this.total_iva = iva;
      this.total_tax_inc = total;
    },
    loadOrderByBill() {
      if (this.creditNote.customization_id === "22") {
        return;
      }
      if (!this.creditNote.bill_id) {
        this.errorMessage = "Ingrese un comprobante válido";
        this.showErrorModal = true;
        return;
      }
      axios.get(`api/orders/byBill/${this.creditNote.bill_id}`, this.$root.config)
        .then(resp => {
          if (!resp.data.order_details) {
            this.errorMessage = "No se encontraron detalles para este comprobante";
            this.showErrorModal = true;
            return;
          }
          this.orderProducts = resp.data.order_details.map(od => ({
            id: od.id,
            order_id: od.order_id,
            barcode: od.barcode,
            product: od.product,
            price_tax_inc: od.price_tax_inc,
            quantity: od.quantity,
            maxQuantity: od.quantity
          }));
          if (resp.data.client) {
            const c = resp.data.client;
            this.orderClientName = c.razon_social || (c.first_name + " " + c.first_lastname) || "Sin Cliente";
            if (this.orderProducts.length > 0) {
              this.creditNote.order_id = this.orderProducts[0].order_id;
            }
            this.creditNote.client_id = c.id;
          }
          if (
            this.creditNote.customization_id === "20" &&
            this.creditNote.correction_concept_code === "1" &&
            this.orderProducts.length > 0 &&
            this.creditNoteItems.length === 0
          ) {
            this.showSelectProductsModal = true;
          }
          else if (
            this.creditNote.customization_id === "20" &&
            this.creditNote.correction_concept_code !== "1" &&
            this.orderProducts.length > 0
          ) {
            this.orderProducts.forEach(prod => {
              const newItem = {
                code_reference: prod.barcode,
                name: prod.product,
                quantity: prod.quantity,
                maxQuantity: prod.maxQuantity,
                discount_rate: 0,
                price: prod.price_tax_inc,
                tax_rate: "19.00",
                withholding_taxes: []
              };
              this.creditNoteItems.push(newItem);
            });
            this.orderProducts = [];
          }
        })
        .catch(err => {
          console.error("Error al cargar la orden:", err);
          this.errorMessage = "Error al cargar la orden";
          this.showErrorModal = true;
        });
    },
    addProductToCreditNote(index) {
      const prod = this.orderProducts[index];
      const newItem = {
        code_reference: prod.barcode,
        name: prod.product,
        quantity: prod.quantity,
        maxQuantity: prod.maxQuantity,
        discount_rate: 0,
        price: prod.price_tax_inc,
        tax_rate: "19.00",
        withholding_taxes: []
      };
      this.creditNoteItems.push(newItem);
      this.orderProducts.splice(index, 1);
    },
    closeSelectProductsModal() {
      this.showSelectProductsModal = false;
    },
    createCreditNote() {
      if (!this.creditNoteItems.length) {
        this.errorMessage = "No hay ítems en la Nota Crédito";
        this.showErrorModal = true;
        return;
      }
      this.loading = true;
      this.calculateTotals();
      const payload = {
        numbering_range_id: this.creditNote.numbering_range_id,
        correction_concept_code: this.creditNote.correction_concept_code,
        customization_id: this.creditNote.customization_id,
        bill_id: this.creditNote.bill_id,
        reference_code: this.creditNote.reference_code,
        observation: this.creditNote.observation,
        payment_method_code: this.creditNote.payment_method_code,
        order_id: this.creditNote.order_id,
        client_id: this.creditNote.client_id,
        items: this.creditNoteItems.map(item => ({
          code_reference: item.code_reference,
          name: item.name,
          quantity: parseInt(item.quantity),
          discount_rate: parseFloat(item.discount_rate),
          price: parseFloat(item.price),
          tax_rate: item.tax_rate.toString(),
          unit_measure_id: 70,
          standard_code_id: 1,
          is_excluded: 0,
          tribute_id: 1,
          withholding_taxes: []
        }))
      };
      axios.post("api/credit-notes/validate", payload, this.$root.config)
        .then(resp => {
          if (resp.data && resp.data.status === "success") {
            this.successMessage = resp.data.message || "Nota Crédito creada y validada con éxito";
            this.showSuccessModal = true;
          } else {
            this.errorMessage = (resp.data && resp.data.message) || "Ocurrió un error al crear la Nota Crédito";
            this.showErrorModal = true;
          }
        })
        .catch(err => {
          console.error("Error al crear la Nota Crédito:", err);
          if (err.response && err.response.data && err.response.data.message) {
            this.errorMessage = err.response.data.message;
          } else {
            this.errorMessage = "Ocurrió un error al crear la Nota Crédito";
          }
          this.showErrorModal = true;
        })
        .finally(() => {
          this.loading = false;
        });
    }
  }
};
</script>

<style scoped>
#create-credit-note {
  font-size: 1.1rem;
}

.bg-purple {
  background-color: #6f42c1 !important;
}
.alert-purple {
  background-color: #f5e9ff;
  border-color: #e2c7ff;
  color: #6f42c1;
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
.btn-purple {
  background-color: #6f42c1;
  color: #fff;
  border: 1px solid #6f42c1;
}
.btn-purple:hover {
  background-color: #5c359e;
  border-color: #5c359e;
}
.modal-backdrop.show {
  z-index: 1050 !important;
}
.modal.show {
  z-index: 1055 !important;
}
.modal-dialog {
  z-index: 1056 !important;
}
.modal {
  transition: opacity 0.15s linear;
}
</style>
