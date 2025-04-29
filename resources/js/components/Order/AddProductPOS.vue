<template>
  <div
    class="modal fade"
    id="addProductModal"
    tabindex="-1"
    aria-labelledby="addProductModalLabel"
    aria-hidden="true"
  >
    <div class="modal-dialog modal-xl" style="max-width: 1200px;">
      <div class="modal-content">

        <!-- Encabezado del modal -->
        <div class="modal-header header-teal">
          <h5 class="modal-title" id="addProductModalLabel">Productos</h5>
          <button
            type="button"
            class="close"
            data-dismiss="modal"
            aria-label="Cerrar"
          >
            <span aria-hidden="true">&times;</span>
          </button>
        </div>

        <!-- Cuerpo del modal -->
        <div class="modal-body">

          <!-- Fila de búsqueda y botón de buscar -->
          <div class="row mb-2">
            <div class="col-md-8">
              <div class="input-group">
                <input
                  type="text"
                  class="form-control"
                  placeholder="Código de barras | Nombre del Producto"
                  v-model="filters.product"
                  @input="onSearchInput"
                />
                <div class="input-group-append">
                  <button class="btn btn-info" @click="searchProduct">
                    Buscar producto
                  </button>
                </div>
              </div>
            </div>
            <!-- Espacio para botones de paginación o flechas (opcional) -->
            <div class="col-md-4 text-right">
              <button class="btn btn-sm btn-outline-info" @click="goBack">
                <i class="bi bi-arrow-left"></i>
              </button>
              <button class="btn btn-sm btn-outline-info" @click="goNext">
                <i class="bi bi-arrow-right"></i>
              </button>
            </div>
          </div>

          <!-- Botones de categorías -->
          <div class="mb-3">
            <!-- Botón "Todas" -->
            <button
              class="btn btn-sm"
              :class="selectedCategory === null ? 'btn-info' : 'btn-outline-info'"
              @click="filterByCategory(null)"
            >
              Todas
            </button>

            <!-- Botones de categoría (solo las que tienen productos) -->
            <button
              v-for="cat in categoriesWithProducts"
              :key="cat.id"
              class="btn btn-sm m-1"
              :class="selectedCategory === cat.id ? 'btn-info' : 'btn-outline-info'"
              @click="filterByCategory(cat.id)"
            >
              {{ cat.name }}
            </button>
          </div>

          <!-- Grid de productos -->
          <div class="row">
            <div
              v-for="product in ProductList"
              :key="product.id"
              class="col-6 col-sm-4 col-md-3 col-lg-2 mb-3 d-flex"
            >
              <div
                class="product-card"
                @click="emitAddProduct(product)"
              >
                <h6 class="product-name">{{ product.product }}</h6>
                <small class="product-barcode">{{ product.barcode }}</small>
                <p class="product-price">
                  $ {{ formatPrice(product.sale_price_tax_inc) }}
                </p>
              </div>
            </div>

            <!-- Mensaje si no hay productos -->
            <div v-if="ProductList.length === 0" class="col-12 text-center text-muted">
              No se encontraron productos
            </div>
          </div>

        </div>

        <!-- Footer del modal con versión y botón cerrar -->
        <div class="modal-footer" style="justify-content: space-between;">
          <span>Restaplus Ver 2.1</span>
          <button
            type="button"
            class="btn btn-secondary"
            data-dismiss="modal"
          >
            Cerrar
          </button>
        </div>

      </div>
    </div>
  </div>
</template>

<script>
import axios from "axios";
// Si deseas un "debounce" para no disparar demasiadas peticiones, puedes usar lodash:
// import debounce from "lodash/debounce";

