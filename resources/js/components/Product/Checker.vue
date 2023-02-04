<template>
  <div class="bg-primary w-100 text-center text-white vh-100"
    style="justify-content: center; display: flex; align-items: center">
    <div class="p-5 w-100">
      <!-- <h1 class="">Consulte su producto</h1> -->
      <div class="form-group w-100">
        <label for="filter" style="font-size: 2rem">Buscar</label>
        <input id="filter" type="text" class="form-control form-control-lg p-5" placeholder="Código de barras"
          aria-label=" with two button addons" aria-describedby="button-add-product" v-model="filters.product" autofocus
          @keyup="searchProduct()" />
        <span class="barcode display-1" v-if="!not_found">{{ product.barcode }}</span>
        <h1 v-if="!not_found">{{ product.product }}</h1>
        <br />
        <h1 class="my-3" v-if="product.sale_price_tax_inc && !not_found">
          ${{ product.sale_price_tax_inc }}
        </h1>
        <div class="text-center mt-2">
          <span v-if="not_found" class="text-center text-danger p-4 h4 bg-white w-50">
            Producto no encontrado
          </span>
        </div>
        <!-- <p>Nombre de producto</p> -->
      </div>
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      filters: {
        product: "",
      },
      product: {},
      not_found: false,
    };
  },
  methods: {
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
            me.not_found = true;
            me.filters.product = ""
          } else {
            me.product = response.data.products;
            me.not_found = false;
          }
        })
        .catch(function (error) {
          console.log(error);
        });
      setTimeout((me.filters.product = ""), 50000)
    },
  },
};
</script>

<style>
</style>
