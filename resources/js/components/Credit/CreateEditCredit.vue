<template>
  <div class="row px-2">
    <div class="col-9 justify-content-center p-2">
      <div class="sticky-top mb-2 text-uppercase w-50" style="z-index: 1022; left: 100%">
        <table class="table table-borderless">
          <tr class="h1 text-white bg-success">
            <td class="text-right">Total</td>
            <td>
              $
              {{ (credit.total_tax_inc = total_tax_inc).toFixed(0) }}
            </td>
          </tr>
        </table>

        <!-- </div> -->
      </div>
      <div class="position-fixed top-0 right-0 w-50" style="z-index: 3000">
        <div class="toast fade hide border border-danger w-100 m-3" style="max-width: 90%" role="alert" id="no-results"
          aria-live="assertive" aria-atomic="true" data-delay="3000">
          <div class="toast-header">
            <strong class="mr-auto h3 text-danger">Advertencia</strong>
            <button type="button" class="ml-2 mb-1 close" data-dismiss="toast" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="toast-body text-dark h4">
            No se ha encontrado coincidencias
          </div>
        </div>
      </div>
      <div class="row position-sticky sticky-top mb-2 bg-light p-1" style="top: 0.5rem">
        <div class="input-group col-6">
          <input type="text" class="form-control" placeholder="Código de barras" aria-label=" with two button addons"
            aria-describedby="button-add-product" v-model="filters.product" autofocus
            @keypress.enter="searchProduct()" />
          <div class="input-group-append" id="button-add-product">
            <button class="btn btn-outline-secondary" type="button" @click="searchProduct()">
              Añadir Producto
            </button>
            <button class="btn btn-outline-secondary" type="button" data-toggle="modal" data-target="#addProductModal">
              <i class="bi bi-card-checklist"></i>
            </button>
          </div>
        </div>
        <div class="input-group col-6">
          <input type="text" class="form-control" :placeholder="credit.client" aria-label=" with two button addons"
            aria-describedby="button-addon4" v-model="filters.client" @keypress.enter="searchClient()" />
          <div class="input-group-append" id="button-addon4">
            <button class="btn btn-outline-secondary" type="button" @click="searchClient()">
              Añadir Cliente
            </button>
            <button class="btn btn-outline-secondary" type="button" data-toggle="modal" data-target="#addClientModal">
              <i class="bi bi-person-lines-fill"></i>
            </button>
          </div>
        </div>
      </div>

      <section>
        <div>
          <table class="
              table table-sm table-responsive-sm table-bordered table-hover
            ">
            <thead class="bg-secondary text-white position-sticky sticky-top" style="top: 4rem">
              <tr>
                <th></th>
                <th>Código</th>
                <th>Producto</th>
                <th>Cantidad</th>
                <th>Precio</th>
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
                </td>

                <td class="barcode">{{ p.barcode }}</td>
                <td>{{ p.product }}</td>
                <td>
                  <input type="number" name="quantity" id="quantity" step="any" placeholder="Cantidad"
                    class="form-control form-control-sm" v-model="p.quantity" style="max-width: 60px" />
                  <span class="hidden d-none">
                    {{
                        (p.cost_price_tax_inc_total =
                          p.cost_price_tax_inc * p.quantity)
                    }}
                  </span>
                </td>
                <td>
                  <input type="number" name="price" id="price" step="any" placeholder="Cantidad"
                    v-model="p.price_tax_inc" class="form-control form-control-sm" style="max-width: 100px" />
                </td>
                <td>
                  <input type="number" name="discount_percentage" id="discount_percentage" step="any"
                    placeholder="Descuento" class="form-control form-control-sm" v-model="p.discount_percentage"
                    style="max-width: 60px" />
                </td>
                <td>
                  <input v-if="p.discount_percentage != 0" type="number" class="form-control form-control-sm"
                    name="discount_price" id="discount_price" step="2" placeholder="Descuento" :value="
                      (p.discount_price = (
                        p.quantity *
                        p.price_tax_inc *
                        (p.discount_percentage / 100)
                      ).toFixed(0))
                    " style="max-width: 100px" />
                  <input v-else type="number" class="form-control form-control-sm" name="discount_price"
                    id="discount_price" step="2" v-model="p.discount_price" style="max-width: 100px" />
                </td>
                <td>
                  <span v-if="p.discount_percentage != 0">
                    $
                    {{
                        (p.price_tax_inc_total =
                          p.quantity * p.price_tax_inc -
                          p.quantity *
                          p.price_tax_inc *
                          (p.discount_percentage / 100))
                    }}
                  </span>
                  <span v-else>
                    $
                    {{
                        (p.price_tax_inc_total =
                          p.quantity * p.price_tax_inc -
                          p.discount_price).toFixed(2)
                    }}
                  </span>
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
    <div class="col-md-3">
      <div class="">
        <section class="card">
          <div>
            <table class="table table-sm table-primary text-right">
              <tr>
                <th colspan="7">Subtotal:</th>
                <th>
                  $
                  {{ (credit.total_tax_exc = total_tax_exc).toFixed(0) }}
                </th>
              </tr>
              <tr>
                <th colspan="7">IVA:</th>
                <th>
                  $
                  {{ (total_tax_inc - total_tax_exc).toFixed(0) }}
                </th>
              </tr>
              <tr>
                <th colspan="7">Descuento:</th>
                <th>
                  $
                  {{ (credit.total_discount = total_discount).toFixed(0) }}
                </th>
              </tr>
              <tr class="bg-success h5 text-white">
                <th colspan="7">Total:</th>
                <th>
                  $
                  {{ (credit.total_tax_inc = total_tax_inc).toFixed(0) }}
                </th>
              </tr>
              <tr class="">
                <th colspan="7">Efectivo:</th>
                <th>
                  <input type="number" value="0" step="any" v-model="credit.cash" />
                </th>
              </tr>
              <tr class="">
                <th colspan="7">Abono</th>
                <th>
                  <input type="number" value="0" step="any" v-model="credit.pay_payment" />
                </th>
              </tr>
              <tr class="">
                <th colspan="7">Cambio:</th>
                <th>
                  <input type="text" :value="payment_return" readonly disabled />
                </th>
              </tr>
              <tr class="">
                <th colspan="7">Fecha de pago:</th>
                <th>
                  <input type="datetime-local" v-model="credit.payment_date" autocomplete="" />
                </th>
              </tr>
            </table>
          </div>
        </section>
        <div class="">
          <div class="my-2">
            <label for="selected_box_user" class="font-weight-bold">Caja <i class="bi bi-box"></i></label>
            <select name="selected_box_user" id="selected_box_user" class="form-control" v-model="$root.box">
              <option value="" disabled>Seleccione una caja</option>
              <option v-for="item in $root.listBoxes" :value="item.id" :key="item.id">
                {{ item.name + " " + item.prefix }}
              </option>
            </select>
          </div>
          <button type="button" class="btn btn-outline-primary btn-block" @click="createOrUpdateCredit(5)">
            <!-- Facturar -->
            <i class="bi bi-receipt"></i> Guardar Crédito
          </button>
          <button type="button" class="btn btn-outline-primary btn-block" @click="createOrUpdateCredit(6)">
            <!-- Facturar -->
            <i class="bi bi-receipt"></i> Guardar e imprimir
          </button>
          <router-link to="/credits" type="button" class="btn btn-outline-secondary btn-block" v-if="order_id != 0">
            <i class="bi bi-receipt"></i> Cancelar
          </router-link>
        </div>
      </div>
    </div>

    <add-product @add-product="addProduct($event)" is_order="1" />
    <add-client @add-client="addClient($event)" />
    <modal-box ref="ModalBox"></modal-box>
  </div>
