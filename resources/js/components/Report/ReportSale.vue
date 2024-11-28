<template>
	<div class="page w-100">
		<div class="page-header">
			<h3>Reporte de venta</h3>
		</div>
		<div class="page-search mx-2 my-2 border p-2">
			<div class="form-row">
				<div class="form-group col-3">
					<label for="category">Estado</label>
					<v-select :options="statusOrders" label="status" :reduce="(status) => status.id" v-model="filter.status" />
				</div>
				<div class="form-group col-md-3">
					<label for="from_date">Desde</label>
					<input type="datetime-local" class="form-control" id="from_date" v-model="filter.from" />
				</div>
				<div class="form-group col-md-3">
					<label for="to_date">Hasta</label>
					<input type="datetime-local" class="form-control" id="to_date" v-model="filter.to" />
				</div>
				<div class="col my-4 col-4">
					<button class="btn btn-success btn-block" @click="getOrders(1)">
						Buscar <i class="bi bi-search"></i>
					</button>
				</div>
				<div class="col-md-3  my-4">
                    <button class="btn btn-outline-success  btn-block" @click="getTicket()">
                      Ticket <i class="bi bi-card-text"></i>
                    </button>
                </div>
			</div>
		</div>
		<div class="page-content">
			<section class="p-3">
				<table class="table table-sm table-bordered table-hover">
					<thead class=" thead-dark">
						<tr>
							<th>Fecha venta</th>
							<th>Número de facturas</th>
							<th>Nro. facturas registradas</th>
							<th>Nro. facturas suspendidas</th>
							<th>Nro. facturas cotizadas</th>
							<th>Total precio de costo</th>
							<th>Total IVA excluido</th>
							<th>Total IVA incluido</th>
							<th>Total Descuento</th>
							<th>Total Pago</th>
							<th>Pagos recibidos</th>
							<th>Total Ganancia</th>
						</tr>
					</thead>
					<tbody v-if="List.length > 0">
						<tr v-for="(l, index) in List" :key="index">
							<td>
								{{  l.date_paid  }}
							</td>
							<td>
								{{  l.number_of_orders  }}
							</td>
							<td>
								{{  l.registered  }}
							</td>
							<td>
								{{  l.suspended  }}
							</td>
							<td>
								{{  l.quoted  }}
							</td>
							<td>
								{{  l.total_cost_price_tax_inc.toFixed(2) | currency  }}
							</td>
							<td>
								{{  l.total_iva_exc.toFixed(2) | currency  }}
							</td>
							<td>
								{{  l.total_iva_inc.toFixed(2) | currency  }}
							</td>
							<td>
								{{  l.total_discount.toFixed(2) | currency  }}
							</td>
							<td>
								{{  l.total_paid.toFixed(2) | currency  }}
							</td>
							<td>
								<span v-if="l.cash != null">Efectivo: {{  l.cash  }} </span> <br />
								<span v-if="l.nequi != null">Nequi: {{  l.nequi  }} </span> <br />
								<span v-if="l.card != null">Tarjeta : {{  l.card  }}</span> <br />
								<span v-if="l.others != null"> Otros medios de pago: {{  l.others  }} </span>
							</td>
							<td>
								{{  (l.total_iva_exc - l.total_cost_price_tax_inc).toFixed(2) | currency  }}
							</td>
						</tr>
					</tbody>
					<tbody v-else>
						<tr>
							<td colspan="3">
								No hay resultados
							</td>
						</tr>
					</tbody>
				</table>
			</section>
		</div>
	</div>
</template>

<script>
export default {
	data() {
		return {
			List: {},
			filter: {
				client: "",
				no_invoice: "",
				from: "",
				to: "",
				status: ""
			},
			statusOrders: [
				{ id: 0, status: "Desechada" },
				{ id: 1, status: "Pedido" },
				{ id: 2, status: "Facturado" },
				{ id: 3, status: "Cotizado" },
				{ id: 4, status: "Facturar e imprimir" },
				{ id: 5, status: "Credito" },
				{ id: 6, status: "Credito e imprimir" }
			],
		};
	},
	methods: {
		getOrders(page = 1) {
			let me = this;

			let data = {
				page: page,
				from: me.filter.from,
				to: me.filter.to,
				no_invoice: me.filter.no_invoice,
				client: me.filter.client,
				status: me.filter.status
			};

			axios
				.get(
					`api/reports/sales-report`,
					{
						params: data,
						headers: this.$root.config.headers,
					})
				.then(function (response) {
					me.List = response.data;
				});
		},
		getTicket() {
            axios
                .post(
                    `api/reports-ticket/sales-report`,
                    { data: this.List },
                    this.$root.config
                )
                .then(function (response) {
                    // me.List = response.data;
                });
        }
	},
	mounted() {
		this.getOrders(1);
	}
};
</script>
