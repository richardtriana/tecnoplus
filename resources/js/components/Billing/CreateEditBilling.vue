<template>
	<div class="row px-2">
		<div class="col-9 justify-content-center p-2">
			<div class="sticky-top mb-2 text-uppercase w-50" style="z-index: 1022; left: 100%">
				<table class="table table-borderless">
					<tr class="h1 text-white bg-primary">
						<td class="text-right">Total</td>
						<td>
							$
							{{ (billing.total_tax_inc = total_tax_inc).toFixed(0) }}
						</td>
					</tr>
				</table>

				<!-- </div> -->
			</div>
			<div class="position-fixed top-0 right-0 w-50" style="z-index: 3000">
				<div class="toast fade hide border border-danger w-100 m-3" style="max-width: 90%" role="alert"
					id="no-results" aria-live="assertive" aria-atomic="true" data-delay="3000">
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
					<input type="text" class="form-control" placeholder="Código de barras"
						aria-label=" with two button addons" aria-describedby="button-add-product" v-model="filters.product"
						autofocus @keypress.enter="searchProduct()" />
					<div class="input-group-append" id="button-add-product">
						<button class="btn btn-outline-secondary" type="button" @click="searchProduct()">
							Añadir Producto
						</button>
						<button class="btn btn-outline-secondary" type="button" data-toggle="modal"
							data-target="#addProductModal">
							<i class="bi bi-card-checklist"></i>
						</button>
					</div>
				</div>
				<div class="input-group col-6">
					<input type="text" class="form-control" :placeholder="billing.supplier"
						aria-label=" with two button addons" aria-describedby="button-addon4" v-model="filters.supplier"
						@keypress.enter="searchSupplier()" />
					<div class="input-group-append" id="button-addon4">
						<button class="btn btn-outline-secondary" type="button" @click="searchSupplier()">
							Añadir Proveedor
						</button>
						<button class="btn btn-outline-secondary" type="button" data-toggle="modal"
							data-target="#addSupplierModal">
							<i class="bi bi-person-lines-fill"></i>
						</button>
					</div>
				</div>
			</div>

			<section>
				<div>
					<table class="
		              table table-sm table-responsive-sm table-bordered table-hover
		            ">
						<thead class="bg-secondary text-white position-sticky sticky-top" style="top: 4rem">
							<tr>
								<th></th>
								<th>Código</th>
								<th>Producto</th>
								<th>Cantidad</th>
								<th>Precio de costo</th>
								<th>Descuento %</th>
								<th>Descuento $</th>
								<th>Precio de venta <br> <small>IVA inc</small>
								</th>
								<th>Total</th>
							</tr>
						</thead>
						<tbody v-if="productsBillingList.length > 0">
							<tr v-for="(p, index) in productsBillingList" :key="p.id">
								<td>
									<button class="btn text-danger" @click="removeProduct(index, p.id)">
										<i class="bi bi-trash"></i>
									</button>
								</td>

								<td class="barcode">{{ p.barcode }}</td>
								<td>{{ p.product }}</td>
								<td>
									<input type="number" name="quantity" id="quantity" step="any" placeholder="Cantidad"
										class="form-control form-control-sm" v-model="p.quantity" style="max-width: 60px" />
								</td>
								<td>
									<input type="number" name="price" id="price" step="any" placeholder="Precio"
										v-model="p.cost_price_tax_inc" class="form-control form-control-sm"
										style="max-width: 100px" />
								</td>
								<td>
									<input type="number" name="discount_percentage" id="discount_percentage" step="any"
										placeholder="Descuento" class="form-control form-control-sm"
										v-model="p.discount_percentage" style="max-width: 60px" />
								</td>
								<td>
									<input v-if="p.discount_percentage != 0" type="number"
										class="form-control form-control-sm" name="discount_price" id="discount_price"
										step="2" placeholder="Descuento" :value="
											(p.discount_price = (
												p.quantity *
												p.cost_price_tax_inc *
												(p.discount_percentage / 100)
											).toFixed(0))
										" style="max-width: 100px" />
									<input v-else type="number" class="form-control form-control-sm" name="discount_price"
										id="discount_price" step="2" v-model="p.discount_price" style="max-width: 100px" />
								</td>
								<td>
									<input type="number" name="sale_price_tax_inc" id="sale_price_tax_inc" step="any"
										placeholder="Precio de venta" v-model="p.sale_price_tax_inc"
										class="form-control form-control-sm" style="max-width: 100px" />
								</td>
								<td>
									<span v-if="p.discount_percentage != 0">
										$
										{{
											(p.cost_price_tax_inc_total =
												p.quantity * p.cost_price_tax_inc -
												p.quantity *
												p.cost_price_tax_inc *
												(p.discount_percentage / 100))
										}}
									</span>
									<span v-else>
										$
										{{
											(p.cost_price_tax_inc_total =
												p.quantity * p.cost_price_tax_inc - p.discount_price).toFixed(2)
										}}
									</span>
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
				<section class="card">
					<div>
						<table class="table table-sm table-primary text-right">
							<tr>
								<th colspan="7">Subtotal:</th>
								<th>
									$
									{{ (billing.total_tax_exc = total_tax_exc).toFixed(0) }}
								</th>
							</tr>
							<tr>
								<th colspan="7">IVA:</th>
								<th>
									$
									{{ (total_tax_inc - total_tax_exc).toFixed(0) }}
								</th>
							</tr>
							<tr>
								<th colspan="7">Descuento:</th>
								<th>
									$
									{{ (billing.total_discount = total_discount).toFixed(0) }}
								</th>
							</tr>
							<tr class="bg-primary h5 text-white">
								<th colspan="7">Total:</th>
								<th>
									$
									{{ (billing.total_tax_inc = total_tax_inc).toFixed(0) }}
								</th>
							</tr>
							<tr class="">
								<th colspan="7">Efectivo:</th>
								<th>
									<input type="number" value="0" step="any" v-model="billing.cash" />
								</th>
							</tr>
							<tr class="">
								<th colspan="7">Cambio:</th>
								<th>
									<input type="text" :value="payment_return" readonly disabled />
								</th>
							</tr>
						</table>
					</div>
				</section>
				<div class="">
					<button type="button" class="btn btn-outline-primary btn-block" @click="createOrUpdateBilling(2)">
						<!-- Facturar -->

						<i class="bi bi-receipt"></i> <b>F1</b> Guardar
					</button>
					<router-link to="/billings" type="button" class="btn btn-outline-secondary btn-block"
						v-if="billing_id != 0">
						<i class="bi bi-receipt"></i> Cancelar
					</router-link>
				</div>
			</div>
		</div>

		<add-product @add-product="addProduct($event)" />
		<add-supplier @add-supplier="addSupplier($event)" />
	</div>