export default {
  name: "AddProductPOS",
  data() {
    return {
      filters: {
        product: "",
      },
      ProductList: [],
      categoryList: null,       // Puede arrancar en null
      selectedCategory: null,
      // Para debounce (opcional):
      // debouncedSearch: null
    };
  },
  computed: {
    // Retorna solo las categorías que tienen productos en la lista actual
    categoriesWithProducts() {
      // Si categoryList no es un array, devolvemos un array vacío
      if (!Array.isArray(this.categoryList)) {
        return [];
      }
      // Obtenemos los ID de categoría que aparecen en ProductList
      const productCatIds = new Set(this.ProductList.map((p) => p.category_id));
      // Filtramos categoryList para dejar solo las categorías que aparecen en productCatIds
      return this.categoryList.filter((cat) => productCatIds.has(cat.id));
    },
  },
  mounted() {
    this.listCategories();
    this.listProducts();

    // Ejemplo con debounce (opcional):
    // this.debouncedSearch = debounce(this.listProducts, 300);
  },
  methods: {
    // Formatea el precio con separador de miles
    formatPrice(value) {
      if (!value) return "0";
      const num = parseFloat(value);
      return num.toLocaleString();
    },

    listCategories() {
      axios
        .get("api/categories", this.$root.config)
        .then((resp) => {
          console.log("Respuesta de categorías:", resp.data);

          // Verificamos si resp.data es un array o un objeto con 'categories'
          if (Array.isArray(resp.data)) {
            // El backend retorna un array directo: [ {id:..., name:...}, ... ]
            this.categoryList = resp.data;
          } else if (resp.data && Array.isArray(resp.data.categories)) {
            // El backend retorna { categories: [ {id:..., name:...}, ... ] }
            this.categoryList = resp.data.categories;
          } else {
            // Si no es ni una cosa ni la otra, asignamos un array vacío
            console.warn("La respuesta de categorías no tiene el formato esperado.");
            this.categoryList = [];
          }
        })
        .catch((err) => {
          console.error("Error al listar categorías:", err);
          this.categoryList = [];
        });
    },

    listProducts() {
      let url = `api/products/filter?state=1`;
      if (this.selectedCategory) {
        url += `&category_id=${this.selectedCategory}`;
      }
      if (this.filters.product) {
        url += `&search=${this.filters.product}`;
      }
      axios
        .get(url, this.$root.config)
        .then((resp) => {
          console.log("Productos recibidos:", resp.data);
          // Asumiendo que resp.data es un array de productos
          this.ProductList = Array.isArray(resp.data) ? resp.data : [];
        })
        .catch((err) => {
          console.error("Error al listar productos:", err);
          this.ProductList = [];
        });
    },

    // Se llama en cada tecla presionada
    onSearchInput() {
      // Sin debounce: llama directamente
      this.listProducts();

      // Con debounce, usarías:
      // this.debouncedSearch();
    },

    // Botón “Buscar producto”
    searchProduct() {
      this.listProducts();
    },

    filterByCategory(catId) {
      this.selectedCategory = catId;
      this.listProducts();
    },

    emitAddProduct(product) {
      // Emite al padre para agregar el producto al pedido
      this.$emit("add-product", product);
    },

    // Métodos de paginación (opcional)
    goBack() {
      console.log("Retroceder página (ejemplo)");
    },

    goNext() {
      console.log("Avanzar página (ejemplo)");
    },
  },
};
</script>

<style scoped>
/* Encabezado teal */
.header-teal {
  background-color: #3c7b8f;
  color: #fff;
}

/* Tarjeta de producto */
.product-card {
  /* Borde menos grueso y color más suave */
  border: 1px solid #3c7b8f;
  border-radius: 8px;
  
  /* Agrega un poco más de relleno */
  padding: 0.75rem;
  
  /* Transición suave para el hover */
  transition: all 0.15s ease-in-out;
  
  /* Ajustes de tamaño y flex para centrar */
  width: 100%;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  
  /* Fondo claro para resaltar */
  background-color: #fdfdfd;
  cursor: pointer;
}

/* Efecto hover */
.product-card:hover {
  transform: translateY(-3px);
  box-shadow: 0 3px 8px rgba(0, 0, 0, 0.15);
  border-color: #2c5b67; /* Un poco más oscuro al hacer hover */
}

/* Título del producto */
.product-name {
  font-size: 1rem;
  font-weight: 600;
  color: #333;
  margin-bottom: 0.3rem;
  text-align: center;
}

/* Código de barras */
.product-barcode {
  color: #777;
  font-size: 0.85rem;
  margin-bottom: 0.5rem;
  text-align: center;
}

/* Precio */
.product-price {
  color: #7a1bfa;
  font-size: 1.2rem;
  font-weight: bold;
  margin: 0;
  text-align: center;
}

.product-card:hover {
  transform: scale(1.05);
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.15);
}

/* Texto dentro de la tarjeta */
.product-name {
  font-size: 1rem;
  font-weight: 600;
  color: #333;
}
.product-barcode {
  color: #777;
  font-size: 0.85rem;
  margin-bottom: 0.5rem;
}
.product-price {
  color: #7a1bfa;
  font-size: 1.2rem;
  font-weight: bold;
}

/* Botones “activos” de categoría */
.btn-info.active,
.btn-info:hover,
.btn-info:focus {
  background-color: #2c5b67 !important;
}
</style>
