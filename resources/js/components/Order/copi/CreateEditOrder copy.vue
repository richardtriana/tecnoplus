<template>
  <div class="row px-2" id="create-edit-order">
    <!-- Overlay de Loading -->
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
              <th>$ {{ total_tax_exc.toFixed(0) }}</th>
              </tr>
              <tr>
              <th colspan="7">IVA:</th>
              <th>$ {{ (total_tax_inc_without_discount - total_tax_exc).toFixed(0) }}</th>
              </tr>
              <tr>
              <th colspan="7">Descuento:</th>
              <th>$ {{ total_discount.toFixed(0) }}</th>
              </tr>
              <!-- Fila total, usando la clase "total-row" -->
              <tr class="total-row">
              <th colspan="7">Total:</th>
              <th>$ {{ total_tax_inc.toFixed(0) }}</th>
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
        <!-- Botón Crédito -->
        <div class="col">
          <button
            type="button"
            class="btn btn-lg btn-outline-aqua w-100"
            @click="createOrUpdateOrder(5)"
          >
            <i class="bi bi-wallet2"></i>
            <br />
            Crédito
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
            <!-- Se muestra el total, redondeado sin decimales -->
             {{ total_tax_inc.toFixed(0) | currency }}
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
                <th>Valor IVA</th>
                <th>Descuento %</th>
                <th>Descuento $</th>
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
                  {{ p.price_tax_exc > 0 ? (((p.price_tax_inc / p.price_tax_exc) - 1) * 100).toFixed(2) + '%' : '0%' }}
                </td>
                <!-- Valor IVA por unidad -->
                <td class="text-right">
                  {{ p.price_tax_exc > 0 ? (p.price_tax_inc - p.price_tax_exc).toFixed(2) : '0.00' }}
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
                <!-- Descuento en valor -->
                <td class="text-right">
                  {{ (p.quantity * p.price_tax_exc * (p.discount_percentage / 100)).toFixed(2) }}
                </td>
                <!-- Total -->
                <td class="text-right">
                  {{ ((p.price_tax_inc * p.quantity) - (p.quantity * p.price_tax_exc * (p.discount_percentage / 100))).toFixed(2) }}
                </td>
              </tr>
              <!-- Fila extra para observaciones del producto -->
              <tr v-for="(p, index) in productsOrderList" :key="'obs-' + p.id" v-if="p.showObservaciones">
                <td colspan="10">
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
                <td colspan="10">No se han añadido productos</td>
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
          <div class="modal-header text-white bg-success">
            <h5 class="modal-title">Terminar venta</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close" @click="removeModalKeydown">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <form>
            <div class="modal-body">
              <!-- Fila con 3 columnas: Total / Pago / Cambio o Referencia -->
              <div class="row text-center align-items-center">
                <!-- Columna 1: Total -->
                <div class="col-12 col-md-4 mb-3 mb-md-0">
                  <h5 class="font-weight-bold mb-1">Total</h5>
                  <h4 class="text-primary">{{ total_tax_inc | currency }}</h4>
                </div>
                <!-- Columna 2: Pago -->
                <div class="col-12 col-md-4 mb-3 mb-md-0">
                  <h5 class="font-weight-bold mb-1">Pago:</h5>
                  <div v-if="selectedPaymentMethod && selectedPaymentMethod.code == '10'">
                    <h4 class="text-primary mb-2">{{ order.payment_methods.cash | currency }}</h4>
                    <input
                      type="number"
                      autofocus
                      class="form-control form-control-lg text-center"
                      id="cashPayment"
                      aria-describedby="cashHelp"
                      v-model="order.payment_methods.cash"
                      @keypress.enter="createOrUpdateOrder(2)"
                    />
                  </div>
                  <div v-else>
                    <input
                      type="number"
                      class="form-control text-center"
                      v-model="order.payment_methods.cash"
                      placeholder="Ingrese valor de pago"
                    />
                  </div>
                </div>
                <!-- Columna 3: Cambio si efectivo, o Referencia si no -->
                <div class="col-12 col-md-4">
                  <template v-if="selectedPaymentMethod && selectedPaymentMethod.code == '10'">
                    <h5 class="font-weight-bold mb-1">Cambio:</h5>
                    <h4 class="text-primary">{{ payment_return | currency }}</h4>
                  </template>
                  <template v-else>
                    <h5 class="font-weight-bold mb-1">Referencia de Pago:</h5>
                    <input
                      type="text"
                      class="form-control text-center"
                      v-model="order.payment_reference"
                      placeholder="Ref. de pago"
                    />
                  </template>
                </div>
              </div>
            </div>

            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-dismiss="modal" @click="removeModalKeydown">
                Cancelar
              </button>
              <!-- Botón Facturar (F1) => state=2 -->
              <button type="button" class="btn btn-outline-primary btn-block" @click="createOrUpdateOrder(2)">
                <i class="bi bi-receipt"></i> Facturar (F1)
              </button>
              <!-- Botón Facturar Imprimir (F2) => state=4 -->
              <button type="button" class="btn btn-outline-success btn-block" @click="createOrUpdateOrder(4)">
                <i class="bi bi-printer"></i> Facturar Imprimir (F2)
              </button>
            </div>
          </form>
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
    total_tax_exc() {
      let total = 0;
      this.productsOrderList.forEach(p => {
        total += p.price_tax_exc * p.quantity;
      });
      return total;
    },
    total_discount() {
      let total = 0;
      this.productsOrderList.forEach(p => {
        total += p.quantity * p.price_tax_exc * (p.discount_percentage / 100);
      });
      return total;
    },
    total_tax_inc() {
      let total = 0;
      this.productsOrderList.forEach(p => {
        total += p.price_tax_inc * p.quantity -
          (p.price_tax_exc * p.quantity * (p.discount_percentage / 100));
      });
      return total;
    },
    total_tax_inc_without_discount() {
      let total = 0;
      this.productsOrderList.forEach(p => {
        total += p.quantity * p.price_tax_inc;
      });
      return total;
    },
    paid_value() {
      let cash = this.order.payment_methods.cash || 0;
      let card = this.order.payment_methods.card || 0;
      let nequi = this.order.payment_methods.nequi || 0;
      let others = this.order.payment_methods.others || 0;
      return Number(cash) + Number(card) + Number(nequi) + Number(others);
    },
    payment_return() {
      return this.paid_value - this.total_tax_inc;
    },
    selectedPaymentForm() {
      if (!this.order.payment_form_id) return null;
      return this.paymentForms.find(f => f.id == this.order.payment_form_id) || null;
    },
    selectedPaymentMethod() {
      if (!this.order.payment_method_id) return null;
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
        if (this.$refs.SelectBoxModal) {
          this.$refs.SelectBoxModal.openModal();
        }
      });
    } else {
      if (this.order.numbering_range_id) {
        this.selectedVoucher = this.order.numbering_range_id;
      }
    }

    // Por defecto, forma y método de pago
    this.order.payment_form_id = 1;
    this.order.payment_method_id = 1;

    // Si es pedido
    if (this.order.state == 1) {
      const pedidoVoucher = this.activeVouchers.find(v => v.document === "Pedido");
      if (pedidoVoucher) {
        this.order.numbering_range_id = pedidoVoucher.id;
      }
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
      axios
        .get("api/payment_forms", this.$root.config)
        .then(res => {
          this.paymentForms = res.data.payment_forms;
        })
        .catch(err => console.error(err));
    },
    loadPaymentMethods() {
      axios
        .get("api/payment_methods", this.$root.config)
        .then(res => {
          this.paymentMethods = res.data.payment_methods;
        })
        .catch(err => console.error(err));
    },
    onChangePaymentForm() {
      if (this.selectedPaymentForm && this.selectedPaymentForm.code == "2") {
        this.order.payment_method_id = null;
        this.order.payment_reference = "";
      } else {
        if (!this.order.payment_method_id) {
          this.order.payment_method_id = 1;
        }
      }
    },
    listItemsOrder() {
      if (this.order_id == 0) return;
      axios
        .get(`api/orders/${this.order_id}`, this.$root.config)
        .then(response => {
          this.order = response.data.order_information;
          this.order.id_client = response.data.order_information.client_id;
          this.order.client =
            response.data.order_information.client.razon_social ||
            response.data.order_information.client.name;
          this.productsOrderList = response.data.order_details;
          // Forzar comprobante en edición
          this.order.payment_form_id = 1;
          this.order.payment_method_id = 1;
          if (this.order.payment_reference) {
            this.order.payment_reference = this.order.payment_reference;
          }
        })
        .catch(err => console.error(err));
    },
    listTables() {
      axios
        .get("api/tables/table-list?page=1", this.$root.config)
        .then(resp => {
          this.tableList = resp.data.tables;
        })
        .catch(err => console.error(err));
    },
    searchProduct() {
      if (!this.filters.product) return;
      const url = `api/products/search-product?product=${this.filters.product}&state=1`;
      axios
        .post(url, null, this.$root.config)
        .then(resp => {
          const new_product = resp.data.products;
          if (!new_product) {
            $("#no-results").toast("show");
          } else {
            this.addProduct(new_product);
          }
        })
        .catch(err => console.error(err));
      this.filters.product = "";
    },
    addProduct(new_product) {
      let exists = false;
      this.productsOrderList.forEach(prod => {
        if (prod.barcode === new_product.barcode) {
          exists = true;
          prod.quantity += 1;
          prod.price_tax_inc_total = prod.price_tax_inc * prod.quantity;
          prod.cost_price_tax_inc_total = prod.cost_price_tax_inc * prod.quantity;
        }
      });
      if (!exists) {
        this.productsOrderList.push({
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
        });
      }
    },
    removeProduct(index, detail_id = null) {
      if (detail_id) {
        axios
          .delete(`api/orders/${this.order.id}/remove-product/${detail_id}`, this.$root.config)
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
      console.log(
        "Observaciones para:",
        this.productsOrderList[index].product,
        "=>",
        this.productsOrderList[index].observaciones
      );
    },
    openAddProductModal() {
      this.filters.product = "";
      $("#addProductModal").modal("show");
    },
    // Botón Facturar (F1) => abre el modal, pero primero verifica que haya productos
    facturarF1() {
      if (!this.productsOrderList.length) {
        Swal.fire({
          icon: "error",
          title: "Oops...",
          text: "Debes añadir productos al carrito"
        });
        return;
      }
      this.openModalPayment();
    },
    openModalPayment() {
      if (this.selectedPaymentMethod && this.selectedPaymentMethod.code == "10") {
        this.order.payment_methods.cash = this.total_tax_inc;
      }
      $("#paymentModal").modal("show");
      $("#paymentModal").on("shown.bs.modal", () => {
        if (this.selectedPaymentMethod && this.selectedPaymentMethod.code == "10") {
          const cashInput = $("#cashPayment");
          cashInput.focus();
          cashInput.select();
        }
        document.addEventListener("keydown", this.modalKeyHandler);
      });
    },
    removeModalKeydown() {
      document.removeEventListener("keydown", this.modalKeyHandler);
    },
    modalKeyHandler(e) {
      // Facturar => state=2
      if (e.key === "F1") {
        e.preventDefault();
        this.createOrUpdateOrder(2);
      }
      // Facturar e Imprimir => state=4
      if (e.key === "F2") {
        e.preventDefault();
        this.createOrUpdateOrder(4);
      }
    },
    createOrUpdateOrder(state_order) {
      // Verifica que haya productos
      if (!this.productsOrderList.length) {
        this.disabled = false;
        Swal.fire({
          icon: "error",
          title: "Oops...",
          text: "Debes añadir productos al carrito"
        });
        return;
      }
      // Verifica si es efectivo y no alcanza el pago
      if (state_order == 2 && this.selectedPaymentMethod && this.selectedPaymentMethod.code == "10" && this.paid_value < this.total_tax_inc) {
        this.openModalPayment();
        return;
      }
      // Verifica si pasa a crédito sin cliente válido
      if (this.order.id_client == 1 && state_order == 5) {
        alert("Debe seleccionar un cliente válido");
        this.disabled = false;
        return;
      }
      if (this.disabled) return;
      this.disabled = true;

      // Activar el loading mientras se guarda
      this.loading = true;

      this.order.state = state_order; // 2 => Facturar, 4 => Facturar e imprimir
      this.order.box_id = this.$root.box;
      if (state_order == 1) {
        // Pedido
        const pedidoVoucher = this.activeVouchers.find(v => v.document === "Pedido");
        if (pedidoVoucher) {
          this.order.numbering_range_id = pedidoVoucher.id;
        }
        this.order.proccess = false;
      } else {
        this.order.numbering_range_id = this.selectedVoucher;
      }
      this.order.total_tax_exc = this.total_tax_exc;
      this.order.total_tax_inc = this.total_tax_inc;
      this.order.total_discount = this.total_discount;
      this.order.total_cost_price_tax_inc = this.total_cost_price_tax_inc;
      this.order.productsOrder = this.productsOrderList;

      // Si es update o create
      if (this.order_id != 0 && this.order_id != null) {
        axios
          .put(`api/orders/${this.order_id}`, this.order, this.$root.config)
          .then(() =>
            Swal.fire({
              icon: "success",
              title: "Excelente",
              text: "Los datos se han guardado correctamente"
            })
          )
          .catch(err => {
            console.log(err);
            Swal.fire({
              icon: "error",
              title: "Oops...",
              text: "Hubo un error al guardar los datos"
            });
          })
          .finally(() => {
            setTimeout(() => {
              this.$router.push({ name: "create-edit-order", params: { order_id: 0 } });
              this.$router.go(0);
              this.disabled = false;
              this.loading = false;
            }, 300);
          });
      } else {
        if (this.order.box_id > 0) {
          axios
            .post("api/orders", this.order, this.$root.config)
            .then(() => {
              Swal.fire({
                icon: "success",
                title: "Excelente",
                text: "Los datos se han guardado correctamente"
              });
            })
            .catch(err => {
              console.log(err);
              Swal.fire({
                icon: "error",
                title: "Oops...",
                text: "Hubo un error al guardar los datos"
              });
            })
            .finally(() => {
              setTimeout(() => {
                this.$router.go(0);
                this.disabled = false;
                this.loading = false;
              }, 300);
            });
        } else {
          alert("Selecciona una caja");
          this.disabled = false;
          this.loading = false;
        }
      }
    },
    commands() {
      // F1 y F2 abren el modal, pero primero verificamos si hay productos
      shortcut.add("F1", () => {
        if (!this.productsOrderList.length) {
          Swal.fire({
            icon: "error",
            title: "Oops...",
            text: "Debes añadir productos al carrito"
          });
        } else {
          this.openModalPayment();
        }
      });
      shortcut.add("F2", () => {
        if (!this.productsOrderList.length) {
          Swal.fire({
            icon: "error",
            title: "Oops...",
            text: "Debes añadir productos al carrito"
          });
        } else {
          this.openModalPayment();
        }
      });
      // F10 => añadir producto
      shortcut.add("F10", () => {
        this.openAddProductModal();
      });
    },
    searchClient() {
      if (!this.filters.client) return;
      const url = `api/clients/search-client?client=${this.filters.client}`;
      axios
        .post(url, null, this.$root.config)
        .then(resp => {
          const new_client = resp.data;
          if (!new_client) {
            $("#no-results").toast("show");
          } else {
            this.addClient(new_client);
          }
        })
        .catch(err => console.error(err));
    },
    addClient(client) {
      this.order.id_client = client.id;
      const clientName =
        client.razon_social ||
        client.name ||
        `${client.first_name} ${client.second_name ? client.second_name : ''} ${client.first_lastname} ${client.second_lastname ? client.second_lastname : ''}`;
      this.order.client = clientName.trim();
      this.filters.client = clientName.trim();
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
  BARRA SUPERIOR (ya existente, ajustada a teal)
  - Degradado diagonal entre tealOscuro y tealMedio
  - Texto en blanco
*/
.top-bar {
  background: linear-gradient(45deg, #006064 0%, #00838F 100%);
  color: #fff; 
  /* border-radius: 0.25rem;  <-- Descomenta si quieres esquinas redondeadas */
}

/* Texto grande para el total (a la derecha en la barra) */
.big-total {
  font-size: 2rem;   /* Ajusta el tamaño */
  font-weight: bold; /* Ajusta el grosor */
}

/* 
  BOTÓN "Cambiar Caja"
  - Fondo teal oscuro
  - Hover un tono más oscuro
*/
.btn-caja {
  background-color: #006064; /* tealOscuro */
  color: #fff; 
  border: 1px solid #004f59; /* tealHover */
  /* padding: 0.4rem 0.6rem;    <-- Ajusta a tu gusto */
  /* border-radius: 0.25rem; */
}
.btn-caja:hover {
  background-color: #004f59; /* tealHover */
  color: #fff;
}

/* 
  PANEL LATERAL (opcional):
  - Si deseas un fondo claro en el panel lateral de Totales, 
    descomenta esto o ajusta el color.
*/
/*
.side-panel {
  background-color: #f0f9f9; 
  border: 1px solid #d0e0e0;
  border-radius: 0.25rem;
  padding: 1rem;
}
*/

/* 
  TABLA DE TOTALES
  - Reemplaza "table-primary" en tu HTML por "table-teal" 
  - Quita "bg-success" de la fila total y usa la clase "total-row" que verás abajo
*/
.table-teal {
  background-color: #00838F !important; /* tealMedio */
  color: #fff !important;
  /* border-radius: 0.25rem; */
  /* border: none;  <-- si quieres sin borde */
}
.table-teal th,
.table-teal td {
  color: #fff !important; /* Texto en blanco */
}
.table-teal tr.total-row {
  background-color: #006064 !important; /* tealOscuro */
  font-weight: bold;
  color: #fff !important;
}

/* 
  ALERTAS (Estado de envío a DIAN)
  - Si deseas un fondo teal en lugar de 'alert-success/alert-danger',
    podrías usar algo como .alert-teal en tu HTML
*/
/*
.alert-teal {
  background-color: #00838F;
  color: #fff;
  border: none;
}
*/

/* 
  TABLA DE PRODUCTOS (cabecera)
  - Si usas .bg-secondary en <thead>, podrías sobreescribirla con teal:
*/
thead.bg-secondary {
  background-color: #006064 !important; /* tealOscuro */
}
thead.bg-secondary th {
  color: #fff !important;
}

/* 
  BOTONES TEAL (equivalentes a .btn-aqua)
  - Botón sólido teal
*/
.btn-aqua {
  background-color: #00838F; /* tealMedio */
  color: #fff;
  border: none;
  /* border-radius: 0.25rem; */
}
.btn-aqua:hover {
  background-color: #006f75; /* un tono más oscuro */
  color: #fff;
}

/* 
  BOTONES TEAL (outline)
  - Borde teal, fondo transparente
*/
.btn-outline-aqua {
  border: 1px solid #00838F;
  color: #00838F;
  background-color: transparent;
  /* border-radius: 0.25rem; */
}
.btn-outline-aqua:hover {
  background-color: #00838F;
  color: #fff;
}

/* 
  OVERLAY DE LOADING 
*/
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

/* 
  AJUSTES GENERALES DEL CONTENEDOR 
*/
#create-edit-order {
  font-size: 1.1rem; /* Ajusta el tamaño base de fuente */
  /* background-color: #f0f9f9;  <-- Descomenta si quieres un fondo claro general */
}
</style>

