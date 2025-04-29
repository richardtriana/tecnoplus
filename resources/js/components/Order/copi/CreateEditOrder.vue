<template>
  <div class="row px-2" id="create-edit-order">
    <!-- Overlay de Loading -->
    <!-- Lo mantenemos en el template pero ya no lo usamos en el script -->
    <div v-if="loading" class="loading-overlay">
      <div class="spinner-border text-primary" role="status">
        <span class="sr-only">Cargando...</span>
      </div>
      <p>Cargando, por favor espere...</p>
    </div>

    <!-- PANEL LATERAL IZQUIERDO: Totales, Forma de Pago, etc. -->
    <div class="col-md-3">
      <section class="card">
        <!-- Estado de envío a DIAN -->
        <div class="alert m-2" :class="enviadoDianAlertClass" role="alert">
          <strong>Estado de envío a DIAN:</strong>
          <span v-if="selectedVoucherInfo">
            <span :style="{ color: selectedVoucherInfo.enviado_dian ? 'green' : 'red' }">
              {{ selectedVoucherInfo.enviado_dian ? 'Se enviará a DIAN' : 'No se enviará a DIAN' }}
            </span>
          </span>
          <span v-else class="text-muted">
            (No se ha seleccionado comprobante)
          </span>
        </div>

        <!-- Tabla de Subtotal, IVA, etc. -->
        <div>
          <table class="table table-sm table-teal text-right">
            <tr>
              <th colspan="7">Subtotal:</th>
              <!-- Formato con separador de miles y 2 decimales -->
              <th>
                $ {{ total_tax_exc.toLocaleString('es-CO', { minimumFractionDigits: 2 }) }}
              </th>
            </tr>
            <tr>
              <th colspan="7">IVA (total):</th>
              <th>
                $ {{ (total_tax_inc_without_discount - total_tax_exc).toLocaleString('es-CO', { minimumFractionDigits: 2 }) }}
              </th>
            </tr>
            <!-- Desglose de IVA -->
            <tr v-for="(taxValue, rate) in taxBreakdown" :key="rate">
              <th colspan="7">IVA {{ rate }}%:</th>
              <th>
                $ {{ taxValue.toLocaleString('es-CO', { minimumFractionDigits: 2 }) }}
              </th>
            </tr>
            <tr>
              <th colspan="7">Descuento:</th>
              <th>
                $ {{ total_discount.toLocaleString('es-CO', { minimumFractionDigits: 2 }) }}
              </th>
            </tr>
            <!-- Fila total, usando la clase "total-row" -->
            <tr class="total-row">
              <th colspan="7">Total:</th>
              <th>
                $ {{ total_tax_inc.toLocaleString('es-CO', { minimumFractionDigits: 2 }) }}
              </th>
            </tr>

            <!-- Forma de Pago -->
            <tr>
              <th colspan="7">Forma de Pago:</th>
              <th>
                <select class="form-control" v-model="order.payment_form_id" @change="onChangePaymentForm">
                  <option v-for="form in paymentForms" :key="form.id" :value="form.id">
                    {{ form.name }}
                  </option>
                </select>
              </th>
            </tr>

            <!-- Método de Pago: solo si code==1 (Pago de contado) -->
            <tr v-if="selectedPaymentForm && selectedPaymentForm.code == '1'">
              <th colspan="7">Método de Pago:</th>
              <th>
                <select class="form-control" v-model="order.payment_method_id">
                  <option v-for="method in paymentMethods" :key="method.id" :value="method.id">
                    {{ method.name }}
                  </option>
                </select>
              </th>
            </tr>

            <!-- Referencia de Pago: solo si NO es efectivo (code != '10') -->
            <tr
              v-if="
                selectedPaymentForm &&
                selectedPaymentForm.code == '1' &&
                selectedPaymentMethod &&
                selectedPaymentMethod.code != '10'
              "
            >
              <th colspan="7">Referencia de Pago:</th>
              <th>
                <input
                  type="text"
                  class="form-control"
                  v-model="order.payment_reference"
                  placeholder="Ingrese referencia de pago"
                />
              </th>
            </tr>

            <tr>
              <th colspan="7">Observaciones:</th>
              <th>
                <input type="text" class="form-control" v-model="order.observations" autocomplete="on" />
              </th>
            </tr>

            <!-- Comprobante -->
            <tr>
              <th colspan="7">Comprobante:</th>
              <th>
                <select class="form-control" v-model="selectedVoucher" v-if="activeVouchers.length">
                  <option v-for="voucher in activeVouchers" :key="voucher.id" :value="voucher.id">
                    {{ voucher.document }} - {{ voucher.prefix }}
                  </option>
                </select>
                <p class="text-muted" v-else>
                  No hay comprobantes activos
                </p>
              </th>
            </tr>
          </table>
        </div>
      </section>

      <!-- BOTONES DE ACCIÓN: 3 por fila, más grandes, con separación -->
      <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 g-2 mt-2">
        <!-- Botón Facturar (F1) -->
        <div class="col">
          <button
            type="button"
            class="btn btn-lg btn-aqua w-100"
            @click="facturarF1"
          >
            <i class="bi bi-receipt"></i>
            <b>F1</b>
            <br />
            Facturar
          </button>
        </div>
        <!-- Botón Pagar -->
        <div class="col">
          <router-link
            to="/orders"
            type="button"
            class="btn btn-lg btn-secondary w-100 text-center"
          >
            <i class="bi bi-cart-x"></i>
            <br />
            Pagar
          </router-link>
        </div>

        <!-- Botón Pedido -->
        <div class="col">
          <button
            type="button"
            class="btn btn-lg btn-outline-aqua w-100"
            @click="createOrUpdateOrder(1)"
          >
            <i class="bi bi-clock-fill"></i>
            <br />
            Pedido
          </button>
        </div>
        <!-- Botón Cotizar -->
        <div class="col">
          <button
            type="button"
            class="btn btn-lg btn-outline-aqua w-100"
            @click="createOrUpdateOrder(3)"
          >
            <i class="bi bi-list-check"></i>
            <br />
            Cotizar
          </button>
        </div>
        <!-- Botón Cancelar (solo si order_id != 0) -->
        <div class="col" v-if="order_id != 0">
          <router-link
            to="/orders"
            type="button"
            class="btn btn-lg btn-outline-secondary w-100 text-center"
          >
            <i class="bi bi-cart-x"></i>
            <br />
            Cancelar
          </router-link>
        </div>
        <!-- Botón Comprobantes -->
        <div class="col">
          <router-link
            to="/orders"
            type="button"
            class="btn btn-lg btn-info w-100 text-center"
          >
            <i class="bi bi-file-text"></i>
            <br />
            Recibos
          </router-link>
        </div>
      </div>
    </div>

    <!-- PANEL PRINCIPAL DERECHO: Barra superior + Tabla de productos -->
    <div class="col-9 p-2">
      <!-- Barra Superior: Caja a la izquierda y TOTAL a la derecha -->
      <div class="row top-bar align-items-center px-3 py-2 mb-2">
        <!-- Info Caja -->
        <div class="col-auto">
          <h5 class="mb-0">
            Caja:
            <span v-if="selectedBoxData" class="font-weight-bold">
              {{ selectedBoxData.name }}
            </span>
            <span v-else class="text-muted">
              (No hay caja seleccionada)
            </span>
          </h5>
          <button class="btn btn-caja btn-sm mt-1" @click="openSelectBoxModal">
            <i class="bi bi-arrow-repeat"></i> Cambiar Caja
          </button>
        </div>

        <!-- Espacio flexible -->
        <div class="col"></div>

        <!-- Total Grande a la derecha -->
        <div class="col-auto text-right">
          <span class="big-total">
            $ {{ total_tax_inc.toLocaleString('es-CO', { minimumFractionDigits: 2 }) }}
          </span>
        </div>
      </div>

      <!-- Fila de búsqueda: productos, clientes y mesa -->
      <div class="row position-sticky sticky-top bg-light p-1 mb-2" style="top: 0.5rem">
        <!-- Buscador de producto -->
        <div class="input-group col-6">
          <input
            type="text"
            class="form-control"
            placeholder="Código de barras"
            aria-label="with two button addons"
            aria-describedby="button-add-product"
            v-model="filters.product"
            autofocus
            @keypress.enter="searchProduct()"
          />
          <div class="input-group-append" id="button-add-product">
            <button class="btn btn-outline-secondary" type="button" @click="openAddProductModal">
              <b>F10</b> Añadir Producto
            </button>
            <button class="btn btn-outline-secondary" type="button" data-toggle="modal" data-target="#addProductModal">
              <i class="bi bi-card-checklist"></i>
            </button>
          </div>
        </div>
        <!-- Buscador de cliente -->
        <div class="input-group col-6">
          <input
            type="text"
            class="form-control"
            placeholder="Buscar Cliente"
            aria-label="with two button addons"
            aria-describedby="button-addon4"
            v-model="filters.client"
            @keypress.enter="searchClient()"
          />
          <div class="input-group-append" id="button-addon4">
            <button class="btn btn-outline-secondary" type="button" @click="searchClient()">
              Añadir Cliente
            </button>
            <button class="btn btn-outline-secondary" type="button" data-toggle="modal" data-target="#addClientModal">
              <i class="bi bi-person-lines-fill"></i>
            </button>
          </div>
        </div>
        <!-- Selección de mesa -->
        <div class="input-group col-6 mt-2">
          <v-select
            :options="tableList"
            placeholder="Seleccionar mesa"
            class="w-full form-input p-0 w-100"
            label="table"
            :reduce="(table) => table.id"
            v-model="order.table_id"
          />
        </div>
      </div>

      <!-- Listado de productos en el pedido -->
      <section>
        <div>
          <table class="table table-sm table-responsive-sm table-bordered table-hover">
            <thead class="bg-secondary text-white position-sticky sticky-top" style="top: 6.6rem">
              <tr>
                <th></th>
                <th>Código</th>
                <th>Producto</th>
                <th>Cantidad</th>
                <th>Precio (sin IVA)</th>
                <th>Porcentaje IVA</th>
                <th>Descuento %</th>
                <th>Total</th>
              </tr>
            </thead>
            <tbody v-if="productsOrderList.length > 0">
              <tr v-for="(p, index) in productsOrderList" :key="p.id">
                <td>
                  <button class="btn text-danger" @click="removeProduct(index, p.id)">
                    <i class="bi bi-trash"></i>
                  </button>
                  <button class="btn text-primary" @click="toggleObservaciones(index)">
                    <i class="bi bi-pencil-square"></i>
                  </button>
                </td>
                <td>{{ p.barcode }}</td>
                <td>{{ p.product }}</td>
                <td>
                  <input
                    type="number"
                    step="any"
                    placeholder="Cantidad"
                    class="form-control form-control-sm"
                    v-model="p.quantity"
                    style="max-width: 60px"
                  />
                </td>
                <!-- Precio sin IVA -->
                <td>
                  <input
                    type="number"
                    step="any"
                    placeholder="Precio sin IVA"
                    class="form-control form-control-sm"
                    v-model="p.price_tax_exc"
                    style="max-width: 100px"
                  />
                </td>
                <!-- Porcentaje IVA calculado -->
                <td class="text-right">
                  {{
                    p.price_tax_exc > 0
                      ? ((p.price_tax_inc / p.price_tax_exc - 1) * 100).toFixed(2) + '%'
                      : '0%'
                  }}
                </td>
                <!-- Descuento % -->
                <td>
                  <input
                    type="number"
                    step="any"
                    placeholder="Desc %"
                    class="form-control form-control-sm"
                    v-model="p.discount_percentage"
                    style="max-width: 60px"
                  />
                </td>
                <!-- Total -->
                <td class="text-right">
                  {{
                    (
                      (p.price_tax_inc * p.quantity) -
                      (p.price_tax_exc * p.quantity * (p.discount_percentage / 100))
                    ).toLocaleString('es-CO', { minimumFractionDigits: 2 })
                  }}
                </td>
              </tr>
              <!-- Fila extra para observaciones del producto -->
              <tr
                v-for="(p, index) in productsOrderList"
                :key="'obs-' + p.id"
                v-if="p.showObservaciones"
              >
                <td colspan="8">
                  <input
                    type="text"
                    class="form-control"
                    placeholder="Observaciones del producto"
                    v-model="p.observaciones"
                    @input="updateObservaciones(index)"
                  />
                </td>
              </tr>
            </tbody>
            <tbody v-else>
              <tr>
                <td colspan="8">No se han añadido productos</td>
              </tr>
            </tbody>
          </table>
        </div>
      </section>
    </div>

    <!-- MODAL DE PAGO -->
    <div class="modal fade" id="paymentModal" tabindex="-1" aria-labelledby="paymentModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <!-- Encabezado verde -->
          <div class="modal-header text-white bg-success">
            <h5 class="modal-title">Terminar Venta</h5>
            <button
              type="button"
              class="close"
              data-dismiss="modal"
              aria-label="Close"
              @click="removeModalKeydown"
            >
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <!-- Cuerpo del modal con nuevo diseño -->
          <div class="modal-body">
            <div class="d-flex justify-content-around mb-3">
              <!-- Columna Pago -->
              <div class="text-center">
                <h5>Pago</h5>
                <!-- Si es efectivo (code '10'), mostramos input de pago -->
                <template v-if="selectedPaymentMethod && selectedPaymentMethod.code == '10'">
                  <input
                    id="cashPayment"
                    type="number"
                    class="form-control form-control-lg text-center"
                    v-model="order.payment_methods.cash"
                    @keypress.enter="createOrUpdateOrder(2)"
                  />
                </template>
                <!-- Si NO es efectivo, mostramos referencia de pago -->
                <template v-else>
                  <input
                    type="text"
                    class="form-control form-control-lg text-center"
                    v-model="order.payment_reference"
                    placeholder="Ref. de pago"
                  />
                </template>
              </div>
              <!-- Columna Total -->
              <div class="text-center">
                <h5>Total</h5>
                <h3 class="text-primary">
                  $ {{ total_tax_inc.toLocaleString('es-CO', { minimumFractionDigits: 2 }) }}
                </h3>
              </div>
            </div>

            <!-- Cambio en grande (verde, sin recuadro) -->
            <div class="text-center mb-3">
              <h2 class="text-success" style="font-size: 2rem;">
                Cambio: $ {{ payment_return.toLocaleString('es-CO', { minimumFractionDigits: 2 }) }}
              </h2>
            </div>
            <!-- Se quita el valor pagado grande -->
          </div>

          <!-- Footer del modal con botones grandes -->
          <div class="modal-footer justify-content-center">
            <button
              type="button"
              class="btn btn-primary btn-lg mx-2"
              @click="createOrUpdateOrder(2)"
            >
              <i class="bi bi-receipt"></i> Facturar (F1)
            </button>
            <button
              type="button"
              class="btn btn-success btn-lg mx-2"
              @click="createOrUpdateOrder(4)"
            >
              <i class="bi bi-printer"></i> Facturar Imprimir (F2)
            </button>
            <button
              type="button"
              class="btn btn-danger btn-lg mx-2"
              data-dismiss="modal"
              @click="removeModalKeydown"
            >
              Salir (Esc)
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Otros componentes: AddProduct, AddClient, ModalBox, SelectBoxModal -->
    <add-product @add-product="addProduct($event)" />
    <add-client @add-client="addClient($event)" />
    <modal-box ref="ModalBox"></modal-box>
    <select-box-modal ref="SelectBoxModal" @box-selected="onBoxSelected" />
  </div>
