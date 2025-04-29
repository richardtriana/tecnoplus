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
            <!-- Cliente -->
            <div class="input-group mb-3">
              <input
                type="text"
                class="form-control"
                :placeholder="paymentCredit.client"
                v-model="labelClient"
                @keypress.enter="searchClient()"
              />
              <div class="input-group-append">
                <button class="btn btn-outline-secondary" type="button" @click="searchClient()">
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

            <!-- Lista de créditos pendientes -->
            <div v-if="listPending.length" class="mb-3">
              <table class="table table-sm table-bordered">
                <thead>
                  <tr>
                    <th>#</th>
                    <th>Total Crédito</th>
                    <th>Abonado</th>
                    <th>Saldo</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="pending in listPending" :key="pending.id">
                    <th scope="row">{{ pending.id }} - {{ pending.no_invoice }}</th>
                    <td>{{ pending.total_paid | currency }}</td>
                    <td>{{ pending.paid_payment | currency }}</td>
                    <td :class="{ 'bg-danger text-white': pending.total_paid - pending.paid_payment > 0 }">
                      {{ (pending.total_paid - pending.paid_payment) | currency }}
                    </td>
                  </tr>
                  <tr>
                    <td colspan="3" class="text-right font-weight-bold">Total:</td>
                    <td :class="{ 'bg-danger text-white': totalBalancePending > 0 }" class="font-weight-bold">
                      {{ totalBalancePending | currency }}
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <!-- Abono -->
            <div v-if="totalBalancePending > 0">
              <div class="form-group">
                <label for="inputPayment">Monto a abonar</label>
                <input
                  id="inputPayment"
                  type="number"
                  class="form-control"
                  v-model.number="paymentCredit.pay_payment"
                  :max="totalBalancePending"
                  min="0"
                  step="any"
                />
                <!-- Valor formateado -->
                <small class="d-block mt-1 text-primary">
                  {{ formattedPay }}
                </small>
              </div>
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
            <button
              type="button"
              class="btn btn-primary"
              @click="payCredit()"
              :disabled="!canPay"
            >
              Guardar
            </button>
          </div>
        </div>
      </div>
    </div>

    <add-client @add-client="addClient($event)" />
  </div>
</template>

<script>
import AddClient from "../Order/AddClient.vue";

export default {
  components: { AddClient },
  data() {
    return {
      labelClient: "",
      paymentCredit: {
        id_client: null,
        client: "Sin cliente",
        pay_payment: 0,
      },
      listPending: [],
    };
  },
  computed: {
    totalBalancePending() {
      return this.listPending.reduce(
        (sum, o) => sum + (o.total_paid - o.paid_payment),
        0
      );
    },
    // Formatea el abono con separadores de miles
    formattedPay() {
      const v = Number(this.paymentCredit.pay_payment) || 0;
      return new Intl.NumberFormat("es-CO", {
        style: "currency",
        currency: "COP",
      }).format(v);
    },
    // Sólo habilita el botón si hay cliente, monto > 0 y <= saldo
    canPay() {
      return (
        this.paymentCredit.id_client &&
        this.paymentCredit.pay_payment > 0 &&
        this.paymentCredit.pay_payment <= this.totalBalancePending
      );
    },
  },
  filters: {
    currency(value) {
      return new Intl.NumberFormat("es-CO", {
        style: "currency",
        currency: "COP",
      }).format(value);
    },
  },
  watch: {
    // No permitir que el usuario escriba más que el saldo
    "paymentCredit.pay_payment"(val) {
      if (val > this.totalBalancePending) {
        this.paymentCredit.pay_payment = this.totalBalancePending;
      }
      if (val < 0) {
        this.paymentCredit.pay_payment = 0;
      }
    },
  },
  methods: {
    // Abre el modal y recibe el crédito seleccionado
    open(credit) {
      this.paymentCredit.id_client = credit.order.client.id;
      this.paymentCredit.client =
        credit.order.client.razon_social || credit.order.client.name;
      // Cargar detalles pendientes
      this.listPending = [credit.order]; // suponiendo que siempre trabajas con un solo pedido
      this.paymentCredit.pay_payment = 0;
      $("#modalPaymentCredit").modal("show");
    },
    resetData() {
      this.labelClient = "";
      this.paymentCredit = {
        id_client: null,
        client: "Sin cliente",
        pay_payment: 0,
      };
      this.listPending = [];
    },
    searchClient() {
      if (!this.labelClient) return;
      axios
        .post(
          `api/clients/search-client?client=${this.labelClient}`,
          null,
          this.$root.config
        )
        .then(({ data }) => {
          if (data) this.addClient(data);
        });
    },
    addClient(client) {
      this.paymentCredit.id_client = client.id;
      this.paymentCredit.client = client.name;
      // recarga (si lo necesitas, aquí sólo uno)
      this.listPending = [];
    },
    payCredit() {
      const payload = {
        id_client: this.paymentCredit.id_client,
        pay_payment: this.paymentCredit.pay_payment,
      };
      axios
        .post("api/orders/payCreditByClient", payload, this.$root.config)
        .then(() => {
          $("#modalPaymentCredit").modal("hide");
          this.$emit("credited");
          this.resetData();
        })
        .catch((err) => {
          console.error(err);
        });
    },
  },
};
</script>

<style scoped>
/* Resalta el valor formateado */
.text-primary {
  font-weight: bold;
  font-size: 1.1rem;
}
</style>
