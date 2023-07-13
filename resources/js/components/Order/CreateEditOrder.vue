<template>
  <div class="row px-2" id="create-edit-order">
    <div class="col-9 justify-content-center p-2">
      <div class="sticky-top mb-2 text-uppercase w-50" style="z-index: 1022; left: 100%">
        <table class="table table-borderless">
          <tr class="h1 text-white bg-success">
            <td class="text-right">Total</td>
            <td>$ {{ order.total_tax_inc = total_tax_inc | currency }}</td>
          </tr>
        </table>
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
            aria-describedby="button-add-product" v-model="filters.product" autofocus @keypress.enter="searchProduct()" />
          <div class="input-group-append" id="button-add-product">
            <button class="btn btn-outline-secondary" type="button" @click="searchProduct()">
              <b>F10</b>
              Añadir Producto
            </button>
            <button class="btn btn-outline-secondary" type="button" data-toggle="modal" data-target="#addProductModal">
              <i class="bi bi-card-checklist"></i>
            </button>
          </div>
        </div>
        <div class="input-group col-6">
          <input type="text" class="form-control" :placeholder="order.client" aria-label=" with two button addons"
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
        <div class="input-group col-6"></div>
        <div class="input-group inline-flex col-6">
          <v-select :options="tableList"  placeholder="Seleccionar mesa" class="w-full form-input p-0 w-100" label="table"
            :reduce="(table) => table.id" v-model="order.table_id" />
        </div>
      </div>

      <section>
        <div>
          <table class="
                table table-sm table-responsive-sm table-bordered table-hover
              ">
            <thead class="bg-secondary text-white position-stickyx sticky-topx" style="top: 4rem">
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

                <td>{{ p.barcode }}</td>
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
                  <input type="number" name="price" id="price" step="any" placeholder="Precio" v-model="p.price_tax_inc"
                    class="form-control form-control-sm" style="max-width: 100px" />
                </td>
                <td>
                  <input type="number" name="discount_percentage" id="discount_percentage" step="any"
                    placeholder="Porcentaje descuento" class="form-control form-control-sm"
                    v-model="p.discount_percentage" style="max-width: 60px" />
                </td>
                <td>
                  <input type="number" class="form-control form-control-sm" name="discount_price" id="discount_price"
                    step="2" placeholder="Descuento" disabled :value="
                      (p.discount_price = (
                        p.quantity *
                        p.price_tax_inc *
                        (p.discount_percentage / 100)
                      ).toFixed(0))
                    " readonly style="max-width: 100px" />
                </td>
                <td>
                  $
                  {{
                    (p.price_tax_inc_total =
                      p.quantity * p.price_tax_inc -
                      p.quantity *
                      p.price_tax_inc *
                      (p.discount_percentage / 100))
                  }}
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

        <section>

          <div class="modal fade" id="paymentModal" tabindex="-1" aria-labelledby="paymentModalLabel" aria-hidden="true">
            <div class="modal-dialog">
              <div class="modal-content">
                <div class="modal-header  text-white bg-success">
                  <h5 class="modal-title">Terminar venta</h5>
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                  </button>
                </div>
                <form>
                  <div class="modal-body">
                    <h4 class="w-100 font-weight-bold text-center"> Total
                      {{ total_tax_inc | currency }}
                    </h4>

                    <div class="form-group">
                      <label class="h6" for="cashPayment">El cliente paga: {{order.payment_methods.cash|currency}}</label>
                      <input type="number" autofocus class="form-control form-control-lg" id="cashPayment" aria-describedby="cashHelp"
                        v-model="order.payment_methods.cash" @keypress.enter="createOrUpdateOrder(2)">
                    </div>

                    <h4 class="font-weight-bold"> Cambio
                      {{ order.payment_methods.cash - total_tax_inc | currency }}
                    </h4>

                  </div>
                  <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="button" :disabled="paid_value < total_tax_inc" class="btn btn-outline-primary btn-block"
                    @click="createOrUpdateOrder(2)">
                    <!-- Facturar -->
                    <i class="bi bi-receipt"></i> <b>F1</b> Facturar
                  </button>
                  <button type="button" :disabled="paid_value < total_tax_inc" class="btn btn-outline-primary btn-block"
                    @click="createOrUpdateOrder(4)">
                    <!-- Facturar -->
                    <i class="bi bi-receipt"></i> <b>F2</b> Facturar e imprimir
                  </button>

                  </div>
                </form>
              </div>
            </div>
          </div>

        </section>

        <section class="card">
          <div>
            <table class="table table-sm table-primary text-right">
              <tr>
                <th colspan="7">Subtotal:</th>
                <th>
                  $ {{ (order.total_tax_exc = total_tax_exc).toFixed(0) }}
                </th>
              </tr>
              <tr>
                <th colspan="7">IVA:</th>
                <th>
                  $
                  {{
                    (total_tax_inc_without_discount - total_tax_exc).toFixed(0)
                  }}
                </th>
              </tr>
              <tr>
                <th colspan="7">Descuento:</th>
                <th>
                  $
                  {{ (order.total_discount = total_discount).toFixed(0) }}
                </th>
              </tr>
              <tr class="bg-success h5 text-white">
                <th colspan="7">Total:</th>
                <th>
                  $ {{ (order.total_tax_inc = total_tax_inc).toFixed(0) }}
                </th>
              </tr>
              <tr class="">
                <th colspan="7">Efectivo:</th>
                <th>
                  <input type="number" value="0" step="any" v-model="order.payment_methods.cash" required />
                </th>
              </tr>
              <tr class="">
                <th colspan="7">Nequi:</th>
                <th>
                  <input type="number" value="0" step="any" v-model="order.payment_methods.nequi" />
                </th>
              </tr>
              <tr class="">
                <th colspan="7">Tarjeta:</th>
                <th>
                  <input type="number" value="0" step="any" v-model="order.payment_methods.card" />
                </th>
              </tr>
              <tr class="">
                <th colspan="7">Otros:</th>
                <th>
                  <input type="number" value="0" step="any" v-model="order.payment_methods.others" />
                </th>
              </tr>
              <tr class="">
                <th colspan="7">Cambio:</th>
                <th>
                  <input type="text" :value="payment_return" readonly disabled />
                </th>
              </tr>
              <!-- <tr class="">
                <th colspan="7">Fecha de pago:</th>
                <th>
                  <input
                    type="datetime-local"
                    v-model="order.payment_date"
                    autocomplete=""
                  />
                </th>
              </tr> -->
              <tr class="">
                <th colspan="7">Observaciones:</th>
                <th>
                  <input type="text" v-model="order.observations" autocomplete="on" />
                </th>
              </tr>
            </table>
          </div>
        </section>
        <div class="">
          <div class="my-2">
            <label for="selected_box_user" class="font-weight-bold-bold">Caja <i class="bi bi-box"></i></label>
            <select name="selected_box_user" id="selected_box_user" class="form-control" v-model="$root.box">
              <option value="" disabled>Seleccione una caja</option>
              <option v-for="item in $root.listBoxes" :value="item.id" :key="item.id">
                {{ item.name + " " + item.prefix }}
              </option>
            </select>
          </div>
          <button type="button" :disabled="paid_value < total_tax_inc" class="btn btn-outline-primary btn-block"
            @click="createOrUpdateOrder(2)">
            <!-- Facturar -->
            <i class="bi bi-receipt"></i> <b>F1</b> Facturar
          </button>
          <!-- <button type="button" :disabled="paid_value < total_tax_inc" class="btn btn-outline-primary btn-block"
            @click="createOrUpdateOrder(4)">
            <i class="bi bi-receipt"></i> <b>F2</b> Facturar e imprimir
          </button> -->
          <button type="button" :disabled="disabled" class="btn btn-outline-primary btn-block"
            @click="createOrUpdateOrder(5)">
            <!-- Credito -->
            <i class="bi bi-wallet2"></i> Pasar a crédito
          </button>
          <button type="button" :disabled="disabled" class="btn btn-outline-primary btn-block"
            @click="createOrUpdateOrder(1)">
            <i class="bi bi-clock-fill"></i> Suspender
          </button>

          <button type="button" class="btn btn-outline-primary btn-block" @click="createOrUpdateOrder(3)">
            <i class="bi bi-list-check"></i> Cotizar
          </button>

          <router-link to="/orders" type="button" class="btn btn-secondary btn-block">
            <i class="bi bi-cart-x"></i> Pagar
          </router-link>

          <router-link to="/orders" type="button" class="btn btn-outline-secondary btn-block" v-if="order_id != 0">
            <i class="bi bi-cart-x"></i> Cancelar
          </router-link>
        </div>
      </div>
    </div>

    <add-product @add-product="addProduct($event)" />
    <add-client @add-client="addClient($event)" />
    <modal-box ref="ModalBox"></modal-box>
  </div>
