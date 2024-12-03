<template>
	<div class="w-100">
		<header class="page-header justify-content-between row px-4">
			<h3>Ordenes</h3>
			<router-link class="btn btn-primary" :to="{
				name: 'create-edit-order',
				params: { order_id: 0 },
			}" v-if="$root.validatePermission('order.store')">
				Nueva orden
			</router-link>
		</header>
		<section>
			<load-pdf :loading="load_pdf" />
			<div class="card-body">
				<div class="form-row">
					<h6 class="w-100">Buscar...</h6>
					<div class="form-group col-3">
						<label for="category">Estado</label>
						<v-select :options="statusOrders" label="status" :reduce="(s) => s.id" v-model="filter.status"  > 
							<template #selected-option="{}" >
								<div style="display: flex; align-items: baseline">
								  <em style="margin-left: 0.5rem"
									>{{statusOrders[filter.status].status}}</em
								  >
								</div>
							  </template>
							 </v-select>

							 
					</div>
					<div class="form-group col-3">
						<label for="nro_factura">Nro Factura</label>
						<input type="text" name="nro_factura" id="nro_factura" class="form-control" placeholder="Nro Factura"
							v-model="filter.no_invoice" />
					</div>
					<div class="form-group col-3">
						<label for="name_client">Cliente</label>
						<input type="text" name="name_client" id="name_client" class="form-control" placeholder="Cliente"
							v-model="filter.client" />
					</div>
					<div class="form-group col-md-3">
						<label for="from_date">Desde</label>
						<input type="datetime-local" class="form-control" id="from_date" v-model="filter.from" />
					</div>
					<div class="form-group col-md-3">
						<label for="to_date">Hasta</label>
						<input type="datetime-local" class="form-control" id="to_date" v-model="filter.to" />
					</div>
					<div class="form-group col-3">
						<label for="nro_results">Mostrar resultados por página:</label>
						<input type="number" step="any" class="form-control" id="nro_results" placeholder="Resultados"
									 v-model="filter.nro_results" />
					</div>
					<div class="form-group col-3" v-if="$root.validatePermission('order.update')">
						<label for="category">Usuario</label>
						<v-select :options="userList" label="name" :reduce="(user) => user.id" v-model="filter.user_id" />
					</div>
					<div class="form-group offset-9 col-md-3">
						<button class="btn btn-success btn-block" @click="getOrders(1)">
							Buscar
						</button>
					</div>
				</div>
				<div class="table-responsive w-100">
					<table class="table table-sm table-bordered">
						<thead>
							<tr>
								<th>#</th>
								<th>Total Pago</th>
								<th>Total Sin Iva</th>
								<th>Total Descuento</th>
								<th>Cliente</th>
								<th>Mesa</th>
								<th>Estado</th>
								<th>Ver</th>
								<th>Ticket</th>
								<th>Imprimir</th>
								<th>Responsable</th>
								<th>Fecha</th>
								<th v-if="$root.validatePermission('order.update')">Editar</th>
								<th v-if="$root.validatePermission('order.delete')">Eliminar</th>
							</tr>
						</thead>
						<tbody>
							<tr v-for="o in OrderList.data" :key="o.id">
								<th scope="row">{{  o.id  }} - {{  o.bill_number  }}</th>
								<td>{{  o.total_paid | currency  }}</td>
								<td>{{  o.total_iva_exc | currency  }}</td>
								<td>{{  o.total_discount | currency  }}</td>
								<td>{{  o.client.name  }}</td>
								<td>
									<template v-if="o.table">
										{{ o.table.table }}
									</template>
								</td>
									<td>
										{{ statusOrders[o.state]["status"] }}
									</td>
									<td>
									<router-link class="btn" :to="{ name: 'details-order', params: { order_id: o.id } }">
										<i class="bi bi-eye"></i>
									</router-link>
								</td>
								<td>
									<button class="btn" v-if="o.state == 5 || o.state == 2 || o.state == 3" @click="printTicket(o.id)">
										<i class="bi bi-receipt"></i>
									</button>
									<button class="btn" v-else disabled>
										<i class="bi bi-receipt"></i>
									</button>
								</td>
								<td>
									<button class="btn text-danger" @click="generatePdf(o.id)">
										<i class="bi bi-file-earmark-pdf-fill"></i>
									</button>
								</td>

								<td>
									{{  o.user.name  }}
								</td>
								<td>
									<span>
										<b>Creación:</b>
										{{  o.created_at | moment("DD-MM-YYYY h:mm:ss a")  }}
									</span>
									<br />
									<span v-if="o.payment_date">
										<b>Facturación:</b>
										{{  o.payment_date | moment("DD-MM-YYYY h:mm:ss a")  }}
									</span>
								</td>
								<td v-if="$root.validatePermission('order.update')">
									<router-link class="btn" :to="{
										name: 'create-edit-order',
										params: { order_id: o.id },
									}">
										<i class="bi bi-pencil-square"></i>
									</router-link>
								</td>
								<td v-if="$root.validatePermission('order.delete')">
									<button class="btn text-danger" @click="deleteOrder(o.id)">
										<i class="bi bi-trash"></i>
									</button>
								</td>
							</tr>
						</tbody>
						<tfoot>
							<tr class="text-bold">
								<th class="border-0"></th>
								<th>{{  TotalOrderList.total_paid | currency  }}</th>
								<th>{{  TotalOrderList.total_iva_exc | currency  }}</th>
								<th>{{  TotalOrderList.total_discount | currency  }}</th>
							</tr>
						</tfoot>
					</table>
				</div>
			</div>
			<pagination :align="'center'" :data="OrderList" :limit="8" @pagination-change-page="getOrders">
				<span slot="prev-nav"><i class="bi bi-chevron-double-left"></i></span>
				<span slot="next-nav"><i class="bi bi-chevron-double-right"></i></span>
			</pagination>
		</section>
		<div class="footer"></div>
	</div>
