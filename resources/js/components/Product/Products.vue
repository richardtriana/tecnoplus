<template>
  <div>
    <div class="col-12">
      <div class="page-header">
        <div>
          <h3>Productos</h3>
        </div>
        <div class="row">
          <div class="col-sm-6 col-md-4 col-lg-3">
            <div class="card border border-primary p-2">
              <h6 class="text-uppercase text-secondary">Nro. de Productos</h6>
              <h2>{{ TotalProductsList.number_of_products }}</h2>
            </div>
          </div>
          <div class="col-sm-6 col-md-4 col-lg-3">
            <div class="card border border-primary p-2">
              <h6 class="text-uppercase text-secondary">
                Cant. total de Productos
              </h6>
              <h2>{{ TotalProductsList.quantity_of_products }}</h2>
            </div>
          </div>
          <div class="col-sm-6 col-md-4 col-lg-3">
            <div class="card border border-primary p-2">
              <h6 class="text-uppercase text-secondary">Valor de stock</h6>
              <h2>{{ TotalProductsList.cost_stock | currency }}</h2>
            </div>
          </div>
          <div class="col-sm-6 col-md-4 col-lg-3">
            <div class="card border border-primary p-2">
              <h6 class="text-uppercase text-secondary">Valor de venta</h6>
              <h2>{{ TotalProductsList.cost_sale | currency }}</h2>
            </div>
          </div>
        </div>
      </div>

      <div class="card-body">
        <div class="form-row">
          <div class="form-group col-3">
            <label for="product">Nombre de producto</label>
            <input
              type="text"
              class="form-control"
              id="search_product"
              placeholder="Nombre de producto"
              v-model="search_product"
              autofocus
              @keyup="listProducts(1)"
            />
          </div>
          <div class="form-group col-3">
            <label for="barcode">Código de barras</label>
            <input
              type="text"
              class="form-control"
              id="search_barcode"
              placeholder="Código de barras"
              v-model="search_barcode"
              autofocus
              @keyup="listProducts(1)"
            />
          </div>
          <div class="form-group col-3">
            <label for="category">Categoría</label>
            <v-select
              :options="categoryList"
              label="name"
              :reduce="(category) => category.id"
              v-model="search_category"
            />
          </div>
          <div class="form-group col-3">
            <label for="state">Estado:</label>
            <select class="custom-select" name="state" id="state" v-model="search_state">
              <option value="all">Todos</option>
              <option value="0">Inactivos</option>
              <option value="1">Activos</option>
            </select>
          </div>
          <div class="form-group col-3">
            <label for="no_results">Mostrar {{search_no_results}} resultados por página:</label>
            <input
              type="number"
              step="any"
              class="form-control"
              id="search_no_results"
              placeholder="Cantidad"
              v-model="search_no_results"
            />
          </div>
          <div class="form-group col-3 offset-6">
            <button class="btn btn-success btn-block" @click="listProducts(1)">
              Buscar <i class="bi bi-search"></i>
            </button>
          </div>
        </div>
        <div
          class="row justify-content-end"
          v-if="$root.validatePermission('product.store')"
        >
          <download-excel
            class="btn btn-outline-success mr-2"
            :fields="json_fields"
            :data="ProductList.data"
            name="product-list.xls"
            type="xls"
          >
            <i class="bi bi-file-earmark-arrow-down-fill"></i> Exportar selección
          </download-excel>
          <button
            type="button"
            class="btn btn-outline-primary mr-2"
            data-toggle="modal"
            data-target="#productImportModal"
            v-if="$root.validatePermission('product.store')"
          >
            <i class="bi bi-cloud-arrow-up-fill"></i>
            Importar Productos
          </button>
          <button
            type="button"
            class="btn btn-outline-primary"
            data-toggle="modal"
            data-target="#productModal"
            v-if="$root.validatePermission('product.store')"
          >
            <i class="bi bi-plus-circle-dotted"></i>
            Crear Producto
          </button>
        </div>
        
        <moon-loader
          :loading="isLoading"
          class="m-auto"
          :color="'#032F6C'"
          :size="100"
        />

        <section class="mt-4" v-if="!isLoading">
          <table class="table table-sm table-bordered table-responsive-sm">
            <thead class="thead-primary">
              <tr>
                <th>Código de barras</th>
                <th scope="col">Producto</th>
                <th>Categoría</th>
                <th>Unidad</th>
                <th scope="col">Precio Venta con IVA</th>
                <th scope="col">Cantidad</th>
                <th scope="col">Ganancia</th>
                <th v-if="$root.validatePermission('product.active')">Estado</th>
                <th v-if="$root.validatePermission('product.update')">Opciones</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="product in ProductList.data" :key="product.id">
                <td class="barcode">{{ product.barcode }}</td>
                <td>{{ product.product }}</td>
                <td>{{ product.category.name }}</td>
                <td>{{ product.measurement_unit ? product.measurement_unit.name : '' }}</td>
                <td class="text-right">
                  {{ product.sale_price_tax_inc | currency }}
                </td>
                <td>{{ product.quantity }}</td>
                <td class="text-right">
                  {{ product.gain | currency }}
                </td>
                <td v-if="$root.validatePermission('product.active')">
                  <button
                    class="btn"
                    :class="product.state == 1 ? 'btn-success' : 'btn-danger'"
                    @click="changeState(product.id)"
                  >
                    <i class="bi bi-check-circle-fill" v-if="product.state == 1"></i>
                    <i class="bi bi-x-circle" v-else></i>
                  </button>
                </td>
                <td v-if="$root.validatePermission('product.update')">