</template>

<script>
import AddProduct from "../Order/AddProduct.vue";
import AddClient from "../Order/AddClient.vue";
import ModalBox from "./../ModalBox.vue";

export default {
  components: { AddProduct, AddClient, ModalBox },
  props: ["order_id"],

  data() {
    return {
      // add product or client with keyup
      filters: {
        product: "",
        client: "",
      },
      productsOrderList: [],

      credit: {
        id_client: 1,
        client: "Sin cliente",
        state: 1,
        total_tax_inc: 0.0,
        total_tax_exc: 0.0,
        total_discount: 0.0,
        total_cost_price_tax_inc: 0.0,
        productsOrder: [],
        cash: 0,
        change: 0,
        pay_payment: 0,
        payment_date: "",
      },
    };
  },
  computed: {
    total_tax_exc: function () {
      var total = 0.0;
      this.productsOrderList.forEach(
        (product) =>
          (total += parseFloat(product.price_tax_exc * product.quantity))
      );
      return total;
    },
    total_discount: function () {
      var total = 0.0;
      this.productsOrderList.forEach((product) => {
        total += parseFloat(product.discount_price);
      });
      return total;
    },
    total_cost_price_tax_inc: function () {
      var total = 0.0;
      this.productsOrderList.forEach((product) => {
        total += parseFloat(product.cost_price_tax_inc_total);
      });
      return total;
    },
    total_tax_inc: function () {
      var total = 0.0;
      this.productsOrderList.forEach((product) => {
        total += parseFloat(
          product.quantity * product.price_tax_inc -
          product.quantity *
          product.price_tax_inc *
          (product.discount_percentage / 100)
        );
      });
      return total;
    },
    payment_return: function () {
      var value = 0.0;
      if (this.credit.cash > 0) {
        if (this.credit.pay_payment > this.credit.total_tax_inc) {
          this.credit.pay_payment = 0;
          alert(
            "No puedes abonar una cantidad superior al valor de la factura"
          );
        } else {
          value = (this.credit.cash - this.credit.pay_payment).toFixed(0);
        }
      }
      return value;
    },
  },
  methods: {
    listItemsCredit() {
      if (this.order_id == 0) {
        return false;
      }
      let me = this;

      axios
        .get(`api/orders/${me.order_id}`, this.$root.config)
        .then(function (response) {
          me.credit.id_client = response.data.order_information.client_id;
          me.credit.client = response.data.order_information.client.name;

          me.productsOrderList = response.data.order_details;
        });
    },
    searchProduct() {
      let me = this;
      if (me.filters.product == "") {
        return false;
      }
      var url = "api/products/search-product?product=" + me.filters.product;
      axios
        .post(url, null, this.$root.config)
        .then(function (response) {
          var new_product = response.data.products;
          if (!new_product) {
            $("#no-results").toast("show");
          } else {
            me.addProduct(new_product);
          }
        })
        .catch(function (error) {
          console.log(error);
        });
      me.filters.product = "";
    },
    addProduct(new_product) {
      let me = this;
      let result = false;
      // Verifica si el producto existe en la lista
      me.productsOrderList.filter((prod) => {
        if (new_product.barcode == prod.barcode) {
          result = true;
          if (result) {
            // Añade cantidad
            prod.quantity += 1;
            prod.price_tax_inc_total = prod.price_tax_inc * prod.quantity;
            prod.cost_price_tax_inc_total =
              prod.cost_price_tax_inc * prod.quantity;
          }
        }
      });

      if (!result) {
        // Sino, lo añade al array
        me.productsOrderList.push({
          product_id: new_product.id,
          barcode: new_product.barcode,
          discount_percentage: 0,
          discount_price: 0,
          quantity: 1,
          price_tax_inc: new_product.sale_price_tax_inc,
          price_tax_exc: new_product.sale_price_tax_exc,
          product: new_product.product,
          price_tax_inc_total: new_product.sale_price_tax_inc,
          cost_price_tax_inc: new_product.cost_price_tax_inc,
          cost_price_tax_inc_total: new_product.cost_price_tax_inc,
        });
      }
    },
    removeProduct(index, detail_id = null) {
      this.productsOrderList.splice(index, 1);
      if (detail_id != null || detail_id != 0) {
        axios.delete(`api/order-details/${detail_id}`, this.$root.config);
      }
    },
    searchClient() {
      let me = this;
      if (me.filters.client == "") {
        return false;
      }
      var url = "api/clients/search-client?client=" + me.filters.client;
      axios
        .post(url, null, me.$root.config)
        .then(function (response) {
          var new_client = response.data;
          if (!new_client) {
            $("#no-results").toast("show");
          } else {
            me.addClient(new_client);
          }
        })
        .catch(function (error) {
          console.log(error);
        });
    },
    addClient(client) {
      let me = this;
      me.credit.id_client = client.id;
      me.credit.client = client.name;
      me.filters.client = client.name;
    },

    createOrUpdateCredit(state_credit) {
      this.credit.state = state_credit;
      this.credit.box_id = this.$root.box;

      if (this.credit.id_client == 1) {
        alert("Debe seleccionar un cliente válido");
        return false;
      }

      if (this.productsOrderList.length > 0) {
        this.credit.total_cost_price_tax_inc = this.total_cost_price_tax_inc;
        this.credit.productsOrder = this.productsOrderList;

        if (this.order_id != 0 && this.order_id != null) {
          axios
            .put(`api/orders/${this.order_id}`, this.credit, this.$root.config)
            .then(() => this.$router.push("/credits"));
        } else {
          if (this.credit.box_id > 0) {
            axios
              .post(`api/orders`, this.credit, this.$root.config)
              .then(() => this.$router.go(0));
          } else {
            alert("Selecciona una caja");
          }
        }
      } else {
        Swal.fire({
          icon: "error",
          title: "Oops...",
          text: "Debes añadir productos al carrito",
        });
      }
    },
    commands() {
      let me = this;
      shortcut.add("F1", function () {
        me.createOrUpdateCredit(5);
      });

      shortcut.add("F2", function () {
        me.createOrUpdateCredit(6);
      });

      shortcut.add("F10", function () {
        $("#addProductModal").modal("show");
      });
    },
  },
  mounted() {
    $("#no-results").toast("hide");
    if (this.order_id != null || this.order_id != 0) {
      this.listItemsCredit();
    }
    this.commands();
    this.$refs.ModalBox.selectedBox();
  },
};
</script>
