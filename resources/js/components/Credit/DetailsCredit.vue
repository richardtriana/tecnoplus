<template>
  <div>
    <section class="page-header">
      <h4 class="w-100 text-center">Detalles de Crédito</h4>
      <load-pdf :loading="load_pdf" />
      <table class="table table-bordered w-100 table-sm">
        <tbody>
          <tr>
            <td>No. Factura</td>
            <th>{{ creditInformation.order.bill_number }}</th>
          </tr>
          <tr>
            <td>Fecha</td>
            <th>{{ creditInformation.created_at | showDate }}</th>
          </tr>
          <tr>
            <th colspan="2" class="text-center">Cliente</th>
          </tr>
          <tr>
            <td>Nombres:</td>
            <th>{{ creditInformation.order.client.name }}</th>
          </tr>
          <tr>
            <td>Documento / Nit:</td>
            <th>{{ creditInformation.order.client.document }}</th>
          </tr>
          <tr>
            <td>Dirección</td>
            <td>{{ creditInformation.order.client.address }}</td>
          </tr>
          <tr>
            <td>Email</td>
            <td>{{ creditInformation.order.client.email }}</td>
          </tr>
          <tr>
            <td>Celular / Teléfono</td>
            <td>{{ creditInformation.order.client.mobile }}</td>
          </tr>
        </tbody>
      </table>

      <div>
        <h4 class="w-100 text-center">Lista de Abonos</h4>
        <div class="text-right">
          <button class="btn btn-light text-danger" @click="generatePaymentPdf(null)">
            <i class="bi bi-file-earmark-pdf-fill"></i> Descargar PDF
          </button>
          <button class="btn btn-light" @click="generatePaymentTicket(null)">
            <i class="bi bi-receipt-cutoff"></i> Descargar Ticket
          </button>
        </div>
      </div>

      <table class="table table-bordered w-100 table-sm">
        <thead>
          <tr>
            <th>Abono</th>
            <th>Fecha</th>
            <th>PDF</th>
            <th>Ticket</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="p in payments" :key="p.id">
            <td>{{ p.amount | currency }}</td>
            <td>{{ p.created_at | showDate }}</td>
            <td class="text-right">
              <button class="btn text-danger" @click="generatePaymentPdf(p.id)">
                <i class="bi bi-file-earmark-pdf-fill"></i>
              </button>
            </td>
            <td class="text-right">
              <button class="btn" @click="generatePaymentTicket(p.id)">
                <i class="bi bi-receipt-cutoff"></i>
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </section>

    <section class="mt-5">
      <h5 class="text-center">Detalles de Productos</h5>
      <table class="table table-sm table-bordered">
        <thead>
          <tr>
            <th>#</th>
            <th>Código</th>
            <th>Producto</th>
            <th>Cantidad</th>
            <th>Precio sin IVA</th>
            <th>Precio con IVA</th>
            <th>Descuento %</th>
            <th>Total</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(item, idx) in ItemList" :key="item.id">
            <td>{{ idx + 1 }}</td>
            <td>{{ item.barcode }}</td>
            <td>{{ item.product }}</td>
            <td>{{ item.quantity }}</td>
            <td>{{ item.price_tax_exc | currency }}</td>
            <td>{{ item.price_tax_inc | currency }}</td>
            <td>{{ item.discount_percentage }}%</td>
            <td class="text-right">{{ item.price_tax_inc_total | currency }}</td>
          </tr>
        </tbody>
        <tfoot class="table-secondary">
          <tr>
            <td colspan="7">Total Crédito</td>
            <td class="text-right">{{ creditInformation.total_credit | currency }}</td>
          </tr>
          <tr>
            <td colspan="7">Total Abonado</td>
            <td class="text-right">{{ creditInformation.total_credit - creditInformation.balance | currency }}</td>
          </tr>
          <tr>
            <td colspan="7">Saldo Pendiente</td>
            <td class="text-right">{{ creditInformation.balance | currency }}</td>
          </tr>
        </tfoot>
      </table>
    </section>
  </div>
</template>

<script>
import LoadPdf from '../Order/LoadPdf.vue';
export default {
  components: { LoadPdf },
  props: ['order_id'],
  data() {
    return {
      load_pdf: false,
      creditInformation: {
        order: { client: {} },
        payments: [],
        total_credit: 0,
        balance: 0,
      },
      ItemList: [],
      payments: [],
    };
  },
  filters: {
    showDate(value) {
      const [date] = value.split('T');
      return date;
    }
  },
  created() {
    this.getDetailsCredit();
  },
  methods: {
    getDetailsCredit() {
      axios
        .get(`api/order-credits/${this.order_id}`, this.$root.config)
        .then(({ data }) => {
          this.creditInformation = data.credit;
          this.ItemList = data.credit.order.detailOrders;
          this.payments = data.credit.payments;
        });
    },
    generatePaymentPdf(id = null) {
      this.load_pdf = true;
      axios
        .get(`api/orders/generatePaymentPdf/${this.order_id}`, {
          params: { payment_id: id },
          headers: this.$root.config.headers
        })
        .then(({ data }) => {
          const pdf = data.pdf;
          const a = document.createElement('a');
          a.href = `data:application/pdf;base64,${pdf}`;
          a.download = `Credit-${this.order_id}.pdf`;
          a.click();
        })
        .finally(() => (this.load_pdf = false));
    },
    generatePaymentTicket(id = null) {
      this.load_pdf = true;
      axios
        .get(`api/print-payment-ticket/${this.order_id}`, {
          params: { payment_id: id },
          headers: this.$root.config.headers
        })
        .finally(() => (this.load_pdf = false));
    }
  }
};
</script>
