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
          <div class="modal-header">
            <h5 class="modal-title" id="productModalLabel">Producto</h5>
            <button
              type="button"
              class="close"
              data-dismiss="modal"
              aria-label="Close"
              @click="CloseModal()"
            >
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <form>
              <div class="form-row">
                <div class="form-group col-5">
                  <label for="barcode"
                    >Codigo de barras
                    <span class="text-danger">(*)</span></label
                  >
                  <input
                    type="text"
                    step="any"
                    class="form-control"
                    id="barcode"
                    v-model="formProduct.barcode"
                    placeholder="Código de barras"
                    required
                  />
                  <span class="barcode">{{ formProduct.barcode }}</span>
                  <small id="barcodeHelp" class="form-text text-danger">{{
                    formErrors.barcode
                  }}</small>
                </div>
                <div class="form-group col-7">
                  <label for="product"
                    >Descripción Producto
                    <span class="text-danger">(*)</span></label
                  >
                  <input
                    type="text"
                    class="form-control"
                    id="product"
                    v-model="formProduct.product"
                    placeholder="Nombre o descripción del producto"
                    required
                  />
                  <small id="productHelp" class="form-text text-danger">{{
                    formErrors.product
                  }}</small>
                </div>
                <div class="form-group col-5">
                  <label for="expiration_date"
                    >Fecha de vencimiento
                  </label>
                  <input
                    type="date"
                    step="any"
                    class="form-control"
                    id="expiration_date"
                    v-model="formProduct.expiration_date"
                    placeholder="Código de barras"
                  />
                  <span class="expiration_date">{{
                    formProduct.expiration_date
                  }}</span>
                  <small
                    id="expirationDateHelp"
                    class="form-text text-danger"
                    >{{ formErrors.expiration_date }}</small
                  >
                </div>
              </div>
              <div class="form-group">
                <div class="form-check form-check-inline">
                  <input
                    class="form-check-input"
                    type="radio"
                    name="type"
                    id="unidad"
                    v-model="formProduct.type"
                    value="1"
                    checked
                  />
                  <label class="form-check-label" for="unidad"
                    >Por Unidad / Pieza</label
                  >
                </div>
                <div class="form-check form-check-inline">
                  <input
                    class="form-check-input"
                    type="radio"
                    name="type"
                    id="granel"
                    v-model="formProduct.type"
                    value="2"
                  />
                  <label class="form-check-label" for="granel"
                    >A granel (Usa decimales)</label
                  >
                </div>
                <div class="form-check form-check-inline">
                  <input
                    class="form-check-input"
                    type="radio"
                    name="type"
                    id="kit"
                    v-model="formProduct.type"
                    value="3"
                  />
                  <label
                    class="form-check-label"
                    for="kit"
                    data-toggle="modal"
                    data-target="#addProductModal"
                    >Como paquete</label
                  >
                </div>
                <div class="">
                  <small id="typeHelp" class="form-text text-danger">{{
                    formErrors.type
                  }}</small>
                </div>
                <hr />
                <add-product-kit
                  v-if="formProduct.type == 3"
                  @add-product="addProduct($event)"
                />
                <table
                  class="table table-sm table-bordered table-secondary mt-2"
                  v-if="formProduct.type == 3"
                >
                  <thead>
                    <tr>
                      <th>#</th>
                      <th>Código de barras</th>
                      <th>Producto</th>
                      <th>Cantidad</th>
                      <th></th>
                    </tr>
                  </thead>
                  <tbody v-if="listItemsKit.length > 0">
                    <tr v-for="(item, index) in listItemsKit" :key="item.id">
                      <td>{{ index }}</td>
                      <td>{{ item.barcode }}</td>
                      <td>{{ item.product }}</td>
                      <td>
                        <input
                          type="number"
                          step="any"
                          v-model="item.quantity"
                        />
                      </td>
                      <td>
                        <button
                          class="btn btn-danger btn-sm"
                          @click="removeProduct(index, item.id)"
                        >
                          <i class="bi bi-trash"></i>
                        </button>
                      </td>
                    </tr>
                  </tbody>
                  <tbody v-else>
                    <tr>
                      <td colspan="4">No se han añadido productos</td>
                    </tr>
                  </tbody>
                </table>
              </div>
              <div class="form-row">
                <div class="form-group col-6">
                  <label for="category_id">Categoria</label>
                  <button
                    type="button"
                    class="btn btn-outline-primary ml-3"
                    data-toggle="modal"
                    data-target="#categoryModal"
                    @click="
                      $refs.CreateEditCategory.ResetData(), (edit = false)
                    "
                    v-if="$root.validatePermission('category.store')"
                  >
                    Crear Categoria
                  </button>
                  <v-select
                    :options="categoryList"
                    label="name"
                    :reduce="(category) => category.id"
                    v-model="formProduct.category_id"
                  />
                  <small id="category_idHelp" class="form-text text-danger">{{
                    formErrors.category_id
                  }}</small>
                </div>
                <div class="form-group col-6">
                  <label for="zone_id">Zona</label>
                  <button
                    type="button"
                    class="btn btn-outline-primary ml-3"
                    data-toggle="modal"
                    data-target="#zoneModal"
                    @click="
                      $refs.CreateEditZone.ResetData(), (edit = false)
                    "
                    v-if="$root.validatePermission('category.store')"
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
                  <small id="zone_idHelp" class="form-text text-danger">{{
                    formErrors.zone_id
                  }}</small>
                  <div> <small>Zonas actuales:</small>
                     <template v-if="formProduct.zones" v-for="z in formProduct.zones">
                      <span class="badge badge-pill badge-info">{{z.zone}}</span>
                  </template>
                </div>
                </div>
                <div class="form-group col-6">
                  <label for="brand_id">Marca</label>
                  <button
                    type="button"
                    class="btn btn-outline-primary ml-3"
                    data-toggle="modal"
                    data-target="#brandModal"
                    v-if="$root.validatePermission('brand.store')"
                  >
                    Crear Marca
                  </button>
                  <v-select
                    :options="brandList"
                    label="name"
                    :reduce="(brand) => brand.id"
                    v-model="formProduct.brand_id"
                  />
                  <small id="brand_idHelp" class="form-text text-danger">{{
                    formErrors.brand_id
                  }}</small>
                </div>
                <div class="form-group col-6">
                  <label for="tax_id"
                    >Impuesto % <span class="text-danger">(*)</span></label
                  >
                  <button
                    type="button"
                    class="btn btn-outline-primary ml-3"
                    data-toggle="modal"
                    data-target="#taxModal"
                    @click="$refs.CreateEditTax.ResetData(), (edit = false)"
                    v-if="$root.validatePermission('tax.store')"
                  >
                    Crear Impuesto
                  </button>
                  <select
                    class="form-control"
                    id="tax_id"
                    v-model="formProduct.tax_id"
                    @click="uploadTax(formProduct.tax_id)"
                    required
                  >
                    <option value="0">--Select--</option>
                    <option v-for="t in taxList" :key="t.id" :value="t.id">
                      {{ t.percentage }}
                    </option>
                  </select>
                  <small id="tax_idHelp" class="form-text text-danger">{{
                    formErrors.tax_id
                  }}</small>
                </div>
              </div>
              <hr />
              <div class="form-row">
                <div class="form-group col-6">
                  <label for="cost_price_tax_exc"
                    >Precio Costo sin IVA
                    <span class="text-danger">(*)</span></label
                  >
                  <input
                    type="number"
                    step="any"
                    class="form-control"
                    id="cost_price_tax_exc"
                    v-model="formProduct.cost_price_tax_exc"
                    placeholder="Precio de costo"
                    required
                  />
                  <small
                    id="cost_price_tax_excHelp"
                    class="form-text text-danger"
                    >{{ formErrors.cost_price_tax_exc }}</small
                  >
                </div>
                <div class="form-group col-6">
                  <label for="cost_price_tax_inc"
                    >Precio Costo con IVA
                    <span class="text-danger">(*)</span></label
                  >
                  <input
                    type="number"
                    step="any"
                    class="form-control"
                    id="cost_price_tax_inc"
                    v-model="formProduct.cost_price_tax_inc"
                    placeholder="Precio de costo"
                    required
                  />
                  <small
                    id="cost_price_tax_incHelp"
                    class="form-text text-danger"
                    >{{ formErrors.cost_price_tax_inc }}</small
                  >
                </div>
                <div class="form-group col-6">
                  <label for="sale_price_tax_exc">Precio venta sin iva</label>
                  <input
                    type="number"
                    step="any"
                    class="form-control"
                    id="sale_price_tax_exc"
                    readonly
                    :value="formProduct.sale_price_tax_exc"
                    placeholder=""
                  />
                  <div class="d-none">
                    {{ (formProduct.sale_price_tax_exc = sale_price_tax_exc) }}
                  </div>
                  <small
                    id="sale_price_tax_excHelp"
                    class="form-text text-danger"
                    >{{ formErrors.sale_price_tax_exc }}</small
                  >
                </div>
                <div class="form-group col-6">
                  <label for="gain">Ganancia</label>
                  <input
                    type="number"
                    step="any"
                    class="form-control"
                    id="gain"
                    placeholder=""
                    readonly="readonly"
                    v-model="formProduct.gain"
                  />
                  <div class="d-none">
                    {{ (formProduct.gain = gain) }}
                  </div>
                  <small id="gainHelp" class="form-text text-danger">{{
                    formErrors.gain
                  }}</small>
                </div>
                <div class="form-group col-6">
                  <label for="sale_price_tax_inc"
                    >Precio venta con iva
                    <span class="text-danger">(*)</span></label
                  >
                  <input
                    type="number"
                    step="any"
                    class="form-control"
                    id="sale_price_tax_inc"
                    v-model="formProduct.sale_price_tax_inc"
                    placeholder="Impuesto Incluído"
                    required
                  />
                  <small
                    id="sale_price_tax_incHelp"
                    class="form-text text-danger"
                    >{{ formErrors.sale_price_tax_inc }}</small
                  >
                </div>
                <div class="form-group col-6">
                  <label for="wholesale_price_tax_exc"
                    >Precio Mayoreo sin iva</label
                  >
                  <input
                    type="number"
                    step="any"
                    class="form-control"
                    id="wholesale_price_tax_exc"
                    v-model="formProduct.wholesale_price_tax_exc"
                    placeholder=""
                    readonly
                  />
                  <div class="d-none">
                    {{
                      (formProduct.wholesale_price_tax_exc =
                        wholesale_price_tax_exc)
                    }}
                  </div>
                  <small
                    id="wholesale_price_tax_excHelp"
                    class="form-text text-danger"
                    >{{ formErrors.wholesale_price_tax_exc }}</small
                  >
                </div>
                <div class="form-group col-6">
                  <label for="wholesale_price_tax_inc"
                    >Precio Mayoreo con iva</label
                  >
                  <input
                    type="number"
                    step="any"
                    class="form-control"
                    id="wholesale_price_tax_inc"
                    v-model="formProduct.wholesale_price_tax_inc"
                    placeholder="impuesto incluído"
                  />
                  <small
                    id="wholesale_price_tax_incHelp"
                    class="form-text text-danger"
                    >{{ formErrors.wholesale_price_tax_inc }}</small
                  >
                </div>
              </div>
              <hr />

              <div class="form-group">
                <div class="form-check">
                  <input
                    class="form-check-input"
                    type="checkbox"
                    value="0"
                    id="stock"
                    v-model="formProduct.stock"
                  />
                  <label class="form-check-label" for="stock">
                    ¿Usa Inventario?
                  </label>
                </div>
                <small id="stockHelp" class="form-text text-danger">{{
                  formErrors.stock
                }}</small>
              </div>
              <div class="form-row" v-if="formProduct.stock == 1">
                <div class="form-group col-md-9">
                  <label for="quantity">Hay</label>
                  <input
                    type="number"
                    step="any"
                    class="form-control"
                    id="quantity"
                    v-model="formProduct.quantity"
                  />
                  <small id="quantityHelp" class="form-text text-danger">{{
                    formErrors.quantity
                  }}</small>
                </div>
                <div class="form-group col-md-3">
                  <small id="" class="form-text text-muted mt-4">
                    En este momento
                  </small>
                </div>
              </div>
              <div class="form-row" v-if="formProduct.stock == 1">
                <div class="form-group col-md-9">
                  <label for="minimum">Mínimo</label>
                  <input
                    type="number"
                    step="any"
                    class="form-control"
                    id="minimum"
                    v-model="formProduct.minimum"
                  />
                  <small id="minimumHelp" class="form-text text-danger">{{
                    formErrors.minimum
                  }}</small>
                </div>
              </div>
              <div class="form-row" v-if="formProduct.stock == 1">
                <div class="form-group col-md-9">
                  <label for="maximum">Máximo</label>
                  <input
                    type="number"
                    step="any"
                    class="form-control"
                    id="maximum"
                    v-model="formProduct.maximum"
                  />
                  <small id="maximumHelp" class="form-text text-danger">{{
                    formErrors.maximum
                  }}</small>
                </div>
              </div>
              <div class="modal-footer">
                <button
                  type="button"
                  class="btn btn-secondary"
                  data-dismiss="modal"
                  aria-label="Close"
                  @click="CloseModal()"
                >
                  Cerrar
                </button>
                <button
                  type="button"
                  class="btn btn-primary"
                  @click="formProduct.id ? EditProduct() : CreateProduct()"
                >
                  Guardar
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
    <div
      class="modal fade"
      id="categoryModal"
      tabindex="-1"
      aria-labelledby="categoryModalLabel"
      aria-hidden="true"
      data-backdrop="static"
    >
      <div class="modal-dialog modal-dialog-scrollable">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="categoryModalLabel">Categoria</h5>
            <button
              type="button"
              class="close"
              data-dismiss="modal"
              aria-label="Close"
            >
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <create-edit-category
              ref="CreateEditCategory"
              @list-categories="listCategories(1)"
            />
          </div>
          <div class="modal-footer">
            <button
              type="button"
              class="btn btn-secondary"
              @click="closeModal()"
            >
              Close
            </button>
            <button
              type="button"
              class="btn btn-primary"
              @click="SaveCategory()"
            >
              Guardar
            </button>
          </div>
        </div>
      </div>
    </div>
    <create-edit-brand ref="CreateEditBrand" @list-brands="listBrands(1)" />
    <div
      class="modal fade"
      id="taxModal"
      tabindex="-1"
      aria-labelledby="taxModalLabel"
      aria-hidden="true"
      data-backdrop="static"
    >
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="taxModalLabel">Tax</h5>
            <button
              type="button"
              class="close"
              data-dismiss="modal"
              aria-label="Cerrar"
            >
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <create-edit-tax ref="CreateEditTax" @list-taxes="listTaxes(1)" />
          </div>
          <div class="modal-footer">
            <button
              type="button"
              class="btn btn-outline-secondary"
              @click="closeModal()"
            >
              Cerrar
            </button>
            <button
              type="button"
              class="btn btn-outline-primary"
              @click="SaveTax()"
            >
              Guardar
            </button>
          </div>
        </div>
      </div>
    </div>

    <div
      class="modal fade"
      id="zoneModal"
      tabindex="-1"
      aria-labelledby="zoneModalLabel"
      aria-hidden="true"
      data-backdrop="static"
    >
      <div class="modal-dialog modal-dialog-scrollable">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="zoneModalLabel">Zona</h5>
            <button
              type="button"
              class="close"
              data-dismiss="modal"
              aria-label="Close"
            >
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <create-edit-zone
              ref="CreateEditZone"
              @list-categories="listZones(1)"
            />
          </div>
          <div class="modal-footer">
            <button
              type="button"
              class="btn btn-secondary"
              @click="closeModal()"
            >
              Close
            </button>
            <button
              type="button"
              class="btn btn-primary"
              @click="SaveZone()"
            >
              Guardar
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import global from "../../services/global.js";
import AddProductKit from "./AddProductKit.vue";
import CreateEditCategory from "../Category/CreateEditCategory.vue";
import CreateEditBrand from "../Brand/CreateEditBrand.vue";
import CreateEditTax from "../Tax/CreateEditTax.vue";
import CreateEditZone from "../Zone/CreateEditZone.vue";

