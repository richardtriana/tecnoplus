<template>
	<div class="w-100 px-4 pt-3">
	  <!-- ENCABEZADO -->
	  <header class="d-flex justify-content-between align-items-center mb-3 header-bg">
		<h3 class="fw-bold mb-0 text-white">Comprobantes</h3>
		<div>
		  <!-- Botón Nueva Orden -->
		  <router-link
			class="btn btn-primary btn-sm me-2"
			:to="{ name: 'create-edit-order', params: { order_id: 0 } }"
			v-if="$root.validatePermission('order.store')"
		  >
			<i class="bi bi-plus-circle me-1"></i> Nueva orden
		  </router-link>
<<<<<<< HEAD
  
=======

>>>>>>> 0ed4468 (factuara electronica + reserva + caja)
		  <!-- Botón Nota Crédito CON Factura: se habilita solo si hay una orden seleccionada en estado 2 o 5 -->
		  <router-link
			v-if="selectedOrder && (selectedOrder.state == 2 || selectedOrder.state == 5)"
			class="btn btn-lila btn-sm me-2"
			:to="{ name: 'credit_notes.create', params: { order_id: selectedOrder.factus_bill_id } }"
		  >
			<i class="bi bi-file-earmark-minus"></i> Nota Crédito con Factura
		  </router-link>
		  <button
			v-else
			class="btn btn-lila btn-sm me-2"
			disabled
		  >
			<i class="bi bi-file-earmark-minus"></i> Nota Crédito con Factura
		  </button>

		  <!-- Botón Reenvío Automático -->
		  <button
			class="btn btn-warning btn-sm"
			@click="showAutoResendModal = true"
		  >
			<i class="bi bi-arrow-clockwise"></i> Reenvío automático
		  </button>
		</div>
	  </header>
<<<<<<< HEAD
  
=======

>>>>>>> 0ed4468 (factuara electronica + reserva + caja)
	  <!-- SECCIÓN DE FILTROS -->
	  <section class="card shadow-sm mb-4">
		<div class="card-body">
		  <div class="row g-3 align-items-end">
			<div class="col-12">
			  <h6 class="text-secondary fw-bold">Buscar</h6>
			</div>
			<div class="col-sm-6 col-md-3">
			  <label for="category" class="form-label">Estado</label>
			  <v-select
				:options="statusOrders"
				label="status"
				:reduce="s => s.id"
				v-model="filter.status"
			  >
				<template #selected-option>
				  <div style="display: flex; align-items: baseline">
					<em style="margin-left: 0.5rem">
					  {{ statusOrders.find(s => s.id === filter.status)?.status }}
					</em>
				  </div>
				</template>
			  </v-select>
			</div>
			<div class="col-sm-6 col-md-3">
			  <label for="nro_factura" class="form-label">Nro Factura</label>
			  <input
				type="text"
				id="nro_factura"
				class="form-control form-control-sm"
				placeholder="Nro Factura"
				v-model="filter.no_invoice"
			  />
			</div>
			<div class="col-sm-6 col-md-3">
			  <label for="name_client" class="form-label">Cliente</label>
			  <input
				type="text"
				id="name_client"
				class="form-control form-control-sm"
				placeholder="Cliente"
				v-model="filter.client"
			  />
			</div>
			<div class="col-sm-6 col-md-3">
			  <label for="from_date" class="form-label">Desde</label>
			  <input
				type="datetime-local"
				id="from_date"
				class="form-control form-control-sm"
				v-model="filter.from"
			  />
			</div>
			<div class="col-sm-6 col-md-3">
			  <label for="to_date" class="form-label">Hasta</label>
			  <input
				type="datetime-local"
				id="to_date"
				class="form-control form-control-sm"
				v-model="filter.to"
			  />
			</div>
			<div class="col-sm-6 col-md-3">
			  <label for="nro_results" class="form-label">Resultados por página</label>
			  <input
				type="number"
				step="1"
				id="nro_results"
				class="form-control form-control-sm"
				placeholder="Resultados"
				v-model.number="filter.nro_results"
				min="1"
			  />
			</div>
			<div class="col-sm-6 col-md-3" v-if="$root.validatePermission('order.update')">
			  <label for="user" class="form-label">Usuario</label>
			  <v-select
				:options="userList"
				label="name"
				:reduce="user => user.id"
				v-model="filter.user_id"
			  />
			</div>
			<div class="col-sm-6 col-md-3 text-end ms-auto">
			  <button class="btn btn-success btn-sm w-100" @click="getOrders(1)">
				<i class="bi bi-search me-1"></i> Buscar
			  </button>
			</div>
		  </div>
		</div>
	  </section>
