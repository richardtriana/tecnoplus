<template>
  <div>
    <!-- Modal -->
    <div
      class="modal fade"
      id="modalPaymentCredit"
      data-backdrop="static"
      data-keyboard="false"
      tabindex="-1"
      aria-labelledby="modalPaymentCreditLabel"
      aria-hidden="true"
    >
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="modalPaymentCreditLabel">Abonar</h5>
            <button
              @click="resetData()"
              type="button"
              class="close"
              data-dismiss="modal"
              aria-label="Close"
            >
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <div class="input-group col-12">
              <input
                type="text"
                class="form-control"
                :placeholder="paymentCredit.client"
                aria-label=" with two button addons"
                aria-describedby="button-addon4"
                v-model="labelClient"
                @keypress.enter="searchClient()"
              />
              <div class="input-group-append" id="button-addon4">
                <button
                  class="btn btn-outline-secondary"
                  type="button"
                  @click="searchClient()"
                >
                  Añadir Cliente
                </button>
                <button
                  class="btn btn-outline-secondary"
                  type="button"
                  data-toggle="modal"
                  data-target="#addClientModal"
                >
                  <i class="bi bi-person-lines-fill"></i>
                </button>
              </div>
            </div>
            <div v-if="listPending.length > 0" class="m-2">
              <table class="table table-sm table-bordered table-responsive-sm">
                <thead>
                  <tr>
                    <th>#</th>
                    <th>Total Pago</th>
                    <th>Abono</th>
                    <th>Saldo</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="pending in listPending" :key="pending.id">
                    <th scope="row">
                      {{ pending.id }} - {{ pending.no_invoice }}
                    </th>
                    <td>{{ pending.total_paid }}</td>
                    <td class="bg-success">{{ pending.paid_payment }}</td>
                    <td
                      :class="{
                        'bg-danger':
                          pending.total_paid - pending.paid_payment > 0,
                      }"
                    >
                      {{ pending.total_paid - pending.paid_payment }}
                    </td>
                  </tr>
                  <tr>
                    <td colspan="3">Total:</td>
                    <td
                      :class="{
                        'bg-danger': totalBalancePending > 0,
                      }"
                    >
                      {{ totalBalancePending }}
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
            <div v-if="totalBalancePending > 0" class="mx-2">
              <table class="table table-sm table-bordered table-responsive-sm">
                <tr class="">
                  <th colspan="7">Efectivo:</th>
                  <th>
                    <input
                      class="form-control form-control-sm"
                      type="number"
                      value="0"
                      step="any"
                      v-model="cash"
                      
                    />
                  </th>
                </tr>
                <tr class="">
                  <th colspan="7">Abono</th>
                  <th>
                    <input
                      class="form-control form-control-sm"
                      type="number"
                      value="0"
                      step="any"
                      v-model="paymentCredit.pay_payment"
                      :max="totalBalancePending"
                    />
                  </th>
                </tr>
                <tr class="">
                  <th colspan="7">Cambio:</th>
                  <th>
                    <input
                      class="form-control form-control-sm"
                      type="text"
                      :value="paymentReturn"
                      readonly
                      disabled
                    />
                  </th>
                </tr>
              </table>
            </div>
          </div>
          <div class="modal-footer">
            <button
              type="button"
              class="btn btn-secondary"
              data-dismiss="modal"
              @click="resetData()"
            >
              Cancelar
            </button>
            <button type="button" class="btn btn-primary" @click="payCredit()">Guardar</button>
          </div>
        </div>
      </div>
    </div>
    <add-client @add-client="addClient($event)" />
  </div>
</template>

<script >
import AddClient from "../Order/AddClient.vue";

export default {
  components: { AddClient },
  data() {
    return {
      labelClient: "",
      cash: 0,
      paymentCredit: {
        id_client: null,
        client: "Sin cliente",
        pay_payment: 0,
      },
      listPending: [],
    };
  },
  watch:{
    
  },
  computed: {
    totalBalancePending() {
      let balance = 0;

      if (this.listPending.length > 0) {
        this.listPending.forEach((item) => {
          balance += item.total_paid - item.paid_payment;
        });
      }
      return balance;
    },
    paymentReturn: function () {
      let value = 0.0;
      let total_pending = this.totalBalancePending;
      if (this.cash > 0) {
        if (this.paymentCredit.pay_payment > total_pending) {
          this.paymentCredit.pay_payment = total_pending;
        }

        value = (this.cash - this.paymentCredit.pay_payment).toFixed(0);

        if(value < 0){
          this.paymentCredit.pay_payment = this.cash;
          value = 0;
        }
      }
      return value;
    },
  },
  methods: {
    searchClient() {
      let me = this;
      if (me.labelClient == "") {
        return false;
      }
      let url = "api/clients/search-client?client=" + me.labelClient;
      axios
        .post(url, null, me.$root.config)
        .then(function (response) {
          var new_client = response.data;
          if (!new_client) {
            //$("#no-results").toast("show");
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
      me.paymentCredit.id_client = client.id;
      me.labelClient = client.name;
      me.paymentCredit.client = client.name;
      me.creditPendingByClient(client.id);
    },

    creditPendingByClient(clientId) {
      let me = this;
      let url = "api/orders/byClient/" + clientId;
      axios
        .get(url, me.$root.config)
        .then(function (response) {
          me.listPending = response.data.orders;
          console.log(typeof me.listPending);
          console.log(me.listPending.length);
        })
        .catch(function (error) {
          console.log(error);
        });
    },
    resetData() {
      this.paymentCredit.id_client = null;
      this.paymentCredit.client = "Sin cliente";
      this.paymentCredit.pay_payment = 0;
      this.labelClient = "";
      this.cash = 0;
      this.listPending = [];
    },
    payCredit() {
      let me = this;
      axios
        .post('api/orders/payCreditByClient', this.paymentCredit, me.$root.config)
        .then(function (response) {
          me.resetData();
          $('#modalPaymentCredit').modal('hide');
          me.$emit('get-credits');
        })
        .catch(function (error) {
          console.log(error);
        });
    },
  },
};
</script>

