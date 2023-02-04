<template>
  <div class="page w-100">
    <div class="page-header">
      <h3>Reporte general de productos vendidos</h3>
    </div>
    <div class="page-search mx-2 my-2 border p-2">
      <div class="form-row">
        <div class="form-group col-md-3">
          <label for="filter_product">Producto</label>
          <input type="text" class="form-control" id="filter_product" v-model="filter.product" />
        </div>
        <div class="form-group col-md-3">
          <label for="filter_category">Categoria</label>
          <select id="filter_category" v-model="filter.category" class="form-control">
              <option value="">Selecciona una categoria</option>
              <option v-for=" (item, index) in categoriesList" :key="index" :value="item.id">{{ item.name }}</option>
          </select>
        </div>
        <div class="form-group col-md-3">
          <label for="from_date">Desde</label>
          <input type="date" class="form-control" id="from_date" v-model="filter.from" />
        </div>
        <div class="form-group col-md-3">
          <label for="to_date">Hasta</label>
          <input type="date" class="form-control" id="to_date" v-model="filter.to" />
        </div>
        <div class="col-md-3  my-4">
          <button class="btn btn-success btn-block" @click="getOrders(1)">
            Buscar <i class="bi bi-search"></i>
          </button>
        </div>
      </div>
    </div>
    <div class="page-content">
      <section class="p-3">
        <table class="table table-sm table-bordered table-hover">
          <thead class=" thead-dark">
            <tr>
              <th>Producto</th>
              <th>Código de barras</th>
              <th>Cantidad productos vendidos</th>
            </tr>
          </thead>
          <tbody v-if="List.length > 0">
            <tr v-for="(l, index) in List" :key="index">
              <td>
                {{ l.product }}
              </td>
              <td>
                <span class="barcode">{{ l.barcode }}</span>
              </td>
              <td>
                {{ l.quantity_of_products }}
              </td>
            </tr>
            <tr>
              <th colspan="2">Total:</th>
              <th>{{ total_products }}</th>
            </tr>
          </tbody>
          <tbody v-else>
            <tr>
              <td colspan="3">
                No hay resultados
              </td>
            </tr>
          </tbody>
        </table>
      </section>
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      List: [],
      filter: {
        from: "",
        to: "",
        product: "",
        category: ""
      },
      categoriesList: []
    };
  },
  computed:{
    total_products : function(){
      return this.List.map(item => item.quantity_of_products).reduce((value1, value2) =>{
        return value1 + value2;
      });
    }
  },
  methods: {
    getOrders(page = 1) {
      let me = this;
      let params = new URLSearchParams(me.filter);
      axios
        .get(
          `api/reports/product-sales-report?page=${page}&${params.toString()}`,
          this.$root.config
        )
        .then(function (response) {
          me.List = response.data;
        });
    },
    getCategories(){
      let me = this;
      axios.get('api/categories?paginate=0', this.$root.config).then( response =>{
        me.categoriesList = response.data.categories;
      });
    }
  },
  mounted() {
    this.getOrders(1);
    this.getCategories();
  }
}
</script>