</template>

<script>
import AddProduct from "./AddProduct.vue";
import AddClient from "./AddClient.vue";
import ModalBox from "./../ModalBox.vue";

export default {
  components: { AddProduct, AddClient, ModalBox },
  props: ["order_id"],
  name: "create-edit-order",

  data() {
    return {
      // add product or client with keyup
      filters: {
        product: "",
        client: "",
      },
      productsOrderList: [],
      tableList: [],
      disabled: false,
      order: {
        id_client: 1,
        client: "Sin Cliente",
        state: 1,
        total_tax_inc: 0.0,
        total_tax_exc: 0.0,
        total_discount: 0.0,
        total_cost_price_tax_inc: 0.0,
        productsOrder: [],
        payment_date: '',
        // payment_date: new Date().toISOString().slice(0, 10),
        payment_methods: {
          cash: 0.0,
          card: 0.0,
          nequi: 0.0,
          others: 0.0,
          change: 0.0,
        },
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
    total_tax_inc_without_discount: function () {
      var total = 0.0;
      this.productsOrderList.forEach((product) => {
        total += parseFloat(product.quantity * product.price_tax_inc);
      });
      return total;
    },

    paid_value: function () {

      var cash = this.order.payment_methods.cash
        ? Number(this.order.payment_methods.cash)
        : 0;
      var card = this.order.payment_methods.card
        ? Number(this.order.payment_methods.card)
        : 0;
      var nequi = this.order.payment_methods.nequi
        ? Number(this.order.payment_methods.nequi)
        : 0;
      var others = this.order.payment_methods.others
        ? Number(this.order.payment_methods.others)
        : 0;

      var paid = cash + nequi + card + others;
      return paid;

    },

    payment_return: function () {
      var value = this.paid_value - this.total_tax_inc;
      return value;
    }
  },
  methods: {
    listItemsOrder() {
      if (this.order_id == 0) {
        return false;
      }

      let me = this;
      axios
        .get(`api/orders/${me.order_id}`, this.$root.config)
        .then(function (response) {
          me.order = response.data.order_information;
          me.order.id_client = response.data.order_information.client_id;
          me.order.client = response.data.order_information.client.name;
          me.productsOrderList = response.data.order_details;
        });
    },
    listTables() {
      let me = this;
      axios
        .get("api/tables/table-list?page=1", me.$root.config)
        .then(function (response) {
          me.tableList = response.data.tables;
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
          type: new_product.type,
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
      me.order.id_client = client.id;
      me.order.client = client.name;
      me.filters.client = client.name;
    },

    createOrUpdateOrder(state_order) {
      this.disabled = true;
      this.order.state = state_order;
      this.order.box_id = this.$root.box;
      this.order.total_cost_price_tax_inc = this.total_cost_price_tax_inc;
      this.order.payment_methods.change = this.payment_return;

      if (this.order.id_client == 1 && state_order == 5) {
        alert("Debe seleccionar un cliente válido");
        return false;
      }
      if (this.productsOrderList.length) {        
        shortcut.remove("F1")
        shortcut.remove("F2")
        this.order.productsOrder = this.productsOrderList;

        if (this.order_id != 0 && this.order_id != null) {
          axios
            .put(`api/orders/${this.order_id}`, this.order, this.$root.config)
            .then(() =>
              Swal.fire({
                icon: "success",
                title: "Excelente",
                text: "Los datos se han guardado correctamente",
              })
            )
            .catch(function (error) {
              // handle error
              console.log("error", error);
              if (error) {
                Swal.fire({
                  icon: "error",
                  title: "Oops...",
                  text: "Hubo un error al guardar los datos",
                });
              }
            })
            .finally(
              setTimeout(() => {
                this.$router.push({
                  name: 'create-edit-order',
                  params: {
                    order_id: 0
                  }
                }),
                  this.$router.go(0),
                  (this.disabled = false)
              }, 1000)
            );
        } else {
          if (this.order.box_id > 0) {
              axios
              .post(`api/orders`, this.order, this.$root.config)
              .then((response) => {
                Swal.fire({
                  icon: "success",
                  title: "Excelente",
                  text: "Los datos se han guardado correctamente",
                });
              })
              .catch(function (error) {
                // handle error
                if (error) {
                  Swal.fire({
                    icon: "error",
                    title: "Oops...",
                    text: "Hubo un error al guardar los datos",
                  });
                }
              })
              .finally(
                setTimeout(() => {
                  this.$router.go(0), (this.disabled = false)
                }, 3000)
              );
          } else {
            alert("Selecciona una caja");
          }
        }
      } else {
        this.disabled = false;
        Swal.fire({
          icon: "error",
          title: "Oops...",
          text: "Debes añadir productos al carrito",
        });
      }
      return false;
    },

    printTicket(order_id) {
      axios.get(
        `api/print-order/${order_id}/${this.order.payment_methods.cash}/${this.payment_return}`,
        this.$root.config
      );
    },
    commands() {
      let me = this;

      shortcut.add("F1", function () {
        if (me.paid_value < me.total_tax_inc) {
          Swal.fire({
            icon: "error",
            title: "Debe completar su pago ",
            text: "Puede añadir varias formas de pago",
          }).then(() => {
            me.openModalPayment()
          });
        } else {
          me.createOrUpdateOrder(2);
        }
      });

      shortcut.add("F2", function () {
        if (me.paid_value < me.total_tax_inc) {
          Swal.fire({
            icon: "error",
            title: "Debe completar su pago ",
            text: "Puede añadir varias formas de pago",
          }).then(() => {
            me.openModalPayment()
          });
        } else {
          me.createOrUpdateOrder(4);
        }
      });

      shortcut.add("F10", function () {
        $("#addProductModal").modal("show");
      });
    },

    openModalPayment() {
      $('#paymentModal').modal('show')
      $('#paymentModal').on('shown.bs.modal', function () {
        $('#cashPayment').trigger('focus')
      })
    }
  },
  mounted() {
    $("#no-results").toast("hide");
    if (this.order_id != null || this.order_id != 0) {
      this.listItemsOrder();
    }
    this.listTables()
    this.commands();
    this.$refs.ModalBox.selectedBox();
  },
};
</script>

<style scoped>
#create-edit-order {
  font-size: 1.1rem;
}
</style>