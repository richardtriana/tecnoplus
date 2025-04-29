<template>
	<div class="page w-100">
	  <!-- Encabezado -->
	  <div class="page-header">
		<h3>Listado de Facturas</h3>
	  </div>
  
	  <!-- Filtros -->
	  <div class="page-search mx-2 my-2 border p-2">
		<div class="form-row">
		  <!-- Estado global -->
		  <div class="form-group col-md-2">
			<label>Estado</label>
			<v-select
			  :options="statusOrders"
			  label="status"
			  :reduce="s => s.id"
			  v-model="filter.status"
			  placeholder="Todos"
			/>
		  </div>
		  <!-- Estado Dian -->
		  <div class="form-group col-md-2">
			<label>Estado Dian</label>
			<v-select
			  :options="dianStatuses"
			  label="status"
			  :reduce="d => d.id"
			  v-model="filter.status_dian"
			  placeholder="Todos"
			/>
		  </div>
		  <!-- Estado Factura -->
		  <div class="form-group col-md-2">
			<label>Estado Factura</label>
			<v-select
			  :options="statusOrders"
			  label="status"
			  :reduce="s => s.id"
			  v-model="filter.state"
			  placeholder="Todos"
			/>
		  </div>
		  <!-- Desde -->
		  <div class="form-group col-md-2">
			<label>Desde</label>
			<input
			  type="datetime-local"
			  class="form-control"
			  v-model="filter.from"
			/>
		  </div>
		  <!-- Hasta -->
		  <div class="form-group col-md-2">
			<label>Hasta</label>
			<input
			  type="datetime-local"
			  class="form-control"
			  v-model="filter.to"
			/>
		  </div>
		  <!-- No. Factura -->
		  <div class="form-group col-md-2">
			<label>No. Factura</label>
			<input
			  type="text"
			  class="form-control"
			  v-model="filter.no_invoice"
			  placeholder="Número o referencia"
			/>
		  </div>
  
		  <!-- Botones -->
		  <div class="col-md-3 mt-3">
			<button class="btn btn-success btn-block" @click="getOrders">
			  Buscar <i class="bi bi-search"></i>
			</button>
		  </div>
		  <div class="col-md-3 mt-3">
			<button class="btn btn-outline-success btn-block" @click="getTicket">
			  Ticket <i class="bi bi-card-text"></i>
			</button>
		  </div>
		  <div class="col-md-3 mt-3">
			<button class="btn btn-primary btn-block" @click="exportExcel">
			  Exportar Excel <i class="bi bi-file-earmark-spreadsheet"></i>
			</button>
		  </div>
		</div>
	  </div>
  
	  <!-- Totales (Tarjetas verdes) -->
	  <div class="row mb-3 px-3">
		<div class="col-md-3">
		  <div class="card text-white bg-success mb-2">
			<div class="card-body">
			  <h5 class="card-title">Total Pagado</h5>
			  <p class="card-text">{{ totalPaidAll | currency }}</p>
			</div>
		  </div>
		</div>
		<div class="col-md-3">
		  <div class="card text-white bg-success mb-2">
			<div class="card-body">
			  <h5 class="card-title">Total Impuestos</h5>
			  <p class="card-text">{{ totalImpuestosAll | currency }}</p>
			</div>
		  </div>
		</div>
		<div class="col-md-3">
		  <div class="card text-white bg-success mb-2">
			<div class="card-body">
			  <h5 class="card-title">Total IVA Exc.</h5>
			  <p class="card-text">{{ totalIvaExcAll | currency }}</p>
			</div>
		  </div>
		</div>
		<div class="col-md-3">
		  <div class="card text-white bg-success mb-2">
			<div class="card-body">
			  <h5 class="card-title">Total Descuento</h5>
			  <p class="card-text">{{ totalDiscountAll | currency }}</p>
			</div>
		  </div>
		</div>
	  </div>
  
	  <!-- Control de "registros por página" -->
	  <div class="px-3 mb-2">
		<label>
		  Mostrar
		  <input
			type="number"
			class="form-control d-inline-block w-auto"
			v-model.number="perPage"
			min="1"
		  />
		  registros por página
		</label>
	  </div>
  
	  <!-- Tabla de Facturas -->
	  <div class="page-content">
		<section class="p-3">
		  <table class="table table-sm table-bordered table-hover">
			<thead class="thead-dark">
			  <tr>
				<th>ID</th>
				<th>Fecha</th>
				<th>Factura</th>
				<th>Cliente</th>
				<th>Total Pagado</th>
				<th>V. impuestos</th>
				<th>IVA Exc.</th>
				<th>Descuento</th>
				<th>Forma Pago</th>
				<th>Método Pago</th>
				<th>Estado</th>
				<th>Estado Dian</th>
			  </tr>
			</thead>
			<tbody v-if="List.length">
			  <tr v-for="(o,i) in List" :key="i">
				<td>{{ o.id }}</td>
				<td>{{ formatDateTime(o.created_at) }}</td>
				<td>{{ o.bill_number || o.no_invoice }}</td>
				<td>{{ o.client?.razon_social || o.client?.first_name || '-' }}</td>
				<td>{{ Number(o.total_paid).toFixed(2) | currency }}</td>
				<td>{{ (o.total_iva_inc - o.total_iva_exc).toFixed(2) | currency }}</td>
				<td>{{ Number(o.total_iva_exc).toFixed(2) | currency }}</td>
				<td>{{ Number(o.total_discount).toFixed(2) | currency }}</td>
				<td>{{ o.payment_form?.name || '-' }}</td>
				<td>{{ o.payment_method?.name || '-' }}</td>
				<td>
				  <span
					class="badge"
					:class="{
					  'badge-success': facturadoStates.includes(o.state),
					  'badge-secondary': !facturadoStates.includes(o.state)
					}"
				  >
					{{ facturadoStates.includes(o.state)
					  ? 'Facturado'
					  : statusMap[o.state] || '—' }}
				  </span>
				</td>
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
				</td>
			  </tr>
			</tbody>
			<tfoot v-if="List.length">
			  <tr class="font-weight-bold">
				<td colspan="4" class="text-right">Totales:</td>
				<td>{{ totalPaidAll | currency }}</td>
				<td>{{ totalImpuestosAll | currency }}</td>
				<td>{{ totalIvaExcAll | currency }}</td>
				<td>{{ totalDiscountAll | currency }}</td>
				<td colspan="4"></td>
			  </tr>
			</tfoot>
			<tbody v-else>
			  <tr>
				<td colspan="12" class="text-center">
				  No se encontraron facturas
				</td>
			  </tr>
			</tbody>
		  </table>
		</section>
	  </div>
	</div>
  </template>
  
  <script>
  import axios from 'axios'
  import vSelect from 'vue-select'
  
  export default {
	name: 'ReportSale',
	components: { vSelect },
	data() {
	  return {
		List: [],
		filter: {
		  from: '',
		  to: '',
		  status: '',
		  status_dian: '',
		  state: '',
		  no_invoice: '',
		  page: 1
		},
		perPage: 15,
		statusOrders: [
		  { id: '', status: 'Todos' },
		  { id: 0, status: 'Desechada' },
		  { id: 1, status: 'Pedido' },
		  { id: 2, status: 'Facturado' },
		  { id: 3, status: 'Cotizado' },
		  { id: 4, status: 'Facturar e imprimir' },
		  { id: 5, status: 'Crédito' },
		  { id: 6, status: 'Crédito e imprimir' }
		],
		dianStatuses: [
		  { id: '', status: 'Todos' },
		  { id: 1, status: 'Recibida' },
		  { id: 0, status: 'No recibida' }
		],
		facturadoStates: [2, 4, 6],
		baseURL: 'http://192.168.100.64/restaplus/public/api'
	  }
	},
	computed: {
	  statusMap() {
		return this.statusOrders.reduce((m, s) => {
		  if (s.id !== '') m[s.id] = s.status
		  return m
		}, {})
	  },
	  totalPaidAll() {
		return this.List.reduce((sum, o) => sum + (o.total_paid || 0), 0)
	  },
	  totalImpuestosAll() {
		return this.List.reduce(
		  (sum, o) => sum + ((o.total_iva_inc - o.total_iva_exc) || 0),
		  0
		)
	  },
	  totalIvaExcAll() {
		return this.List.reduce((sum, o) => sum + (o.total_iva_exc || 0), 0)
	  },
	  totalDiscountAll() {
		return this.List.reduce((sum, o) => sum + (o.total_discount || 0), 0)
	  }
	},
	methods: {
	  getOrders() {
		axios
		  .get(`${this.baseURL}/reports/sales-report`, {
			params: {
			  ...this.filter,
			  per_page: this.perPage
			},
			headers: this.$root.config.headers
		  })
		  .then(response => {
			const od = response.data.orders
			if (od && od.data) {
			  this.List = od.data
			  this.filter.page = od.current_page
			} else if (Array.isArray(od)) {
			  this.List = od
			  this.filter.page = 1
			} else {
			  this.List = []
			}
		  })
		  .catch(err => {
			console.error('Error al traer facturas:', err)
			this.List = []
		  })
	  },
	  getTicket() {
		axios
		  .post(
			`${this.baseURL}/reports-ticket/sales-report`,
			{ data: this.List },
			{ headers: this.$root.config.headers }
		  )
		  .catch(err => {
			console.error('Error al generar ticket:', err)
		  })
	  },
	  exportExcel() {
		axios
		  .get(`${this.baseURL}/reports/sales-report/export`, {
			params: {
			  ...this.filter,
			  per_page: this.perPage
			},
			responseType: 'blob',
			headers: this.$root.config.headers
		  })
		  .then(res => {
			const url = window.URL.createObjectURL(new Blob([res.data]))
			const link = document.createElement('a')
			link.href = url
			link.setAttribute('download', 'ventas.xlsx')
			document.body.appendChild(link)
			link.click()
			link.remove()
		  })
		  .catch(err => {
			console.error('Error al exportar Excel:', err)
		  })
	  },
	  formatDateTime(dt) {
		if (!dt) return ''
		return new Date(dt).toLocaleString()
	  }
	},
	mounted() {
	  this.getOrders()
	}
  }
  </script>
  
  <style scoped>
  .badge-success {
	background-color: #28a745 !important;
  }
  .badge-secondary {
	background-color: #6c757d !important;
  }
  .badge-danger {
	background-color: #dc3545 !important;
  }
  </style>
  