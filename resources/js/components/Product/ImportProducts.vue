<template>
	<div>
		<div
			class="modal fade"
			id="productImportModal"
			tabindex="-1"
			aria-labelledby="productImportModalLabel"
			aria-hidden="true"
		>
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title" id="productImportModalLabel">
							Importación de productos
						</h5>
						<button
							type="button"
							class="close"
							data-dismiss="modal"
							aria-label="Close"
						>
							<span aria-hidden="true">&times;</span>
						</button>
					</div>
					<form enctype="multipart/form-data" @submit.prevent="uploadFile()">
						<div class="modal-body">
							<div class="alert alert-info text-center" role="alert">
								<span>Descargar plantilla para importación de productos</span>
								<a
									href="import/download-example-import"
									target="_blank"
									class="btn btn-light border border-primary"
									type="button"
								>
									<i class="bi bi-cloud-download text-dark"></i> Descargar
								</a>
							</div>
							<div class="custom-file mt-4">
								<input
									type="file"
									class="custom-file-input"
									name="filename"
									id="filename"
									v-on:change="onFileChange"
								/>
								<label class="custom-file-label" for="filename"
									>Subir archivo de importación</label
								>
							</div>
							{{ file != "" ? filename : "" }}
						</div>
						<div class="modal-footer">
							<button
								type="button"
								class="btn btn-secondary"
								data-dismiss="modal"
							>
								Cancelar
							</button>
							<button
								type="submit"
								class="btn btn-primary"
								value="upload"
								@change="isLoading = true"
							>
								Importar
							</button>
						</div>
						<div v-if="reportItems.total_items">
							<h5 class="w-100 text-black-50 text-center">
								Reporte de importaciones
							</h5>

							<ul class="list-group">
								<li
									class="list-group-item d-flex justify-content-between align-items-center"
								>
									Total de productos
									<span class="badge badge-success badge-pillx p-2">{{
										reportItems.total_items
									}}</span>
								</li>
								<li
									class="list-group-item d-flex justify-content-between align-items-center"
								>
									Productos guardados:
									<span class="badge badge-success badge-pillx p-2">{{
										reportItems.item_saved
									}}</span>
								</li>
								<li
									class="list-group-item d-flex justify-content-between align-items-center"
								>
									Productos precio de costo $0 :
									<span class="badge badge-warning badge-pillx p-2">{{
										reportItems.item_cost_null
									}}</span>
								</li>
								<li
									class="list-group-item d-flex justify-content-between align-items-center"
								>
									Productos no guardados:
									<span class="badge badge-danger badge-pillx p-2">{{
										reportItems.item_error
									}}</span>
								</li>
							</ul>
						</div>
						<div class="text-center w-100">
							<bar-loader
								class="m-auto"
								:loading="isLoading"
								:color="'#032F6C'"
								:height="6"
								:width="80"
								widthUnit="%"
							/>
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>
</template>

<script>
export default {
	data() {
		return {
			isLoading: false,
			file: "",
			filename: "",
			reportItems: {}
		};
	},

	methods: {
		onFileChange(e) {
			this.filename = "Archivo Seleccionado: " + e.target.files[0].name;
			this.file = e.target.files[0];
		},
		uploadFile() {
			this.isLoading = true;
			let me = this;

			const config = {
				headers: {
					"content-type": "multipart/form-data",
					"X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]')
						.content
				}
			};

			// form data
			let formData = new FormData();
			formData.append("file", this.file);
			// send upload request
			axios
				.post("api/import/upload-file-import", formData, this.$root.config)
				.then(function(response) {
					me.filename = "";
					me.$emit("list-products");
					me.reportItems = response.data;
				})
				.catch(function(error) {
					me.output = error;
				})
				.finally(
					() => (
						(this.isLoading = false),
						setTimeout(() => {
							$("#productImportModal").modal("hide");
							me.reportItems = {};
						}, 10000)
					)
				);
		}
	}
};
</script>