<<<<<<< HEAD
  
=======

>>>>>>> 0ed4468 (factuara electronica + reserva + caja)
	  <!-- TABLA DE ÓRDENES -->
	  <section class="card shadow-sm">
		<div class="card-body p-0">
		  <load-pdf :loading="load_pdf" />
		  <div class="table-responsive">
			<table class="table table-hover table-striped align-middle my-custom-table mb-0">
			  <thead>
				<tr>
				  <th class="text-center">ID</th>
				  <th class="text-center">Nro Factura</th>
				  <th class="text-center">Cliente</th>
				  <th class="text-center">ID de Devolución</th>
				  <th>Total Pago</th>
				  <th>Mesa</th>
				  <th>Estado</th>
				  <th>Estado DIAN</th>
				  <th>Ver</th>
				  <th>Ticket / Pre-cuenta</th>
				  <th>Imprimir</th>
				  <th>Responsable</th>
				  <th>Fecha</th>
				  <th v-if="$root.validatePermission('order.update')">Editar</th>
				  <th v-if="$root.validatePermission('order.delete')">Eliminar</th>
				</tr>
			  </thead>
			  <tbody>
				<tr
				  v-for="o in OrderList.data"
				  :key="o.id"
				  :class="{ 'table-active': selectedOrder && selectedOrder.id === o.id }"
				  @click="selectOrder(o)"
				  style="cursor: pointer"
				>
				  <td class="text-center">{{ o.id }}</td>
				  <td class="text-center">
<<<<<<< HEAD
					<span class="pill-badge">{{ o.bill_number }}</span>
=======
					<span
					  :class="o.is_reservation ? 'pill-badge-reserva' : 'pill-badge'"
					>
					  {{ o.bill_number }}
					</span>
>>>>>>> 0ed4468 (factuara electronica + reserva + caja)
				  </td>
				  <td class="text-center">
					{{ o.client
					  ? (o.client.razon_social || (o.client.first_name + ' ' + o.client.first_lastname))
					  : 'Sin Cliente' }}
				  </td>
				  <td class="text-center">{{ o.factus_bill_id || '-' }}</td>
				  <td>{{ o.total_paid | currency }}</td>
				  <td>
					<span class="pill-badge" v-if="o.table">{{ o.table.table }}</span>
				  </td>
				  <td>{{ statusOrders[o.state]?.status }}</td>
				  <td>
					<span
					  class="badge"
					  :class="{
						'badge-success': o.status_dian === 1,
						'badge-danger': o.status_dian !== 1
					  }"
					>
					  {{ o.status_dian === 1 ? 'Recibida' : 'No recibida' }}
					</span>
					<button
					  v-if="canResend(o)"
					  class="btn btn-sm btn-outline-danger ms-1"
					  @click.stop="resendDian(o)"
					>
					  <i class="bi bi-arrow-clockwise"></i>
					</button>
				  </td>
				  <td>
					<button class="btn btn-outline-secondary btn-sm" @click.stop="openModal(o.id)">
					  <i class="bi bi-eye"></i>
					</button>
				  </td>
				  <td>
					<button
					  class="btn btn-outline-info btn-sm"
					  v-if="o.state == 1"
					  @click.stop="printPreCuenta(o.id)"
					>
					  <i class="bi bi-receipt me-1"></i> Pre-cuenta
					</button>
					<button
					  class="btn btn-outline-success btn-sm"
					  v-else-if="o.state == 2 || o.state == 3 || o.state == 5"
					  @click.stop="printTicket(o.id)"
					>
					  <i class="bi bi-receipt"></i>
					</button>
					<button class="btn btn-secondary btn-sm" v-else disabled>
					  <i class="bi bi-receipt"></i>
					</button>
				  </td>
				  <td>
					<button class="btn btn-outline-danger btn-sm" @click.stop="generatePdf(o.id)">
					  <i class="bi bi-file-earmark-pdf-fill"></i>
					</button>
					<button class="btn btn-outline-dark btn-sm" @click.stop="reprintOrder(o.id)">
					  <i class="bi bi-printer"></i>
					</button>
				  </td>
				  <td>{{ o.user.name }}</td>
				  <td>
					<span class="d-block">
					  <b>Creación:</b> {{ o.created_at | moment("DD-MM-YYYY h:mm:ss a") }}
					</span>
					<span class="d-block" v-if="o.payment_date">
					  <b>Facturación:</b> {{ o.payment_date | moment("DD-MM-YYYY h:mm:ss a") }}
					</span>
				  </td>
