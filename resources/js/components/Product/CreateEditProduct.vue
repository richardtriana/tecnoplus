<template>
  <div>
    <div
      class="modal fade"
      id="productModal"
      tabindex="-1"
      aria-labelledby="productModalLabel"
      aria-hidden="true"
      data-backdrop="static"
      data-keyboard="false"
    >
      <div class="modal-dialog modal-lg">
        <div class="modal-content">

          <!-- Encabezado del modal -->
          <div class="modal-header">
            <h5 class="modal-title" id="productModalLabel">Producto</h5>
            <button
              type="button"
              class="close"
              data-dismiss="modal"
              aria-label="Close"
              @click="CloseModal"
            >
              <span aria-hidden="true">&times;</span>
            </button>
          </div>

          <!-- Contenido del modal -->
          <div class="modal-body">
            <form>
              <!-- FILA 1: Código de barras y descripción -->
              <div class="form-row">
                <div class="form-group col-5">
                  <label for="barcode">
                    Código de barras <span class="text-danger">(*)</span>
                  </label>
                  <input
                    type="text"
                    class="form-control"
                    id="barcode"
                    v-model="formProduct.barcode"
                    placeholder="Código de barras"
                    required
                  />
                  <small id="barcodeHelp" class="form-text text-danger">
                    {{ formErrors.barcode }}
                  </small>
                </div>
                <div class="form-group col-7">
                  <label for="product">
                    Descripción Producto <span class="text-danger">(*)</span>
                  </label>
                  <input
                    type="text"
                    class="form-control"
                    id="product"
                    v-model="formProduct.product"
                    placeholder="Nombre o descripción del producto"
                    required
                  />
                  <small id="productHelp" class="form-text text-danger">
                    {{ formErrors.product }}
                  </small>
                </div>
              </div>

              <!-- FILA 2: Fecha de vencimiento y tipo (Por Unidad/Pieza) -->
              <div class="form-row">
                <div class="form-group col-5">
                  <label for="expiration_date">Fecha de vencimiento</label>
                  <input
                    type="date"
                    class="form-control"
                    id="expiration_date"
                    v-model="formProduct.expiration_date"
                    placeholder="Fecha de vencimiento"
                  />
                  <small id="expirationDateHelp" class="form-text text-danger">
                    {{ formErrors.expiration_date }}
                  </small>
                </div>
                <div class="form-group col-7 d-flex align-items-center">
                  <label class="mb-0 mr-2">Tipo de producto:</label>
                  <span class="badge badge-primary">Por Unidad / Pieza</span>
                </div>
              </div>

              <!-- CATEGORÍA y ZONA -->
              <div class="form-row">
                <div class="form-group col-6">
                  <label for="category_id">Categoría</label>
                  <button
                    type="button"
                    class="btn btn-outline-primary ml-3"
                    data-toggle="modal"
                    data-target="#categoryModal"
                    @click="$refs.CreateEditCategory.ResetData()"
                    v-if="$root.validatePermission('category.store')"
                  >
                    Crear Categoría
                  </button>
                  <v-select
                    :options="categoryList"
                    label="name"
                    :reduce="(category) => category.id"
                    v-model="formProduct.category_id"
                  />
                  <small id="category_idHelp" class="form-text text-danger">
                    {{ formErrors.category_id }}
                  </small>
                </div>
                <div class="form-group col-6">
                  <label for="zone_id">Zona</label>
                  <button
                    type="button"
                    class="btn btn-outline-primary ml-3"
                    data-toggle="modal"
                    data-target="#zoneModal"
                    @click="$refs.CreateEditZone.ResetData()"
                    v-if="$root.validatePermission('zone.store')"
                  >
                    Crear Zona
                  </button>
                  <v-select
                    :options="zoneList"
                    label="zone"
                    :reduce="(zone) => zone.id"
                    v-model="formProduct.zone_id"
                    multiple
                  />
                  <small id="zone_idHelp" class="form-text text-danger">
                    {{ formErrors.zone_id }}
                  </small>
                  <div>
                    <small>Zonas actuales:</small>
                    <template v-if="formProduct.zones" v-for="z in formProduct.zones">
                      <span class="badge badge-pill badge-info">{{ z.zone }}</span>
                    </template>
                  </div>
                </div>
              </div>

              <!-- IMPUESTO, UNIDAD DE MEDIDA, ESTÁNDAR IDENTIFICACIÓN -->
              <div class="form-row">
                <div class="form-group col-6">
                  <label for="tax_id">
                    Impuesto % <span class="text-danger">(*)</span>
                  </label>
                  <button
                    type="button"
                    class="btn btn-outline-primary ml-3"
                    data-toggle="modal"
                    data-target="#taxModal"
                    @click="$refs.CreateEditTax.ResetData()"
                    v-if="$root.validatePermission('tax.store')"
                  >
                    Crear Impuesto
                  </button>
                  <select
                    class="form-control"
                    id="tax_id"
                    v-model="formProduct.tax_id"
                    @change="uploadTax(formProduct.tax_id)"
                    required
                  >
                    <option value="0">--Select--</option>
                    <option v-for="t in taxList" :key="t.id" :value="t.id">
                      {{ t.percentage }}
                    </option>
                  </select>
                  <small id="tax_idHelp" class="form-text text-danger">
                    {{ formErrors.tax_id }}
                  </small>
                </div>
                <div class="form-group col-6">
                  <label for="measurement_unit_id">Unidad de Medida</label>
                  <v-select
                    :options="measurementUnitList"
                    label="name"
                    :reduce="(m) => m.id"
                    v-model="formProduct.measurement_unit_id"
                  />
                  <small id="measurement_unit_idHelp" class="form-text text-danger">
                    {{ formErrors.measurement_unit_id }}
                  </small>
                </div>
              </div>

              <div class="form-row">
                <div class="form-group col-6">
                  <label for="product_identification_standard_id">
                    Estándar de Identificación
                  </label>
                  <v-select
                    :options="productIdentificationStandardList"
                    label="name"
                    :reduce="(pis) => pis.id"
                    v-model="formProduct.product_identification_standard_id"
                  />
                  <small id="product_identification_standard_idHelp" class="form-text text-danger">
                    {{ formErrors.product_identification_standard_id }}
                  </small>
                </div>
              </div>

              <hr />

              <!-- PRECIOS E IMPUESTOS (Costo y Venta) -->
              <div class="form-row">
                <div class="form-group col-6">
                  <label for="cost_price_tax_exc">
                    Precio Costo sin IVA <span class="text-danger">(*)</span>
                  </label>
                  <input
                    type="number"
                    step="any"
                    class="form-control"
                    id="cost_price_tax_exc"
                    v-model="formProduct.cost_price_tax_exc"
                    placeholder="Precio de costo"
                    @input="calculateCostTaxValues"
                    required
                  />
                  <small id="cost_price_tax_excHelp" class="form-text text-danger">
                    {{ formErrors.cost_price_tax_exc }}
                  </small>
                  <div>
                    <span class="font-weight-bold text-success">
                      {{ formatCurrency(formProduct.cost_price_tax_exc) }}
                    </span>
                  </div>
                </div>
                <div class="form-group col-6">
                  <label for="cost_price_tax_inc">
                    Precio Costo con IVA <span class="text-danger">(*)</span>
                  </label>
                  <input
                    type="number"
                    step="any"
                    class="form-control"
                    id="cost_price_tax_inc"
                    v-model="formProduct.cost_price_tax_inc"
                    placeholder="Costo con IVA"
                    readonly
                    required
                  />
                  <small id="cost_price_tax_incHelp" class="form-text text-danger">
                    {{ formErrors.cost_price_tax_inc }}
                  </small>
                  <div>
                    <span class="font-weight-bold text-success">
                      {{ formatCurrency(formProduct.cost_price_tax_inc) }}
                    </span>
                  </div>
                </div>
              </div>

              <div class="form-row">
                <div class="form-group col-6">
                  <label for="cost_tax_value">Impuesto en Costo</label>
                  <input
                    type="number"
                    step="any"
                    class="form-control"
                    id="cost_tax_value"
                    v-model="formProduct.cost_tax_value"
                    readonly
                  />
                  <small id="cost_tax_valueHelp" class="form-text text-danger">
                    {{ formErrors.cost_tax_value }}
                  </small>
                  <div>
                    <span class="font-weight-bold text-success">
                      {{ formatCurrency(formProduct.cost_tax_value) }}
                    </span>
                  </div>
                </div>
                <div class="form-group col-6">
                  <label for="sale_price_tax_inc">
                    Precio venta con IVA <span class="text-danger">(*)</span>
                  </label>
                  <input
                    type="number"
                    step="any"
                    class="form-control"
                    id="sale_price_tax_inc"
                    v-model="formProduct.sale_price_tax_inc"
                    placeholder="Venta con IVA"
                    @input="calculateSaleTaxValue"
                    required
                  />
                  <small id="sale_price_tax_incHelp" class="form-text text-danger">
                    {{ formErrors.sale_price_tax_inc }}
                  </small>
                  <div>
                    <span class="font-weight-bold text-success">
                      {{ formatCurrency(formProduct.sale_price_tax_inc) }}
                    </span>
                  </div>
                </div>
              </div>

              <div class="form-row">
                <div class="form-group col-6">
                  <label for="sale_tax_value">Impuesto en Venta</label>
                  <input
                    type="number"
                    step="any"
                    class="form-control"
                    id="sale_tax_value"
                    v-model="formProduct.sale_tax_value"
                    readonly
                  />
                  <small id="sale_tax_valueHelp" class="form-text text-danger">
                    {{ formErrors.sale_tax_value }}
                  </small>
                  <div>
                    <span class="font-weight-bold text-success">
                      {{ formatCurrency(formProduct.sale_tax_value) }}
                    </span>
                  </div>
                </div>
                <div class="form-group col-6">
                  <label for="wholesale_price_tax_inc">
                    Precio Mayoreo con IVA
                  </label>
                  <input
                    type="number"
                    step="any"
                    class="form-control"
                    id="wholesale_price_tax_inc"
                    v-model="formProduct.wholesale_price_tax_inc"
                    placeholder="Mayoreo con IVA"
                  />
                  <small id="wholesale_price_tax_incHelp" class="form-text text-danger">
                    {{ formErrors.wholesale_price_tax_inc }}
                  </small>
                  <div>
                    <span class="font-weight-bold text-success">
                      {{ formatCurrency(formProduct.wholesale_price_tax_inc) }}
                    </span>
                  </div>
                </div>
              </div>

              <hr />

              <!-- INVENTARIO -->
              <div class="form-group">
                <div class="form-check">
                  <input
                    class="form-check-input"
                    type="checkbox"
                    id="stock"
                    v-model="formProduct.stock"
                  />
                  <label class="form-check-label" for="stock">
                    ¿Usa Inventario?
                  </label>
                </div>
                <small id="stockHelp" class="form-text text-danger">
                  {{ formErrors.stock }}
                </small>
              </div>
              <div class="form-row" v-if="formProduct.stock == 1 || formProduct.stock === true">
                <div class="form-group col-md-9">
                  <label for="quantity">Hay</label>
                  <input
                    type="number"
                    step="any"
                    class="form-control"
                    id="quantity"
                    v-model="formProduct.quantity"
                  />
                  <small id="quantityHelp" class="form-text text-danger">
                    {{ formErrors.quantity }}
                  </small>
                </div>
                <div class="form-group col-md-3">
                  <small class="form-text text-muted mt-4">
                    En este momento
                  </small>
                </div>
              </div>
              <div class="form-row" v-if="formProduct.stock == 1 || formProduct.stock === true">
                <div class="form-group col-md-9">
                  <label for="minimum">Mínimo</label>
                  <input
                    type="number"
                    step="any"
                    class="form-control"
                    id="minimum"
                    v-model="formProduct.minimum"
                  />
                  <small id="minimumHelp" class="form-text text-danger">
                    {{ formErrors.minimum }}
                  </small>
                </div>
              </div>
              <div class="form-row" v-if="formProduct.stock == 1 || formProduct.stock === true">
                <div class="form-group col-md-9">
                  <label for="maximum">Máximo</label>
                  <input
                    type="number"
                    step="any"
                    class="form-control"
                    id="maximum"
                    v-model="formProduct.maximum"
                  />
                  <small id="maximumHelp" class="form-text text-danger">
                    {{ formErrors.maximum }}
                  </small>
                </div>
              </div>

              <hr />

              <!-- USA PORCIONES -->
              <div class="form-check mb-3">
                <input
                  class="form-check-input"
                  type="checkbox"
                  id="uses_portions"
                  v-model="formProduct.uses_portions"
                />
                <label class="form-check-label" for="uses_portions">
                  Usa ingredientes (porciones)
                </label>
              </div>

              <!-- SECCIÓN DE PORCIONES -->
              <div v-if="formProduct.uses_portions">
                <h5>Detalles de Porciones</h5>

                <!-- FILA DE BÚSQUEDA Y SELECCIÓN DE PORCIÓN -->
                <div class="form-row align-items-end mb-2">
                  <div class="col-6">
                    <label for="portionSearchSelect">Buscar Porción</label>
                    <v-select
                      id="portionSearchSelect"
                      :options="portionSearchList"
                      label="description"
                      :reduce="(p) => p"
                      :searchable="true"
                      :filterable="true"
                      v-model="newPortionSelected"
                      placeholder="Ingresa el nombre de la porción"
                    />
                  </div>
                  <div class="col-2">
                    <label for="newPortionQuantity">Cantidad</label>
                    <input
                      type="number"
                      step="any"
                      class="form-control"
                      id="newPortionQuantity"
                      v-model="newPortionQuantity"
                    />
                  </div>
                  <div class="col-2">
                    <button class="btn btn-primary" type="button" @click="addPortion">
                      Agregar
                    </button>
                  </div>
                </div>

                <!-- TABLA DE PORCIONES -->
                <table class="table table-bordered table-sm">
                  <thead>
                    <tr>
                      <th>Porción</th>
                      <th style="width: 120px;">Cantidad</th>
                      <th style="width: 100px;">Acción</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="(item, index) in portionList" :key="index">
                      <!-- Se muestra item.portion.description si existe; de lo contrario, 'Porción ID: ' + item.portion_id -->
                      <td>
                        {{ item.portion && item.portion.description ? item.portion.description : ('Porción ID: ' + item.portion_id) }}
                      </td>
                      <td>
                        <input type="number" class="form-control form-control-sm" v-model="item.quantity" />
                      </td>
                      <td>
                        <button class="btn btn-danger btn-sm" @click="removePortion(index)">
                          Eliminar
                        </button>
                      </td>
                    </tr>
                    <tr v-if="portionList.length === 0">
                      <td colspan="3" class="text-center">
                        No hay porciones agregadas
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>

              <!-- BOTONES DEL MODAL -->
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal" @click="CloseModal">
                  Cerrar
                </button>
                <button type="button" class="btn btn-primary" @click="formProduct.id ? EditProduct() : CreateProduct()">
                  Guardar
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>

    <!-- MODAL: CREACIÓN DE CATEGORÍA -->
    <div class="modal fade" id="categoryModal" tabindex="-1" aria-labelledby="categoryModalLabel" aria-hidden="true" data-backdrop="static">
      <div class="modal-dialog modal-dialog-scrollable">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="categoryModalLabel">Categoría</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <create-edit-category ref="CreateEditCategory" @list-categories="listCategories(1)" />
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" @click="closeModal">
              Cerrar
            </button>
            <button type="button" class="btn btn-primary" @click="SaveCategory">
              Guardar
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- MODAL: CREACIÓN/EDICIÓN DE IMPUESTO -->
    <div class="modal fade" id="taxModal" tabindex="-1" aria-labelledby="taxModalLabel" aria-hidden="true" data-backdrop="static">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="taxModalLabel">Tax</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <create-edit-tax ref="CreateEditTax" @list-taxes="listTaxes(1)" />
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-outline-secondary" @click="closeModal">
              Cerrar
            </button>
            <button type="button" class="btn btn-outline-primary" @click="SaveTax">
              Guardar
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- MODAL: CREACIÓN/EDICIÓN DE ZONA -->
    <div class="modal fade" id="zoneModal" tabindex="-1" aria-labelledby="zoneModalLabel" aria-hidden="true" data-backdrop="static">
      <div class="modal-dialog modal-dialog-scrollable">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="zoneModalLabel">Zona</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <create-edit-zone ref="CreateEditZone" @list-categories="listZones(1)" />
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" @click="closeModal">
              Cerrar
            </button>
            <button type="button" class="btn btn-primary" @click="SaveZone">
              Guardar
            </button>
          </div>
        </div>
      </div>
    </div>
    <!-- Se eliminó modal de Marca si no se usa -->
  </div>