<<<<<<< HEAD
=======
                  <!-- Botón Observaciones -->
                  <button
                    class="btn btn-outline-info mr-1"
                    @click="$refs.obsModal.open(product.id)"
                  >
                    <i class="bi bi-chat-dots"></i>
                  </button>
                  <!-- Botón Editar -->
>>>>>>> 0ed4468 (factuara electronica + reserva + caja)
                  <button class="btn btn-outline-success" @click="ShowData(product)">
                    <i class="bi bi-pen"></i>
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
          <pagination
            :align="'center'"
            :data="ProductList"
            :limit="8"
            @pagination-change-page="listProducts"
          >
            <span slot="prev-nav"><i class="bi bi-chevron-double-left"></i></span>
            <span slot="next-nav"><i class="bi bi-chevron-double-right"></i></span>
          </pagination>
        </section>
      </div>
    </div>
<<<<<<< HEAD
=======

>>>>>>> 0ed4468 (factuara electronica + reserva + caja)
    <!-- Modal para creación y edición de productos -->
    <create-edit-product
      ref="CreateEditProduct"
      @list-products="listProducts(1)"
    />
    <import-products @list-products="listProducts(1)" />

    <!-- Modal Observaciones -->
    <product-observations ref="obsModal" />
  </div>
</template>

<script>
import CreateEditProduct from "./CreateEditProduct.vue";
import ImportProducts from "./ImportProducts.vue";
<<<<<<< HEAD
=======
import ProductObservations from "./ProductObservations.vue";
>>>>>>> 0ed4468 (factuara electronica + reserva + caja)

export default {
  components: {
    CreateEditProduct,
    ImportProducts,
    ProductObservations
  },
  data() {
    return {
      search_product: "",
      search_barcode: "",
      search_category: 0,
      // Se eliminaron search_quantity_sign, search_quantity, search_expiration_date_from y search_expiration_date_to
      search_state: 1,
      search_no_results: 20,
      isLoading: false,
      ProductList: {},
      TotalProductsList: {},
      categoryList: [],
      now: new Date().toISOString().slice(0, 10),
      json_fields: {
        'Código': {
          field: 'barcode',
          callback: (value) => value
        },
        'Producto': {
          field: 'product',
          callback: (value) => value
        },
        'P. Costo': {
          field: 'cost_price_tax_inc',
          callback: (value) => value
        },
        'P. Venta': {
          field: 'sale_price_tax_inc',
          callback: (value) => value
        },
        'Ganancia': {
          field: 'gain',
          callback: (value) => value
        },
        'P. Mayoreo': {
          field: 'wholesale_price_tax_inc',
          callback: (value) => value
        },
        'Existencia': {
          field: 'quantity',
          callback: (value) => value
        },
        'Inventario Máximo': {
          field: 'maximum',
          callback: (value) => value
        },
        'Tipo venta': {
          field: 'type',
          callback: (value) => {
            return value == 1 ? 'UNIDAD' : value == 2 ? 'GRANEL' : value;
          }
        },
        'IVA': {
          field: 'tax.percentage',
          callback: (value) => value
        },
        'Departamento': {
          field: 'category.name',
          callback: (value) => value
        }
      }
    };
  },
  created() {
    this.$root.validateToken();
  },
  methods: {
    listProducts(page = 1) {
      this.isLoading = true;
      let me = this;
      let data = {
        page: page,
        product: me.search_product,
        barcode: me.search_barcode,
        category_id: me.search_category,
        state: me.search_state,
        no_results: me.search_no_results,
      };
      axios
        .get("api/products", {
          params: data,
          headers: this.$root.config.headers
        })
        .then(function (response) {
          me.ProductList = response.data.products;
          me.TotalProductsList = response.data.total_products;
        })
        .finally(() => (this.isLoading = false));
    },
    listCategories() {
      let me = this;
      axios
        .get("api/categories/category-list", this.$root.config)
        .then(function (response) {
          me.categoryList = response.data.categories;
        });
    },
    ShowData(product) {
      this.$refs.CreateEditProduct.OpenEditProduct(product);
    },
    changeState(id) {
      let me = this;
      axios
        .post("api/products/" + id + "/activate", null, this.$root.config)
        .then(function () {
          me.listProducts(1);
        });
    },
  },
  mounted() {
    this.listProducts(1);
    this.listCategories();
  },
};
</script>