</template>

<script>
import AddProduct from "./AddProductPOS.vue";
import AddClient from "./AddClient.vue";
import ModalBox from "./../ModalBox.vue";
import SelectBoxModal from "./SelectBoxModal.vue";
import axios from "axios";
import Swal from "sweetalert2";

export default {
  name: "create-edit-order",
  components: {
    AddProduct,
    AddClient,
    ModalBox,
    SelectBoxModal
  },
  props: ["order_id"],
  data() {
    return {
      loading: false,
      filters: {
        product: "",
        client: ""
      },
      productsOrderList: [],
      tableList: [],
      disabled: false,
      paymentForms: [],
      paymentMethods: [],
      order: {
        id_client: 1,
        client: "Sin Cliente",
        state: 1,
        total_tax_inc: 0.0,
        total_tax_exc: 0.0,
        total_discount: 0.0,
        total_cost_price_tax_inc: 0.0,
        productsOrder: [],
        payment_date: "",
        proccess: true,
        payment_form_id: null,
        payment_method_id: null,
        payment_reference: "",
        numbering_range_id: null,
        payment_methods: {
          cash: 0.0,
          card: 0.0,
          nequi: 0.0,
          others: 0.0
        }
      },
      selectedVoucher: null
    };
  },
  computed: {
    selectedBoxData() {
      if (!this.$root.box || !this.$root.listBoxes) return null;
      return this.$root.listBoxes.find(b => b.id == this.$root.box) || null;
    },
    activeVouchers() {
      if (!this.selectedBoxData) return [];
      if (!this.selectedBoxData.numbering_ranges) return [];
      const active = this.selectedBoxData.numbering_ranges.filter(v => v.is_active == 1);
      if (!this.selectedVoucher && active.length) {
        this.selectedVoucher = active[0].id;
      }
      return active;
    },
    selectedVoucherInfo() {
      if (!this.selectedVoucher) return null;
      return this.activeVouchers.find(v => v.id == this.selectedVoucher) || null;
    },
    enviadoDianAlertClass() {
      if (!this.selectedVoucherInfo) {
        return "alert alert-secondary";
      }
      return this.selectedVoucherInfo.enviado_dian
        ? "alert alert-success"
        : "alert alert-danger";
    },
    // Sumas y cálculos (internos)
    total_tax_exc() {
      return this.productsOrderList.reduce((sum, p) => sum + p.price_tax_exc * p.quantity, 0);
    },
    total_discount() {
      return this.productsOrderList.reduce((sum, p) => sum + p.quantity * p.price_tax_exc * (p.discount_percentage / 100), 0);
    },
    total_tax_inc() {
      return this.productsOrderList.reduce((sum, p) =>
        sum +
        p.price_tax_inc * p.quantity -
        p.price_tax_exc * p.quantity * (p.discount_percentage / 100)
      , 0);
    },
    total_tax_inc_without_discount() {
      return this.productsOrderList.reduce((sum, p) => sum + p.quantity * p.price_tax_inc, 0);
    },
    // Desglose de IVA
    taxBreakdown() {
      const taxes = {};
      this.productsOrderList.forEach(p => {
        let rate = p.price_tax_exc > 0 ? (p.price_tax_inc / p.price_tax_exc - 1) * 100 : 0;
        rate = parseFloat(rate.toFixed(2));
        const key = rate.toFixed(0);
        const lineSubtotal = p.price_tax_exc * p.quantity;
        const discountBase = lineSubtotal * (p.discount_percentage / 100);
        const baseAfterDiscount = lineSubtotal - discountBase;
        const lineTax = baseAfterDiscount * (rate / 100);
        taxes[key] = (taxes[key] || 0) + lineTax;
      });
      return taxes;
    },
    // Pagos
    paid_value() {
      const m = this.order.payment_methods;
      return Number(m.cash) + Number(m.card) + Number(m.nequi) + Number(m.others);
    },
    payment_return() {
      return this.paid_value - this.total_tax_inc;
    },
    selectedPaymentForm() {
      return this.paymentForms.find(f => f.id == this.order.payment_form_id) || null;
    },
    selectedPaymentMethod() {
      return this.paymentMethods.find(m => m.id == this.order.payment_method_id) || null;
    }
  },
  mounted() {
    const storedBox = localStorage.getItem("box_id");
    const storedVoucher = localStorage.getItem("selected_voucher");
    if (storedBox) {
      this.$root.box = storedBox;
      this.order.box_id = storedBox;
    }
    if (storedVoucher) {
      this.selectedVoucher = storedVoucher;
      this.order.numbering_range_id = storedVoucher;
    }
    if (this.order_id && this.order_id != 0) {
      this.listItemsOrder();
    }
    this.listTables();
    this.loadPaymentForms();
    this.loadPaymentMethods();
    this.commands();

    if (!this.$root.box) {
      this.$nextTick(() => {
        this.$refs.SelectBoxModal?.openModal();
      });
    } else if (this.order.numbering_range_id) {
      this.selectedVoucher = this.order.numbering_range_id;
    }

    this.order.payment_form_id = 1;
    this.order.payment_method_id = 1;
    if (this.order.state == 1) {
      const pv = this.activeVouchers.find(v => v.document === "Pedido");
      if (pv) this.order.numbering_range_id = pv.id;
    }
  },
  methods: {
    openSelectBoxModal() {
      this.$refs.SelectBoxModal.openModal();
    },
    onBoxSelected({ boxId, voucherId }) {
      this.$root.box = boxId;
      localStorage.setItem("box_id", boxId);
      localStorage.setItem("selected_voucher", voucherId);
      this.order.box_id = boxId;
      this.order.numbering_range_id = voucherId;
      this.selectedVoucher = voucherId;
    },
    loadPaymentForms() {
      axios.get("api/payment_forms", this.$root.config)
        .then(res => this.paymentForms = res.data.payment_forms)
        .catch(console.error);
    },
    loadPaymentMethods() {
      axios.get("api/payment_methods", this.$root.config)
        .then(res => this.paymentMethods = res.data.payment_methods)
        .catch(console.error);
    },
    onChangePaymentForm() {
      if (this.selectedPaymentForm?.code == "2") {
        this.order.payment_method_id = null;
        this.order.payment_reference = "";
      } else if (!this.order.payment_method_id) {
        this.order.payment_method_id = 1;
      }
    },
    listItemsOrder() {
      if (!this.order_id) return;
      axios.get(`api/orders/${this.order_id}`, this.$root.config)
        .then(({ data }) => {
          this.order = data.order_information;
          this.order.id_client = this.order.client_id;
          this.order.client = this.order.client.razon_social || this.order.client.name;
          this.productsOrderList = data.order_details;
          this.order.payment_form_id = 1;
          this.order.payment_method_id = 1;
        })
        .catch(console.error);
    },
    listTables() {
      axios.get("api/tables/table-list?page=1", this.$root.config)
        .then(resp => this.tableList = resp.data.tables)
        .catch(console.error);
    },
    searchProduct() {
      if (!this.filters.product) return;
      axios.post(
        `api/products/search-product?product=${this.filters.product}&state=1`,
        null,
        this.$root.config
      )
        .then(resp => {
          const np = resp.data.products;
          np ? this.addProduct(np) : $("#no-results").toast("show");
        })
        .catch(console.error);
      this.filters.product = "";
    },
    addProduct(new_product) {
      let exists = false;
      this.productsOrderList.forEach(prod => {
        if (prod.barcode === new_product.barcode) {
          exists = true;
          prod.quantity++;
          prod.price_tax_inc_total = prod.price_tax_inc * prod.quantity;
          prod.cost_price_tax_inc_total = prod.cost_price_tax_inc * prod.quantity;
        }
      });
      if (!exists) {
        const pd = {
          product_id: new_product.id,
          barcode: new_product.barcode,
          discount_percentage: 0,
          discount_price: 0,
          quantity: 1,
          price_tax_exc: new_product.sale_price_tax_exc,
          price_tax_inc: new_product.sale_price_tax_inc,
          product: new_product.product,
          price_tax_inc_total: new_product.sale_price_tax_inc,
          cost_price_tax_inc: new_product.cost_price_tax_inc,
          cost_price_tax_inc_total: new_product.cost_price_tax_inc,
          type: new_product.type,
          showObservaciones: false,
          observaciones: ""
        };
        if (new_product.uses_portions) {
          pd.portions = new_product.portions.map(portion => {
            let q = parseFloat(portion.quantity);
            if (!q || q <= 0) q = 1.00;
            return { ...portion, quantity: q.toFixed(2) };
          });
        }
        this.productsOrderList.push(pd);
      }
    },
    removeProduct(index, detail_id = null) {
      if (detail_id) {
        axios.delete(`api/orders/${this.order.id}/remove-product/${detail_id}`, this.$root.config)
          .then(() => {
            this.productsOrderList.splice(index, 1);
            alert("Producto eliminado y comanda de eliminación enviada.");
          })
          .catch(err => {
            console.error("Error al eliminar el producto:", err);
            alert("Error al eliminar el producto.");
          });
      } else {
        this.productsOrderList.splice(index, 1);
      }
    },
    toggleObservaciones(index) {
      this.productsOrderList[index].showObservaciones = !this.productsOrderList[index].showObservaciones;
    },
    updateObservaciones(index) {
      console.log(`Observaciones para: ${this.productsOrderList[index].product} => ${this.productsOrderList[index].observaciones}`);
    },
    openAddProductModal() {
      this.filters.product = "";
      $("#addProductModal").modal("show");
    },
    facturarF1() {
      if (!this.productsOrderList.length) {
        return Swal.fire({ icon: "error", title: "Oops...", text: "Debes añadir productos al carrito" });
      }
      this.openModalPayment();
    },
    openModalPayment() {
      if (this.selectedPaymentMethod?.code == "10") {
        this.order.payment_methods.cash = this.total_tax_inc;
      }
      $("#paymentModal").modal("show");
      $("#paymentModal").on("shown.bs.modal", () => {
        if (this.selectedPaymentMethod?.code == "10") {
          const ci = $("#cashPayment");
          ci.focus();
          ci.select();
        }
        document.addEventListener("keydown", this.modalKeyHandler);
      });
    },
    removeModalKeydown() {
      document.removeEventListener("keydown", this.modalKeyHandler);
    },
    modalKeyHandler(e) {
      if (e.key === "F1") {
        e.preventDefault();
        this.createOrUpdateOrder(2);
      }
      if (e.key === "F2") {
        e.preventDefault();
        this.createOrUpdateOrder(4);
      }
    },
    async createOrUpdateOrder(state_order) {
  // Verifica que haya productos
  if (!this.productsOrderList.length) {
    return Swal.fire({
      icon: "error",
      title: "Oops...",
      text: "Debes añadir productos al carrito"
    });
  }

  // Verifica si es efectivo y no alcanza el pago
  if (
    state_order === 2 &&
    this.selectedPaymentMethod?.code === "10" &&
    this.paid_value < this.total_tax_inc
  ) {
    return this.openModalPayment();
  }

  // Verifica si pasa a crédito sin cliente válido
  if (this.order.id_client === 1 && state_order === 5) {
    return alert("Debe seleccionar un cliente válido");
  }

  // Mostrar loading de SweetAlert2
  Swal.fire({
    title: "Guardando, por favor espere...",
    allowOutsideClick: false,
    didOpen: () => {
      Swal.showLoading();
    }
  });

  // Ajustar el objeto order
  this.order.state = state_order;
  this.order.box_id = this.$root.box;

  if (state_order === 1) {
    const pv = this.activeVouchers.find(v => v.document === "Pedido");
    if (pv) this.order.numbering_range_id = pv.id;
    this.order.proccess = false;
  } else {
    this.order.numbering_range_id = this.selectedVoucher;
  }

  this.order.total_tax_exc = this.total_tax_exc;
  this.order.total_tax_inc = this.total_tax_inc;
  this.order.total_discount = this.total_discount;
  this.order.total_cost_price_tax_inc = this.total_cost_price_tax_inc;
  this.order.productsOrder = this.productsOrderList;

  // Llamar a la API
  const req = (this.order_id && this.order_id != 0)
    ? axios.put(`api/orders/${this.order_id}`, this.order, this.$root.config)
    : axios.post("api/orders", this.order, this.$root.config);

  req
    .then(({ data }) => {
      const warning = data.warning;

      // Construir el HTML del modal de éxito
      let htmlMessage = `<p>Los datos se han guardado correctamente.</p>`;

      if (warning) {
        htmlMessage += `
          <div class="alert alert-warning text-left mt-3">
            <strong>Error de envío:</strong> ${warning}.<br>
          </div>
        `;
      }

      // Mostrar SweetAlert2 con diseño mejorado
      Swal.fire({
        icon: "success",
        title: "Excelente",
        html: htmlMessage,
        confirmButtonText: "OK"
      }).then(() => {
        window.location.reload();
      });
    })
    .catch(err => {
      console.error(err);
      Swal.fire({
        icon: "error",
        title: "Oops...",
        text: "Hubo un error al guardar los datos"
      });
    })
    .finally(() => {
      // Cerrar modal de pago y navegar al home
      $("#paymentModal").modal("hide");
      this.$router.push({ path: "/" });
    });
},

    commands() {
      shortcut.add("F1", () => {
        if (!this.productsOrderList.length) {
          Swal.fire({ icon: "error", title: "Oops...", text: "Debes añadir productos al carrito" });
        } else {
          this.openModalPayment();
        }
      });
      shortcut.add("F2", () => {
        if (!this.productsOrderList.length) {
          Swal.fire({ icon: "error", title: "Oops...", text: "Debes añadir productos al carrito" });
        } else {
          this.openModalPayment();
        }
      });
      shortcut.add("F10", () => this.openAddProductModal());
    },
    searchClient() {
      if (!this.filters.client) return;
      axios.post(`api/clients/search-client?client=${this.filters.client}`, null, this.$root.config)
        .then(resp => {
          const nc = resp.data;
          nc ? this.addClient(nc) : $("#no-results").toast("show");
        })
        .catch(console.error);
    },
    addClient(client) {
      this.order.id_client = client.id;
      const cname = client.razon_social || client.name ||
        `${client.first_name} ${client.second_name || ""} ${client.first_lastname} ${client.second_lastname || ""}`.trim();
      this.order.client = cname;
      this.filters.client = cname;
    }
  }
};
</script>