</template>

<script>
import AddProduct from "../Order/AddProduct.vue";
import AddSupplier from "./AddSupplier.vue";

export default {
	components: { AddProduct, AddSupplier },
	props: ["billing_id"],

	data() {
		return {
			// add product or supplier with keyup
			filters: {
				product: "",
				supplier: ""
			},
			productsBillingList: [],

			billing: {
				id_supplier: 1,
				supplier: "Sin proveedor",
				state: 1,
				total_tax_inc: 0.0,
				total_tax_exc: 0.0,
				total_discount: 0.0,
				productsBilling: [],
				cash: 0,
				change: 0
			}
		};
	},
	computed: {
		total_tax_exc: function () {
			var total = 0.0;
			this.productsBillingList.forEach(
				product =>
					(total += parseFloat(product.cost_price_tax_exc * product.quantity))
			);
			return total;
		},
		total_discount: function () {
			var total = 0.0;
			this.productsBillingList.forEach(product => {
				total += parseFloat(product.discount_price);
			});
			return total;
		},
		total_tax_inc: function () {
			var total = 0.0;
			this.productsBillingList.forEach(product => {
				total += parseFloat(
					product.quantity * product.cost_price_tax_inc -
					product.quantity *
					product.cost_price_tax_inc *
					(product.discount_percentage / 100)
				);
			});
			return total;
		},
		payment_return: function () {
			var value = 0.0;
			if (this.billing.cash > 0) {
				value = (this.billing.cash - this.total_tax_inc).toFixed(0);
			}
			return value;
		}
	},
	methods: {
		listItemsBilling() {
			if (this.billing_id == 0) {
				return false;
			}

			let me = this;

			axios
				.get(`api/billings/${me.billing_id}`, this.$root.config)
				.then(function (response) {
					me.billing.id_supplier = response.data.billing_information.supplier_id;
					me.billing.supplier = response.data.billing_information.supplier.name;
					me.productsBillingList = response.data.billing_details;
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
			me.productsBillingList.filter(prod => {
				if (new_product.barcode == prod.barcode) {
					result = true;
					if (result) {
						// Añade cantidad
						prod.quantity += 1;
						prod.cost_price_tax_inc_total = prod.cost_price_tax_inc * prod.quantity;
					}
				}
			});

			if (!result) {
				// Sino, lo añade al array
				me.productsBillingList.push({
					product_id: new_product.id,
					barcode: new_product.barcode,
					discount_percentage: 0,
					discount_price: 0,
					quantity: 1,
					cost_price_tax_inc: new_product.cost_price_tax_inc,
					cost_price_tax_exc: new_product.cost_price_tax_exc,
					sale_price_tax_exc: new_product.sale_price_tax_exc,
					sale_price_tax_inc: new_product.sale_price_tax_inc,
					product: new_product.product,
					cost_price_tax_inc_total: new_product.cost_price_tax_inc
				});
			}
		},
		removeProduct(index, detail_id = null) {
			this.productsBillingList.splice(index, 1);
			if (detail_id != null || detail_id != 0) {
				axios.delete(`api/billing-details/${detail_id}`, this.$root.config);
			}
		},
		searchSupplier() {
			let me = this;
			if (me.filters.supplier == "") {
				return false;
			}
			var url = "api/clients/search-supplier?supplier=" + me.filters.supplier;
			axios
				.post(url, null, me.$root.config)
				.then(function (response) {
					var new_client = response.data;
					if (!new_client) {
						$("#no-results").toast("show");
					} else {
						me.addSupplier(new_client);
					}
				})
				.catch(function (error) {
					console.log(error);
				});
		},
		addSupplier(supplier) {
			let me = this;
			me.billing.id_supplier = supplier.id;
			me.billing.supplier = supplier.name;
			me.filters.supplier = supplier.name;
		},

		createOrUpdateBilling(state_billing) {
			this.billing.state = state_billing;
			if (this.productsBillingList.length > 0) {
				this.billing.productsBilling = this.productsBillingList;
				if (this.billing_id != 0 && this.billing_id != null) {
					axios
						.put(
							`api/billings/${this.billing_id}`,
							this.billing,
							this.$root.config
						)
						.then(
							() => (
								Swal.fire({
									icon: 'success',
									title: 'Excelente',
									text: 'Los datos se han guardado correctamente',
								})
							)
						)
						.catch(function (error) {
							// handle error
							console.log('error', error)
							if (error) {
								Swal.fire({
									icon: 'error',
									title: 'Oops...',
									text: 'Hubo un error al guardar los datos',
								})
							}
						})
						.finally(
							this.$router.push("/billings")
						);
				} else {
					axios
						.post(`api/billings`, this.billing, this.$root.config)
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
						.finally(() =>
							setTimeout(() => {
								this.$router.go(0)
							}, 3000)
						);
				}
			} else {
				alert("No hay productos en la orden");
			}
		},
		commands() {
			let me = this;
			shortcut.add("F1", function () {
				me.createOrUpdateBilling(2);
			});

			shortcut.add("F2", function () {
				me.createOrUpdateBilling(4);
			});

			shortcut.add("F10", function () {
				$("#addProductModal").modal("show");
			});
		}
	},
	mounted() {
		$("#no-results").toast("hide");
		if (this.billing_id != null || this.billing_id != 0) {
			this.listItemsBilling();
		}
		this.commands();
	}
};
</script>