export default {
  data() {
    return {
      //Variables de producto
      tax: {
        percentage: 19,
      },
      formProduct: {
        barcode: "",
        product: "",
        type: 0,
        tax_id: 0,
        cost_price_tax_exc: 0.0,
        cost_price_tax_inc: 0.0,
        gain: 0.0,
        sale_price_tax_exc: 0.0,
        sale_price_tax_inc: 0.0,
        wholesale_price_tax_exc: 0.0,
        wholesale_price_tax_inc: 0.0,
        category_id: 0,
        brand_id: 0,
        zone_id: null,
        stock: 0,
        minimum: 0.0,
        quantity: 0.0,
        maximum: 0.0,
        expiration_date: "",
      },
      listItemsKit: [],
      taxList: [],
      categoryList: [],
      brandList: [],
      zoneList: [],
      formErrors: {
        barcode: "",
        product: "",
        type: "",
        cost_price_tax_exc: "",
        cost_price_tax_inc: "",
        gain: "",
        sale_price_tax_exc: "",
        sale_price_tax_inc: "",
        wholesale_price_tax_exc: "",
        wholesale_price_tax_inc: "",
        stock: "",
        quantity: "",
        minimum: "",
        maximum: "",
        state: 1,
        category_id: "",
        tax_id: "",
        zone_id: "",
        brand_id: "",
        expiration_date: "",
      },
    };
  },
  components: {
    AddProductKit,
    CreateEditCategory,
    CreateEditBrand,
    CreateEditTax,
    CreateEditZone
  },
  computed: {
    gain: function () {
      var result = 0.0;
      if (
        this.formProduct.sale_price_tax_inc != 0 &&
        this.formProduct.tax_id != 0
      ) {
        result = parseFloat(
          this.formProduct.sale_price_tax_exc -
            this.formProduct.cost_price_tax_inc
        );
        return result;
      }
      return result;
    },
    sale_price_tax_exc: function () {
      var result = 0.0;
      if (this.formProduct.tax_id != 0) {
        let percentage = this.tax.percentage / 100;
        result = Math.round(
          parseFloat(this.formProduct.sale_price_tax_inc) / (1 + percentage)
        ).toFixed(2);
        return result;
      }
      return result;
    },
    wholesale_price_tax_exc() {
      var result = 0.0;
      if (this.formProduct.tax_id != 0) {
        let percentage = this.tax.percentage / 100;
        result = Math.round(
          parseFloat(this.formProduct.wholesale_price_tax_inc) /
            (1 + percentage)
        ).toFixed(2);

        return result;
      }
      return result;
    },
  },
  methods: {
    listTaxes() {
      let me = this;
      axios.get("api/taxes", me.$root.config).then(function (response) {
        me.taxList = response.data.taxes.data;
      });
    },
    listBrands() {
      let me = this;
      axios
        .get(`api/brands/brand-list`, this.$root.config)
        .then(function (response) {
          me.brandList = response.data.brands;
        });
    },
    listCategories() {
      let me = this;
      axios
        .get("api/categories/category-list?page=1", me.$root.config)
        .then(function (response) {
          me.categoryList = response.data.categories;
        });
    },
    listZones() {
      let me = this;
      axios
        .get("api/zones/zone-list?page=1", me.$root.config)
        .then(function (response) {
          me.zoneList = response.data.zones;
        });
    },
    OpenEditProduct(product) {
      this.edit = true;
      let me = this;
      $("#productModal").modal("show");
      me.formProduct = product;
      me.uploadTax(product.tax_id);
      if (product.type == 3) {
        axios
          .get(`api/kit-products?parent_id=${product.id}`, me.$root.config)
          .then((response) => (me.listItemsKit = response.data))
          .catch();
      }
    },
    CreateProduct() {
      let me = this;
      me.assignErrors(false);

      var data = {
        product: me.formProduct,
        itemListKit: me.listItemsKit,
      };

      axios
        .post("api/products", data, me.$root.config)
        .then(function () {
          $("#productModal").modal("hide");
          me.CloseModal();
          me.$emit("list-products");
        })
        .catch((response) => {
          me.assignErrors(response);
        });
    },
    EditProduct() {
      let me = this;
      me.assignErrors(false);

      var data = {
        product: me.formProduct,
        itemListKit: me.listItemsKit,
      };

      axios
        .put("api/products/" + me.formProduct.id, data, me.$root.config)
        .then(function () {
          $("#productModal").modal("hide");
          me.CloseModal();
          me.edit = false;
          // me.$emit("list-products");
        })
        .catch((response) => {
          me.assignErrors(response);
        });
    },
    ResetData() {
      let me = this;
      $("#productModal").modal("hide");
      Object.keys(this.formProduct).forEach(function (key, index) {
        me.formProduct[key] = "";
      });
      me.listItemsKit = [];
    },
    SaveCategory: function () {
      this.$refs.CreateEditCategory.CreateCategory();
      this.listCategories(1);
    },
    SaveTax: function () {
      this.$refs.CreateEditTax.CreateTax();
    },
    SaveZone: function () {
      this.$refs.CreateEditZone.CreateZone();
    },
    uploadTax(tax_id) {
      let result = 0.0;
      if (tax_id > 0) {
        result = this.taxList.find((tax) => tax.id == tax_id);
        this.tax.percentage = result.percentage;
      }
    },
    CloseModal: function () {
      this.edit = false;
      this.ResetData();
      this.$emit("list-products");
    },
    addProduct(new_product) {
      let me = this;
      let result = false;
      // Verifica si el producto existe en la lista
      me.listItemsKit.filter((prod) => {
        if (new_product.barcode == prod.barcode) {
          result = true;
          if (result) {
            // Añade cantidad
            prod.quantity += 1;
          }
        }
      });

      if (!result) {
        // Sino, lo añade al array
        me.listItemsKit.push({
          product_id: new_product.id,
          barcode: new_product.barcode,
          quantity: 1,
          product: new_product.product,
        });
      }
    },
    removeProduct(index, detail_id = null) {
      this.listItemsKit.splice(index, 1);
      if (detail_id != null) {
        axios.delete(`api/kit-products/${detail_id}`, this.$root.config);
      }
    },
    validatePermission(permission) {
      return global.validatePermission(this.$root.permissions, permission);
    },
    assignErrors(response) {
      const fillable = [
        "barcode",
        "product",
        "type",
        "cost_price_tax_exc",
        "cost_price_tax_inc",
        "gain",
        "sale_price_tax_exc",
        "sale_price_tax_inc",
        "wholesale_price_tax_exc",
        "wholesale_price_tax_inc",
        "stock",
        "quantity",
        "minimum",
        "maximum",
        "state",
        "category_id",
        "zone_id",
        "tax_id",
        "brand_id",
        "expiration_date",
      ];

      if (response) {
        var errors = response.response.data.errors;
        console.log(errors);

        fillable.forEach((index) => {
          if (errors[index] != undefined) {
            this.formErrors[index] = errors[index][0];
          }
        });
      } else {
        fillable.forEach((index) => {
          this.formErrors[index] = "";
        });
      }
    },
  },
  created() {
    this.listTaxes();
    this.listCategories();
    this.listBrands();
    this.listZones();
  },

  mounted() {},
};
</script>
