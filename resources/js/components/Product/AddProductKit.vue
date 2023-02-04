<template>
  <div>
    <div class="border bg-white p-2">
      <div class="">
        <div class="form-group">
          <label for="">Buscar...</label>
          <input
            type="text"
            class="form-control"
            placeholder="Código de barras | Nombre de producto"
            aria-label=" with two button addons"
            aria-describedby="button-addon4"
            v-model="filters.product"
            @keyup="listProducts()"
          />
        </div>
        <table class="table table-sm table-bordered table-responsive-sm">
          <tbody>
            <tr v-for="product in ProductList.data" v-bind:key="product.id">
              <td>{{ product.barcode }}</td>
              <td>{{ product.product }}</td>
              <td>{{ product.quantity }}</td>
              <td>
                <button
                  class="btn btn-success btn-sm"
                  type="button"
                  @click="$emit('add-product', product)"
                >
                  <i class="bi bi-plus-circle"></i>
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: "add-product-kit",
  data() {
    return {
      // Filter modal
      filters: {
        product: "",
      },
      ProductList: {},
    };
  },

  created() {
    // this.listProducts();
  },
  methods: {
    listProducts() {
      let me = this;
      axios
        .post(
          `api/products/filter-product-list?product=${me.filters.product}`,
          null,
          this.$root.config
        )
        .then(function (response) {
          me.ProductList = response;
        })
        .catch(function (error) {
          $("#no-results").toast("show");

          console.log(error);
        });
    },
    searchProduct() {
      let me = this;
      if (me.filters.product == "") {
        return false;
      }
      var url =
        "api/products/filter-product-list?product=" + me.filters.product;
      if (me.filters.product.length >= 3) {
        axios
          .post(url, null, me.$root.config)
          .then(function (response) {
            me.ProductList = response;
          })
          .catch(function (error) {
            $("#no-results").toast("show");

            console.log(error);
          });
      }
    },
  },
};
</script>