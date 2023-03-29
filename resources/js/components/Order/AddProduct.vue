<template>
  <div class="modal fade" id="addProductModal" tabindex="-1" aria-labelledby="addProductModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="addProductModalLabel">Productos</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <div class="input-group">
            <input type="text" class="form-control" placeholder="Código de barras | Nombre de product"
              aria-label=" with two button addons" aria-describedby="button-addon4" v-model="filters.product"
              @keyup="searchProduct()" />
            <div class="input-group-append" id="button-addon4">
              <button class="btn btn-outline-secondary" type="button" @click="searchProduct()">
                Buscar Producto
              </button>
            </div>
          </div>
          <section class="table-responsive">
            <table class="table table-sm table-bordered">
              <thead class="thead-primary">
                <tr>
                  <th scope="col">#</th>
                  <th>Código de barras</th>
                  <th scope="col">Producto</th>
                  <th>Categoria</th>
                  <th scope="col">Precio Venta</th>
                  <th scope="col">Cantidad</th>
                  <th>Añadir</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="product in ProductList.data" v-bind:key="product.id">
                  <td>{{ product.id }}</td>
                  <td>{{ product.barcode }}</td>
                  <td>{{ product.product }}</td>
                  <td>
                    {{ product.category.name ? product.category.name : "" }}
                  </td>
                  <td class="text-right">$ {{ product.sale_price_tax_inc }}</td>
                  <td>{{ product.quantity }}</td>
  
                  <td>
                    <button class="btn btn-success" @click="$emit('add-product', product)">
                      <i class="bi bi-plus-circle"></i>
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </section>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">
            Cerrar
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: "add-product",
  props: { 'is_order': 0 },
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
    this.listProducts();
  },
  methods: {
    listProducts() {
      let me = this;
      axios
        .post(
          `api/products/filter-product-list?product=${me.filters.product}&is_order=${this.is_order}`,
          null,
          this.$root.config
        )
        .then(function (response) {
          me.ProductList = response;
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