<<<<<<< HEAD
				  <td v-if="$root.validatePermission('order.update')">
					<router-link
					  v-if="o.state != 2 && o.state != 0"
=======
				  <td v-if="$root.validatePermission('order.update') && o.status_dian === 0">
					<router-link
>>>>>>> 0ed4468 (factuara electronica + reserva + caja)
					  class="btn btn-info btn-sm"
					  :to="{ name: 'create-edit-order', params: { order_id: o.id } }"
					>
					  <i class="bi bi-pencil-square me-1"></i> pagar - editar
					</router-link>
				  </td>
<<<<<<< HEAD
				  <td v-if="$root.validatePermission('order.delete')">
=======
				  <td v-if="$root.validatePermission('order.delete') && o.status_dian === 0">
>>>>>>> 0ed4468 (factuara electronica + reserva + caja)
					<button class="btn btn-danger btn-sm" @click.stop="deleteOrder(o.id)">
					  <i class="bi bi-trash"></i>
					</button>
				  </td>
				</tr>
			  </tbody>
			  <tfoot>
				<tr class="fw-bold">
				  <td colspan="2" class="border-0 text-end">Total:</td>
				  <td>{{ TotalOrderList.total_paid | currency }}</td>
				  <td colspan="12" class="border-0"></td>
				</tr>
			  </tfoot>
			</table>
		  </div>
		</div>
		<div class="card-footer">
		  <pagination
			:align="'center'"
			:data="OrderList"
			:limit="8"
			@pagination-change-page="getOrders"
		  >
			<span slot="prev-nav"><i class="bi bi-chevron-double-left"></i></span>
			<span slot="next-nav"><i class="bi bi-chevron-double-right"></i></span>
		  </pagination>
		</div>
	  </section>
<<<<<<< HEAD
  
	  <!-- MODAL PARA DETALLES DE ORDEN -->
=======

	  <!-- MODAL PARA DETALLES DE ORDEN / RESERVA -->
>>>>>>> 0ed4468 (factuara electronica + reserva + caja)
	  <div
		class="modal fade"
		:class="{ show: showModal }"
		:style="{ display: showModal ? 'block' : 'none' }"
		tabindex="-1"
		role="dialog"
		aria-hidden="true"
		v-if="showModal"
	  >
<<<<<<< HEAD
		<div class="modal-dialog modal-dialog-centered modal-lg" role="document">
		  <div class="modal-content">
			<div class="modal-header">
			  <h5 class="modal-title">Detalle de la Orden {{ selectedOrderId }}</h5>
			  <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close" @click="closeModal"></button>
			</div>
			<div class="modal-body">
