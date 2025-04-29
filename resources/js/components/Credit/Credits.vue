<template>
	<div class="w-100">
	  <!-- Header -->
	  <header class="page-header row px-4 mb-3">
		<h3 class="col">Créditos</h3>
	  </header>
  
	  <section class="px-4">
		<!-- Filtros -->
		<div class="form-row align-items-end mb-4">
		  <div class="form-group col-md-2">
			<label for="filterStatus">Estado</label>
			<select id="filterStatus" class="form-control" v-model="filter.status">
			  <option value="pending">Pendiente</option>
			  <option value="paid">Pagado</option>
			  <option value="">Todos</option>
			</select>
		  </div>
		  <div class="form-group col-md-2">
			<label for="filterInvoice">Nro Factura</label>
			<input
			  id="filterInvoice"
			  type="text"
			  class="form-control"
			  placeholder="Número de factura"
			  v-model="filter.invoice"
			/>
		  </div>
		  <div class="form-group col-md-3">
			<label for="filterClient">Cliente</label>
			<input
			  id="filterClient"
			  type="text"
			  class="form-control"
			  placeholder="Nombre cliente"
			  v-model="filter.client"
			/>
		  </div>
		  <div class="form-group col-md-2">
			<label for="filterFrom">Desde</label>
			<input id="filterFrom" type="date" class="form-control" v-model="filter.from" />
		  </div>
		  <div class="form-group col-md-2">
			<label for="filterTo">Hasta</label>
			<input id="filterTo" type="date" class="form-control" v-model="filter.to" />
		  </div>
		  <div class="form-group col-md-1">
			<button class="btn btn-success btn-block" @click="getCredits(1)">Buscar</button>
		  </div>
		</div>
  
		<!-- Cards de totales -->
		<div class="row mb-4">
		  <div class="col-md-4 mb-2">
			<div class="card text-white bg-primary text-center">
			  <div class="card-body">
				<h5>Total Crédito</h5>
				<p class="h4">{{ totals.total_credit | currency }}</p>
			  </div>
			</div>
		  </div>
		  <div class="col-md-4 mb-2">
			<div class="card text-white bg-success text-center">
			  <div class="card-body">
				<h5>Abonado</h5>
				<p class="h4">{{ totals.total_paid | currency }}</p>
			  </div>
			</div>
		  </div>
		  <div class="col-md-4 mb-2">
			<div class="card text-white bg-danger text-center">
			  <div class="card-body">
				<h5>Saldo</h5>
				<p class="h4">{{ totals.total_balance | currency }}</p>
			  </div>
			</div>
		  </div>
		</div>
  
		<!-- Tabla -->
		<table class="table table-sm table-bordered table-responsive-sm">
		  <thead class="thead-light">
			<tr>
			  <th>#Factura</th>
			  <th>Total Crédito</th>
			  <th>Abonado</th>
			  <th>Saldo</th>
			  <th>Cliente</th>
			  <th>Estado</th>
			  <th>Abonar</th>
			  <th>Ver</th>
			  <th>Ticket</th>
			  <th>PDF</th>
			  <th>Eliminar</th>
			</tr>
		  </thead>
		  <tbody>
			<tr v-for="credit in creditList.data" :key="credit.id">
			  <td>{{ credit.order.bill_number }}</td>
			  <td>{{ parseFloat(credit.total_credit) | currency }}</td>
			  <td>{{ (parseFloat(credit.total_credit) - parseFloat(credit.balance)) | currency }}</td>
			  <td>{{ parseFloat(credit.balance) | currency }}</td>
			  <td>{{ credit.order.client.razon_social || credit.order.client.name }}</td>
			  <td>{{ statusLabels[credit.status] }}</td>
			  <td>
				<button
				  class="btn btn-outline-primary btn-sm"
				  @click="openPayment(credit)"
				  :disabled="credit.status === 'paid'"
				>
				  <i class="bi bi-cash-stack"></i>
				</button>
			  </td>
			  <td>
				<router-link
				  :to="{ name: 'details-credit', params: { order_id: credit.order_id } }"
				  class="btn btn-sm"
				>
				  <i class="bi bi-eye"></i>
				</router-link>
			  </td>
			  <td>
				<button class="btn btn-sm" @click="printTicket(credit.order_id)">
				  <i class="bi bi-receipt-cutoff"></i>
				</button>
			  </td>
			  <td>
				<button class="btn text-danger btn-sm" @click="generatePdf(credit.order_id)">
				  <i class="bi bi-file-earmark-pdf-fill"></i>
				</button>
			  </td>
			  <td>
				<button class="btn btn-sm text-danger" @click="deleteCredit(credit.id)">
				  <i class="bi bi-trash"></i>
				</button>
			  </td>
			</tr>
		  </tbody>
		  <tfoot class="table-secondary font-weight-bold">
			<tr>
			  <td>Total página</td>
			  <td>{{ pageCreditSum | currency }}</td>
			  <td>{{ pagePaidSum | currency }}</td>
			  <td>{{ pageBalanceSum | currency }}</td>
			  <td colspan="7"></td>
			</tr>
		  </tfoot>
		</table>
  
		<!-- Paginación -->
		<pagination
		  :data="creditList"
		  :limit="8"
		  align="center"
		  @pagination-change-page="getCredits"
		>
		  <span slot="prev-nav"><i class="bi bi-chevron-double-left"></i></span>
		  <span slot="next-nav"><i class="bi bi-chevron-double-right"></i></span>
		</pagination>
	  </section>
  
	  <!-- Modal de Pago / Abono -->
	  <payment-credit ref="paymentModal" @credited="getCredits()" />
	</div>
  </template>
  
  <script>
  import axios from "axios";
  import PaymentCredit from "./PaymentCredit.vue";
  
  export default {
	components: { PaymentCredit },
	data() {
	  return {
		creditList: { data: [], totals: { total_credit: 0, total_paid: 0, total_balance: 0 } },
		totals: { total_credit: 0, total_paid: 0, total_balance: 0 },
		filter: {
		  status: "pending",
		  invoice: "",
		  client: "",
		  from: "",
		  to: ""
		},
		statusLabels: {
		  pending: "Pendiente",
		  paid: "Pagado"
		}
	  };
	},
	created() {
	  this.getCredits(1);
	},
	computed: {
	  // Totales de la página
	  pageCreditSum() {
		return this.creditList.data.reduce((sum, c) => sum + parseFloat(c.total_credit), 0);
	  },
	  pagePaidSum() {
		return this.creditList.data.reduce(
		  (sum, c) => sum + (parseFloat(c.total_credit) - parseFloat(c.balance)),
		  0
		);
	  },
	  pageBalanceSum() {
		return this.creditList.data.reduce((sum, c) => sum + parseFloat(c.balance), 0);
	  }
	},
	filters: {
	  currency(value) {
		return new Intl.NumberFormat("es-CO", {
		  style: "currency",
		  currency: "COP"
		}).format(value);
	  }
	},
	methods: {
	  getCredits(page = 1) {
		const params = {
		  page,
		  status: this.filter.status,
		  client: this.filter.client,
		  from: this.filter.from,
		  to: this.filter.to,
		  invoice: this.filter.invoice
		};
		axios
		  .get("api/order-credits", {
			params,
			headers: this.$root.config.headers
		  })
		  .then(({ data }) => {
			this.creditList = data;
			// Totales globales del servidor
			this.totals = data.totals || this.totals;
		  });
	  },
	  openPayment(credit) {
		this.$refs.paymentModal.open(credit);
	  },
	  printTicket(orderId) {
		axios.get(`api/print-payment-ticket/${orderId}`, this.$root.config);
	  },
	  generatePdf(orderId) {
		axios
		  .get(`api/orders/generatePaymentPdf/${orderId}`, this.$root.config)
		  .then(res => {
			const pdf = res.data.pdf;
			const a = document.createElement("a");
			a.href = "data:application/pdf;base64," + pdf;
			a.download = `Credit-${orderId}.pdf`;
			a.click();
		  });
	  },
	  deleteCredit(id) {
		axios.delete(`api/order-credits/${id}`, this.$root.config).then(() => this.getCredits(1));
	  }
	}
  };
  </script>
  
  <style scoped>
  .page-header h3 {
	margin: 0;
  }
  </style>
  