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
            <input
              type="text"
              class="form-control"
              placeholder="Código de barras | Nombre del producto"
              aria-label=" with two button addons"
              aria-describedby="button-addon4"
              v-model="filters.product"
              @keyup="searchProduct()"
            />
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
                  <th>Categoría</th>
                  <th scope="col">Precio Venta</th>
                  <th scope="col">Cantidad</th>
                  <th>Añadir</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="product in ProductList.data" :key="product.id">
                  <td>{{ product.id }}</td>
                  <td>{{ product.barcode }}</td>
                  <td>{{ product.product }}</td>
                  <td>{{ product.category?.name || "" }}</td>
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
  props: { is_order: 0 },
  data() {
    return {
      // Filtros del modal
      filters: {
        product: "",
      },
      ProductList: {}, // Lista de productos obtenidos
    };
  },
  created() {
    this.listProducts(); // Llama al listado inicial de productos al montar el componente
  },
  methods: {
    listProducts() {
      axios
        .post(
          `api/products/filter-product-list?product=${this.filters.product}&is_order=${this.is_order}&state=1`,
          null,
          this.$root.config
        )
        .then((response) => {
          this.ProductList = { data: response.data }; // Asigna los datos de la API
        })
        .catch((error) => {
          console.error("Error al listar productos:", error);
        });
    },
    searchProduct() {
      if (this.filters.product === "") {
        return false;
      }
      if (this.filters.product.length >= 3) {
        axios
          .post(
            `api/products/filter-product-list?product=${this.filters.product}&state=1`,
            null,
            this.$root.config
          )
          .then((response) => {
            this.ProductList = { data: response.data }; // Asigna los datos de la búsqueda
          })
          .catch((error) => {
            console.error("Error al buscar producto:", error);
            $("#no-results").toast("show");
          });
      }
    },
  },
};
</script>