=======
		<div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable" role="document">
		  <div class="modal-content">
			<div class="modal-header">
			  <h5 class="modal-title">
				{{ selectedOrder && selectedOrder.is_reservation ? 'Detalle de la Reserva' : 'Detalle de la Orden' }}
				<span v-if="!selectedOrder.is_reservation"> {{ selectedOrderId }}</span>
			  </h5>
			  <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close" @click="closeModal"></button>
			</div>
			<div class="modal-body">
			  <template v-if="selectedOrder && selectedOrder.is_reservation">
				<div class="reservation-info text-center mb-4">
				  <h4 class="mb-2">RESERVA</h4>
				  <p class="mb-1"><strong>Fecha:</strong> {{ selectedOrder.reservation_at | moment("DD-MM-YYYY") }}</p>
				  <p class="mb-0"><strong>Hora:</strong> {{ selectedOrder.reservation_at | moment("HH:mm") }}</p>
				</div>
			  </template>
>>>>>>> 0ed4468 (factuara electronica + reserva + caja)
			  <DetailsOrder :order_id="selectedOrderId" />
			</div>
			<div class="modal-footer">
			  <button type="button" class="btn btn-secondary" @click="closeModal">Cerrar</button>
			</div>
		  </div>
		</div>
	  </div>
	  <div class="modal-backdrop fade" :class="{ show: showModal }" v-if="showModal"></div>

	  <!-- MODAL DE CARGA REENVÍO DIAN -->
	  <div
		class="modal fade"
		:class="{ show: showResendModal }"
		:style="{ display: showResendModal ? 'block' : 'none' }"
		tabindex="-1"
		aria-hidden="true"
		v-if="showResendModal"
	  >
		<div class="modal-dialog modal-dialog-centered modal-sm">
		  <div class="modal-content text-center p-4">
			<div v-if="resendLoading">
			  <div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div>
			  <p class="mt-2">Reenviando a DIAN...</p>
			</div>
		  </div>
		</div>
	  </div>
	  <div class="modal-backdrop fade" :class="{ show: showResendModal }" v-if="showResendModal"></div>
	</div>
</template>

<script>
import axios from 'axios'
import vSelect from 'vue-select'
import LoadPdf from './LoadPdf.vue'
import DetailsOrder from './DetailsOrder.vue'

