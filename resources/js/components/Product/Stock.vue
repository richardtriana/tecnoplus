<template>
  <div class="w-100">
    <div class="page-header text-center mb-2">
      <h3 class="">Inventario</h3>
    </div>
    <moon-loader
      class="m-auto"
      :loading="isLoading"
      :color="'#032F6C'"
      :size="100"
    />
    <div class="page-search mx-2" v-if="!isLoading">
      <div class="form-row">
        <div class="form-group col-md-3">
          <label for="search_product">Nombre de producto</label>
          <input
            type="text"
            class="form-control"
            id="search_product"
            placeholder="Nombre  de producto"
            v-model="search_product"
            autofocus
            @keyup="listProducts(1)"
          />
        </div>
        <div class="form-group col-md-3">
          <label for="search_barcode">Código de producto</label>
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
        <div class="form-group col-md-3">
          <label for="category">Categoría</label>
          <v-select
            :options="categoryList"
            label="name"
            :reduce="(category) => category.id"
            v-model="search_category"
          />
        </div>
        <div class="form-group col-md-3">
          <label for="brand">Marca</label>
          <v-select
            :options="brandList"
            label="name"
            :reduce="(brand) => brand.id"
            v-model="search_brand"
          />
        </div>
        <div class="form-group col-3">
          <label for="search_quantity_sign">Cantidad:</label>
          <select
            name="search_quantity_sign"
            class="custom-select"
            v-model="search_quantity_sign"
            id="search_quantity_sign"
          >
            <option value=">">Mayor que</option>
            <option value="<">Menor que</option>
            <option value="=">Igual a</option>
          </select>
        </div>
        <div class="form-group col-3">
          <label for="quantity">Valor de Cantidad:</label>
          <input
            type="number"
            step="any"
            class="form-control"
            id="search_quantity"
            placeholder="Cantidad"
            v-model="search_quantity"
          />
        </div>
        <div class="form-group col-3">
          <label for="expiration_date_from">Fecha de expiración desde:</label>
          <input
            type="date"
            step="any"
            class="form-control"
            id="search_expiration_date_from"
            placeholder="Fecha de vencimiento"
            v-model="search_expiration_date_from"
          />
        </div>
        <div class="form-group col-3">
          <label for="expiration_date_to">Fecha de expiración hasta:</label>
          <input
            type="date"
            step="any"
            class="form-control"
            id="search_expiration_date_to"
            placeholder="Fecha de vencimiento"
            v-model="search_expiration_date_to"
          />
        </div>
        <div class="col-3 offset-6">
          <button class="btn btn-success btn-block" @click="listProducts(1)">
            Buscar <i class="bi bi-search"></i>
          </button>
        </div>
      </div>
    </div>
    <div class="page-content mx-2">
      <section class="mt-4">
        <table class="table table-sm table-bordered table-responsive-sm">
          <thead class="thead-primary">
            <tr>
              <th>Código de barras</th>
              <th scope="col">Producto</th>
              <th>Categoria</th>
              <th>Marca</th>
              <th scope="col">Cantidad</th>
              <th>Fecha de vencimiento</th>
              <td v-if="$root.validatePermission('product.update')">Editar</td>
            </tr>
          </thead>
          <tbody>
            <tr v-for="product in ProductList.data" v-bind:key="product.id">
              <td>{{ product.barcode }}</td>
              <td>{{ product.product }}</td>
              <td>{{ product.category.name }}</td>
              <td>
                <span v-if="product.brand">
                  {{ product.brand.name }}
                </span>
              </td>
              <td>{{ product.quantity }}</td>
              <td>
                <div v-if="product.expiration_date">
                  <span
                    v-if="product.expiration_date <= now"
                    class="badge badge-pill badge-danger"
                  >
                    {{ product.expiration_date }}
                  </span>
                  <span
                    v-else-if="
                      product.alert_expiration_date > now &&
                      product.expiration_date < product.alert_expiration_date
                    "
                    class="badge badge-pill badge-warning"
                    >{{ product.expiration_date }}</span
                  >
                  <span
                    v-else-if="product.alert_expiration_date > now"
                    class="badge badge-pill badge-success"
                    >{{ product.expiration_date }}</span
                  >
                </div>
              </td>
              <td v-if="$root.validatePermission('product.update')">
                <div class="input-group mb-3">
                  <input
                    type="number"
                    class="form-control"
                    placeholder="Cantidad de productos"
                    aria-label="Product quantity"
                    aria-describedby="button-qty"
                    value="0"
                    v-model="product.qty"
                  />
                  <div class="input-group-append">
                    <button
                      class="btn btn-outline-success"
                      type="button"
                      id="button-qty"
                      v-if="product.qty && product.qty != 0"
                      @click="updateStock(product.id, product.qty)"
                    >
                      <i class="bi bi-check2-circle"></i>
                    </button>
                  </div>
                </div>
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
          <span slot="next-nav"
            ><i class="bi bi-chevron-double-right"></i
          ></span>
        </pagination>
      </section>
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      search_product: "",
      search_barcode : "",
      search_category: 0,
      search_brand: 0,
      search_quantity_sign: ">",
      search_quantity: 0,
      search_expiration_date_from: "",
      search_expiration_date_to: "",
      isLoading: false,
      ProductList: {},
      categoryList: [],
      brandList: [],
      now: new Date().toISOString().slice(0, 10),
    };
  },
  created() {
    this.isLoading = true;
    let me = this;
    axios
      .get(`api/products?page=1`, this.$root.config)
      .then(function (response) {
        me.ProductList = response.data.products;
      })
      .finally(() => (this.isLoading = false));
  },
  methods: {
    listProducts(page = 1) {
      let me = this;

      let data = {
        page: page,
        product: me.search_product,
        barcode: me.search_barcode,
        category_id: me.search_category,
        brand_id: me.search_brand,
        quantity_sign: me.search_quantity_sign,
        quantity: me.search_quantity,
        expiration_date_from: me.search_expiration_date_from,
        expiration_date_to: me.search_expiration_date_to,
      }
      axios
        .get(
          `api/products`,
          {
            params: data,
            headers: this.$root.config.headers
          }
        )
        .then(function (response) {
          me.ProductList = response.data.products;
        });
    },
    listCategories() {
      let me = this;
      axios
        .get(`api/categories/category-list`, this.$root.config)
        .then(function (response) {
          me.categoryList = response.data.categories;
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
    updateStock(id, quantity) {
      let me = this;
      axios
        .post(
          `api/products/stock-update/${id}`,
          { quantity: quantity },
          this.$root.config
        )
        .then(function (response) {
          me.listProducts(1);
        });
    },
  },
  mounted() {
    this.listCategories();
    this.listBrands();
  },
};
</script>

<style>
</style>