<style scoped>
/* 
  =============================
  PALETA DE COLORES (TEMA TEAL)
  -----------------------------
  - tealOscuro:   #006064
  - tealMedio:    #00838F
  - tealHover:    #004f59  (más oscuro)
  - textoBlanco:  #fff
  - fondoClaro:   #f0f9f9  (opcional para backgrounds)
  =============================
*/

/* 
  BARRA SUPERIOR
*/
.top-bar {
  background: linear-gradient(45deg, #006064 0%, #00838F 100%);
  color: #fff;
}
.big-total {
  font-size: 2rem;
  font-weight: bold;
}

/* Botón "Cambiar Caja" */
.btn-caja {
  background-color: #006064;
  color: #fff;
  border: 1px solid #004f59;
}
.btn-caja:hover {
  background-color: #004f59;
  color: #fff;
}

/* Tabla Totales */
.table-teal {
  background-color: #00838F !important;
  color: #fff !important;
}
.table-teal th,
.table-teal td {
  color: #fff !important;
}
.table-teal tr.total-row {
  background-color: #006064 !important;
  font-weight: bold;
  color: #fff !important;
}

/* Cabecera tabla de productos */
thead.bg-secondary {
  background-color: #006064 !important;
}
thead.bg-secondary th {
  color: #fff !important;
}

/* Botones Teal */
.btn-aqua {
  background-color: #00838F;
  color: #fff;
  border: none;
}
.btn-aqua:hover {
  background-color: #006f75;
  color: #fff;
}
.btn-outline-aqua {
  border: 1px solid #00838F;
  color: #00838F;
  background-color: transparent;
}
.btn-outline-aqua:hover {
  background-color: #00838F;
  color: #fff;
}

/* Overlay de Loading */
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

/* Ajustes generales */
#create-edit-order {
  font-size: 1.1rem;
}
</style>