</template>
<script>
import LoadPdf from "./LoadPdf.vue";
export default {
	components: { LoadPdf },
	props: {
		status: {
			type: Number,
			default: 0
		}
	},
	data() {
		return {
			load_pdf: false,
			OrderList: {},
			TotalOrderList: [],
			userList: [],
			filter: {
				client: "",
				no_invoice: "",
				from: "",
				to: "",
				user_id: "--Seleccionar--",
				status: `1`,
				nro_results: 15
			},
			statusOrders: [
				{ id: 0, status: "Desechada" },
				{ id: 1, status: "Pedido" },
				{ id: 2, status: "Facturado" },
				{ id: 3, status: "Cotizado" },
				{ id: 4, status: "Facturar e imprimir" },
				{ id: 5, status: "Credito" },
				{ id: 6, status: "Credito e imprimir" },
			],
		};
	},
	created() {
		this.$root.validateToken();
		this.listUsers();
		this.getOrders(1);
	},
	methods: {
		getOrders(page = 1) {
			let me = this;
			let data = {
				page: page,
				client: me.filter.client,
				no_invoice: me.filter.no_invoice,
				from: me.filter.from,
				to: me.filter.to,
				user_id: me.filter.user_id == '--Seleccionar--' ? '-1' : me.filter.user_id,
				status: me.filter.status != undefined ? me.filter.status : me.status.default,
				nro_results: me.filter.nro_results
			};

			axios
				.get(`api/orders`, { params: data, headers: this.$root.config.headers })
				.then(function (response) {
					me.OrderList = response.data.orders;
					me.TotalOrderList = response.data.totalOrders;
				});
		},
		deleteOrder(order_id) {
			axios
				.delete(`api/orders/${order_id}`, this.$root.config)
				.then(() => {
					this.getOrders(1);
					Swal.fire({
						icon: "success",
						title: "Excelente",
						text: "Los datos se han eliminado correctamente",
					});
				})
				.catch(function (error) {
					// handle error
					if (error) {
						Swal.fire({
							icon: "error",
							title: "Oops...",
							text: "Hubo un error al eliminar la orden",
						});
					}
				});
		},
		generatePdf(id) {
			this.load_pdf = true;
			axios
				.get("api/orders/generatePdf/" + id, this.$root.config)
				.then((response) => {
					console.log(response);

					const pdf = response.data.pdf;
					var a = document.createElement("a");
					a.href = "data:application/pdf;base64," + pdf;
					a.download = `Order-${id}.pdf`;
					a.click();
				})
				.finally(() => {
					this.load_pdf = false;
				});
		},
		printTicket(order_id) {
			axios.get(`api/print-order/${order_id}`, this.$root.config);
		},
		listUsers() {
			let me = this;
			axios
				.get(`api/users/user-list`, this.$root.config)
				.then(function (response) {
					me.userList = response.data.users;
				});
		},
	},
};
</script>
