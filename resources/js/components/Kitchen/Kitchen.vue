<template>
	<div class="w-100">
		<header class="page-header justify-content-between row px-4">
			<h3>Cocina</h3>
			<button class="btn btn-primary" @click="getOrders(OrderList.current_page)" v-if="$root.validatePermission('kitchen.index')">
				Recargar
			</button>
		</header>
		<section>
			<div class="card-body">
				<div class="form-row d-none">
					<h6 class="w-100">Buscar...</h6>


					<div class="form-group col-md-3">
						<label for="from_date">Desde</label>
						<input type="datetime-local" class="form-control" id="from_date" v-model="filter.from" />
					</div>
					<div class="form-group col-md-3">
						<label for="to_date">Hasta</label>
						<input type="datetime-local" class="form-control" id="to_date" v-model="filter.to" />
					</div>

					<div class="form-group offset-9 col-md-3">
						<button class="btn btn-success btn-block" @click="getOrders(1)">
							Buscar
						</button>
					</div>
				</div>
				<div class=" w-100">

					<div class="row row-cols-1 row-cols-md-3 g-4">
						<template v-for="o in OrderList.data">
							<div class="col mb-3">
								<div class="card border-success h-100">
									<div class="card-header">
										<h5 class="card-title text-center text-primary font-weight-bolder h2"><template
												v-if="o.table">
												{{ o.table.table }}
											</template>
										</h5>
										<small>{{ o.created_at }}</small>
									</div>
									<div class="card-body h1">
										<ul class="list-group list-group-flush">
											<li class="list-group-item d-flex justify-content-between align-items-center py-1 px-1"
												v-for="p in o.detail_orders">
												- {{ p.product }}
												<span class="badge text-bg-primary rounded-pill  font-weight-bolder">{{
													p.quantity }}</span>
											</li>
										</ul>

									</div>
									<div class="card-footer bg-white">
										<div class="h3"><b class="text-primary">Observaciones: </b>{{ o.observations ??
											'Ninguna'}} </div>
										<hr>
										<button class="btn btn-success w-100" @click="prepareOrder(o.id)">
											Preparado</button>
									</div>
								</div>
							</div>
						</template>

					</div>
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
export default {
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
			filter: {
				client: "",
				no_invoice: "",
				from: "",
				to: "",
				user_id: "",
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
				user_id: me.filter.user_id,
				nro_results: me.filter.nro_results
			};

			axios
				.get(`api/orders/kitchen`, { params: data, headers: this.$root.config.headers })
				.then(function (response) {
					me.OrderList = response.data.orders;
					Swal.fire({
						icon: "success",
						title: "Excelente",
						text: "Los datos se han cargado correctamente",
					})
				})
				.catch(function (error) {
					// handle error
					console.log("error", error);
					if (error) {
						Swal.fire({
							icon: "error",
							title: "Oops...",
							text: "Hubo un error al cargar los datos",
						});
					}
				});
		},
		prepareOrder(order_id) {
			axios
				.put(`api/orders/kitchen/${order_id}`, null, this.$root.config)
				.then(() => {
					this.getOrders(1);
					Swal.fire({
						icon: "success",
						title: "Excelente",
						text: "Los datos se han actualizado correctamente",
					});
				})
				.catch(function (error) {
					// handle error
					if (error) {
						Swal.fire({
							icon: "error",
							title: "Oops...",
							text: "Hubo un error al actualizar la orden",
						});
					}
				});
		},

	},
};
</script>