export default {
	components: { vSelect, LoadPdf, DetailsOrder },
	data() {
	  return {
		load_pdf: false,
		OrderList: {},
		TotalOrderList: {},
		userList: [],
		numberingRanges: [],
		filter: {
		  client: '',
		  no_invoice: '',
		  from: '',
		  to: '',
		  user_id: '--Seleccionar--',
<<<<<<< HEAD
		  status: 1,              // default "Pedido"
=======
		  status: 1,
>>>>>>> 0ed4468 (factuara electronica + reserva + caja)
		  status_dian: '',
		  nro_results: 15
		},
		statusOrders: [
		  { id: 0, status: 'Desechada' },
		  { id: 1, status: 'Pedido' },
		  { id: 2, status: 'Facturado' },
		  { id: 3, status: 'Cotizado' },
		  { id: 5, status: 'Crédito' }
		],
		showModal: false,
		selectedOrderId: null,
		selectedOrder: null,
		showResendModal: false,
		resendLoading: false
	  }
	},
	created() {
	  this.$root.validateToken()
	  this.listUsers()
	  this.loadNumberingRanges()
	  this.getOrders(1)
	},
	methods: {
	  getOrders(page = 1) {
		const data = {
		  page,
		  client: this.filter.client,
		  no_invoice: this.filter.no_invoice,
		  from: this.filter.from,
		  to: this.filter.to,
		  user_id: this.filter.user_id === '--Seleccionar--' ? '-1' : this.filter.user_id,
		  status: this.filter.status,
		  status_dian: this.filter.status_dian,
		  nro_results: this.filter.nro_results
		}
		axios.get('api/orders', { params: data, headers: this.$root.config.headers })
		  .then(r => {
			this.OrderList = r.data.orders
			this.TotalOrderList = r.data.totalOrders
			this.selectedOrder = null
			this.selectedOrderId = null
		  })
	  },
	  listUsers() {
		axios.get('api/users/user-list', this.$root.config)
		  .then(r => this.userList = r.data.users)
	  },
	  loadNumberingRanges() {
		axios.get('api/boxes/byUser', this.$root.config)
		  .then(r => {
			if (r.data.boxes.length) {
			  this.numberingRanges = r.data.boxes[0].numbering_ranges
			}
		  })
	  },
	  canResend(o) {
		const range = this.numberingRanges.find(r => r.id === o.numbering_range_id)
		return range && range.enviado_dian === 1 && o.status_dian === 0
	  },
	  resendDian(o) {
		this.showResendModal = true
		this.resendLoading = true
		axios.post(`api/orders/${o.id}/resend-dian`, {}, this.$root.config)
		  .then(() => this.getOrders(this.OrderList.current_page))
		  .finally(() => {
			this.resendLoading = false
			this.showResendModal = false
		  })
	  },
	  deleteOrder(order_id) {
		axios.delete(`api/orders/${order_id}`, this.$root.config)
		  .then(() => {
			this.getOrders(1)
			Swal.fire({ icon: 'success', title: 'Excelente', text: 'Orden eliminada correctamente' })
		  })
	  },
	  generatePdf(id) {
		this.load_pdf = true
		axios.get(`api/orders/generatePdf/${id}`, this.$root.config)
		  .then(r => {
			const a = document.createElement('a')
			a.href = 'data:application/pdf;base64,' + r.data.pdf
			a.download = `Order-${id}.pdf`
			a.click()
		  })
		  .finally(() => this.load_pdf = false)
	  },
	  reprintOrder(id) {
		axios.get(`api/orders/reprint/${id}`, this.$root.config)
		  .then(r => {
			const a = document.createElement('a')
			a.href = 'data:application/pdf;base64,' + r.data.pdf
			a.download = `ReprintOrder-${id}.pdf`
			a.click()
		  })
	  },
	  printTicket(id) {
		axios.get(`api/print-order/${id}`, this.$root.config)
	  },
	  printPreCuenta(id) {
		axios.get(`api/print-precuenta/${id}`, this.$root.config)
	  },
	  openModal(id) {
		this.selectedOrderId = id
<<<<<<< HEAD
=======
		this.selectedOrder = this.OrderList.data.find(o => o.id === id)
>>>>>>> 0ed4468 (factuara electronica + reserva + caja)
		this.showModal = true
	  },
	  closeModal() {
		this.showModal = false
		this.selectedOrderId = null
<<<<<<< HEAD
=======
		this.selectedOrder = null
>>>>>>> 0ed4468 (factuara electronica + reserva + caja)
	  },
	  selectOrder(o) {
		this.selectedOrder = o
		this.selectedOrderId = o.id
	  }
	}
}
</script>

<style scoped>
.header-bg {
	background-color: #34BF9B;
	padding: 0.75rem 1rem;
	border-radius: 0.25rem;
}
.pill-badge {
	display: inline-block;
	padding: 0.25rem 0.75rem;
	border-radius: 50px;
	background-color: #34BF9B;
	color: #fff;
	font-weight: 600;
}
<<<<<<< HEAD
=======
.pill-badge-reserva {
	display: inline-block;
	padding: 0.25rem 0.75rem;
	border-radius: 50px;
	background-color: #6f42c1; /* morado */
	color: #fff;
	font-weight: 600;
}
>>>>>>> 0ed4468 (factuara electronica + reserva + caja)
.my-custom-table tbody tr:hover {
	background-color: #f8f9fa;
}
.my-custom-table th,
.my-custom-table td {
	vertical-align: middle !important;
}
.form-label {
	font-weight: 600;
	font-size: 0.875rem;
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
.table-active {
	background-color: #e2e3e5 !important;
}
/* Botón lila */
.btn-lila {
	background-color: #c95807;
	color: #fff;
	border: 1px solid #c95807;
}
.btn-lila:hover {
	background-color: #863d09;
	border-color: #863d09;
}
<<<<<<< HEAD
=======
/* Info de reserva en modal */
.reservation-info h4 {
	font-size: 2rem;
	font-weight: bold;
}
.reservation-info p {
	font-size: 1.2rem;
	margin: 0.25rem 0;
}
>>>>>>> 0ed4468 (factuara electronica + reserva + caja)
</style>