</template>

<script>
import global from "../../services/global.js";
import CreateEditCategory from "../Category/CreateEditCategory.vue";
import CreateEditTax from "../Tax/CreateEditTax.vue";
import CreateEditZone from "../Zone/CreateEditZone.vue";
import vSelect from "vue-select";
import axios from "axios";

// Función para formatear números con 2 decimales
function formatNumber(value) {
  if (!value) return "0.00";
  return new Intl.NumberFormat("es-ES", {
    style: "decimal",
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  }).format(value);
}

export default {
  components: {
    CreateEditCategory,
    CreateEditTax,
    CreateEditZone,
    "v-select": vSelect
  },
  data() {
    return {
      tax: { percentage: 19 },
      formProduct: {
        id: null,
        barcode: "",
        product: "",
        type: 1,
        tax_id: 0,
        cost_price_tax_exc: 0.0,
        cost_price_tax_inc: 0.0,
        cost_tax_value: 0.0,
        sale_price_tax_inc: 0.0,
        sale_tax_value: 0.0,
        wholesale_price_tax_inc: 0.0,
        category_id: 0,
        zone_id: null,
        stock: 0,
        minimum: 0.0,
        quantity: 0.0,
        maximum: 0.0,
        expiration_date: "",
        uses_portions: false,
        measurement_unit_id: null,
        product_identification_standard_id: null,
        gain: 0.0,
        sale_price_tax_exc: 0.0,
        wholesale_price_tax_exc: 0.0,
        state: 1,
        zones: []
      },
      portionList: [],
      newPortionSelected: null,
      newPortionQuantity: 1,
      portionSearchList: [],
      taxList: [],
      categoryList: [],
      zoneList: [],
      measurementUnitList: [],
      productIdentificationStandardList: [],
      formErrors: {},
      edit: false,
      searchPortion: ""
    };
  },
  methods: {
    formatCurrency(value) {
      return `$ ${formatNumber(value)}`;
    },
    listTaxes() {
      axios.get("api/taxes", this.$root.config).then((response) => {
        this.taxList = response.data.taxes.data || response.data.taxes;
      });
    },
    listCategories() {
      axios.get("api/categories/category-list?page=1", this.$root.config).then((response) => {
        this.categoryList = response.data.categories;
      });
    },
    listZones() {
      axios.get("api/zones/zone-list?page=1", this.$root.config).then((response) => {
        this.zoneList = response.data.zones;
      });
    },
    loadMeasurementUnits() {
      axios.get("api/measurement-units", this.$root.config)
        .then((r) => (this.measurementUnitList = r.data))
        .catch((err) => console.error("Error al cargar measurement units", err));
    },
    loadProductIdentificationStandards() {
      axios.get("api/product-identification-standards", this.$root.config)
        .then((r) => (this.productIdentificationStandardList = r.data))
        .catch((err) => console.error("Error al cargar product identification standards", err));
    },
    loadPortions() {
      axios.get("api/portions", this.$root.config)
        .then((r) => {
          // Ajustar si tu respuesta es distinta
          this.portionSearchList = r.data.portions.data;
        })
        .catch((err) => console.error("Error al cargar porciones", err));
    },
    OpenEditProduct(product) {
      this.edit = true;
      $("#productModal").modal("show");
      this.formProduct = product;
      this.uploadTax(product.tax_id);

      // Si el producto trae porciones, asignarlas y forzar la visualización de la sección
      if (product.portions && product.portions.length > 0) {
        this.portionList = product.portions;
        this.formProduct.uses_portions = true;
      } else {
        this.formProduct.uses_portions = parseInt(product.uses_portions) === 1;
        this.portionList = [];
      }
    },
    CreateProduct() {
      this.assignErrors(false);
      const data = {
        product: this.formProduct,
        portionList: this.portionList
      };
      axios.post("api/products", data, this.$root.config)
        .then(() => {
          $("#productModal").modal("hide");
          this.CloseModal();
          this.$emit("list-products");
        })
        .catch((response) => {
          this.assignErrors(response);
        });
    },
    EditProduct() {
      this.assignErrors(false);
      const data = {
        product: this.formProduct,
        portionList: this.portionList
      };
      axios.put(`api/products/${this.formProduct.id}`, data, this.$root.config)
        .then(() => {
          $("#productModal").modal("hide");
          this.CloseModal();
          this.edit = false;
          this.$emit("list-products");
        })
        .catch((response) => {
          this.assignErrors(response);
        });
    },
    CloseModal() {
      this.edit = false;
      this.ResetData();
      this.$emit("list-products");
    },
    ResetData() {
      $("#productModal").modal("hide");
      Object.keys(this.formProduct).forEach((key) => {
        if (typeof this.formProduct[key] === "boolean") {
          this.formProduct[key] = false;
        } else if (typeof this.formProduct[key] === "number") {
          this.formProduct[key] = 0;
        } else {
          this.formProduct[key] = "";
        }
      });
      this.formProduct.type = 1;
      this.formProduct.id = null;
      this.formProduct.zones = [];
      this.formProduct.stock = 0;
      this.formProduct.uses_portions = false;
      this.formProduct.sale_price_tax_exc = 0.0;
      this.formProduct.cost_tax_value = 0.0;
      this.formProduct.sale_tax_value = 0.0;
      this.formProduct.gain = 0.0;
      this.portionList = [];
      this.newPortionSelected = null;
      this.newPortionQuantity = 1;
      this.searchPortion = "";
      this.formErrors = {};
    },
    SaveCategory() {
      this.$refs.CreateEditCategory.CreateCategory();
      this.listCategories();
    },
    SaveTax() {
      this.$refs.CreateEditTax.CreateTax();
    },
    SaveZone() {
      this.$refs.CreateEditZone.CreateZone();
    },
    uploadTax(tax_id) {
      let result = this.taxList.find((tax) => tax.id == tax_id);
      if (result) {
        this.tax.percentage = result.percentage;
      } else {
        this.tax.percentage = 0;
      }
      this.calculateCostTaxValues();
      this.calculateSaleTaxValue();
    },
    calculateCostTaxValues() {
      const exc = parseFloat(this.formProduct.cost_price_tax_exc) || 0;
      const percentage = parseFloat(this.tax.percentage) || 0;
      const inc = exc * (1 + percentage / 100);
      this.formProduct.cost_price_tax_inc = parseFloat(inc.toFixed(2));
      this.formProduct.cost_tax_value = parseFloat((inc - exc).toFixed(2));
    },
    calculateSaleTaxValue() {
      const percentage = parseFloat(this.tax.percentage) || 0;
      const saleInc = parseFloat(this.formProduct.sale_price_tax_inc) || 0;
      const saleExc = saleInc / (1 + percentage / 100);
      this.formProduct.sale_price_tax_exc = parseFloat(saleExc.toFixed(2));
      this.formProduct.sale_tax_value = parseFloat((saleInc - saleExc).toFixed(2));
      const costInc = parseFloat(this.formProduct.cost_price_tax_inc) || 0;
      this.formProduct.gain = parseFloat((saleExc - costInc).toFixed(2));
    },
    addPortion() {
    if (!this.newPortionSelected) return;
    let found = false;
    // Compara usando portion_id o id
    this.portionList.forEach((p) => {
      const currentId = p.portion_id ? p.portion_id : p.id;
      if (currentId === this.newPortionSelected.id) {
        found = true;
        p.quantity = parseFloat(p.quantity) + parseFloat(this.newPortionQuantity);
      }
    });
    if (!found) {
      // Guardamos también la propiedad portion para mostrar su descripción
      this.portionList.push({
        portion_id: this.newPortionSelected.id,
        portion: this.newPortionSelected,
        quantity: parseFloat(this.newPortionQuantity)
      });
    }
    this.newPortionSelected = null;
    this.newPortionQuantity = 1;
  },


    removePortion(index) {
      this.portionList.splice(index, 1);
    },
    assignErrors(response) {
      const fillable = [
        "barcode",
        "product",
        "type",
        "cost_price_tax_exc",
        "cost_price_tax_inc",
        "cost_tax_value",
        "sale_price_tax_inc",
        "sale_tax_value",
        "wholesale_price_tax_inc",
        "stock",
        "quantity",
        "minimum",
        "maximum",
        "state",
        "category_id",
        "zone_id",
        "tax_id",
        "expiration_date",
        "measurement_unit_id",
        "product_identification_standard_id",
        "sale_price_tax_exc",
        "gain"
      ];
      if (response) {
        const errors = response.response ? response.response.data.errors : null;
        if (errors) {
          fillable.forEach((index) => {
            if (errors[index] !== undefined) {
              this.formErrors[index] = errors[index][0];
            }
          });
        }
      } else {
        fillable.forEach((index) => {
          this.formErrors[index] = "";
        });
      }
    },
    closeModal() {
      $("#categoryModal").modal("hide");
      $("#taxModal").modal("hide");
      $("#zoneModal").modal("hide");
    }
  },
  created() {
    this.listTaxes();
    this.listCategories();
    this.listZones();
    this.loadMeasurementUnits();
    this.loadProductIdentificationStandards();
    this.loadPortions();
  }
};
</script>

<style scoped>
.v-select {
  max-width: 100%;
}
.table-bordered {
  margin-bottom: 0;
}
.font-weight-bold.text-success {
  display: block;
  margin-top: 4px;
}
</style>
