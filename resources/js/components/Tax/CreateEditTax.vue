<template>
  <div class="container">
    <div class="row justify-content-center">
      <form>
        <!-- Select para elegir el tributo de la tabla product_tributes -->
        <div class="form-group">
          <label for="tributeSelect">Seleccione Impuesto</label>
          <select
            class="form-control"
            id="tributeSelect"
            v-model="selectedTributeId"
            @change="onTributeSelectChange"
          >
            <option value="" disabled>-- Seleccionar Impuesto --</option>
            <!-- Mostramos code, name y description en la misma opción -->
            <option
              v-for="tribute in productTributes"
              :key="tribute.id"
              :value="tribute.id"
            >
              {{ tribute.code }} - {{ tribute.name }} ({{ tribute.description }})
            </option>
          </select>
        </div>

        <!-- Campo para CODE del impuesto -->
        <div class="form-group">
          <label for="code">Código</label>
          <input
            type="text"
            class="form-control"
            id="code"
            v-model="formTax.code"
            required
          />
          <small class="form-text text-danger">{{ formErrors.code }}</small>
        </div>

        <!-- Campo para NAME del impuesto -->
        <div class="form-group">
          <label for="name">Nombre Impuesto</label>
          <input
            type="text"
            class="form-control"
            id="name"
            v-model="formTax.name"
            required
          />
          <small class="form-text text-danger">{{ formErrors.name }}</small>
        </div>

        <!-- Campo para PERCENTAGE -->
        <div class="form-group">
          <label for="percentage">Porcentaje</label>
          <input
            type="number"
            class="form-control"
            id="percentage"
            v-model="formTax.percentage"
            required
          />
          <small class="form-text text-danger">{{ formErrors.percentage }}</small>
        </div>

        <!-- Campo para DESCRIPTION -->
        <div class="form-group">
          <label for="description">Descripción</label>
          <textarea
            class="form-control"
            id="description"
            placeholder="Ingrese descripción"
            v-model="formTax.description"
          ></textarea>
          <small class="form-text text-danger">{{ formErrors.description }}</small>
        </div>
      </form>
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      // Datos que se enviarán al endpoint /api/taxes
      formTax: {
        id: null,        // Se usa si editamos un Tax existente
        code: "",        // Código del impuesto (llenado automáticamente o manual)
        name: "",        // Nombre del impuesto
        percentage: 0,   // Porcentaje
        description: ""  // Descripción
      },
      formErrors: {
        code: "",
        name: "",
        percentage: "",
        description: ""
      },
      // Lista de tributos que obtenemos de /api/product-tributes
      productTributes: [],
      // ID del tributo seleccionado en el <select>
      selectedTributeId: ""
    };
  },
  methods: {
    /**
     * Obtiene la lista de tributos desde /api/product-tributes
     * Asegúrate de que esta ruta no requiera autenticación
     * o de enviar el token en caso contrario.
     */
    fetchProductTributes() {
      axios
        .get("api/product-tributes") // Si está fuera de auth:api, no necesita token
        .then(response => {
          // Asumimos que la respuesta viene como { "data": [...] }
          if (response.data && response.data.data) {
            this.productTributes = response.data.data;
          }
        })
        .catch(error => {
          console.error("Error fetching product tributes", error);
        });
    },

    /**
     * Cuando el usuario selecciona un tributo en el <select>,
     * buscamos ese objeto en productTributes y llenamos los campos del Tax.
     */
    onTributeSelectChange() {
      const tribute = this.productTributes.find(
        t => t.id === this.selectedTributeId
      );
      if (tribute) {
        // Llenamos formTax con los datos del tributo
        this.formTax.code = tribute.code; // El code del tributo
        this.formTax.name = tribute.name; // Nombre del tributo
        // Asignamos un porcentaje si es "IVA"
        this.formTax.percentage = tribute.name.toLowerCase().includes("iva")
          ? 19
          : 0;
        this.formTax.description = tribute.description || "";
      }
    },

    /**
     * Crea un nuevo impuesto llamando a POST /api/taxes
     */
    CreateTax() {
      this.assignErrors(false);
      axios
        .post("api/taxes", this.formTax, this.$root.config)
        .then(() => {
          // Cerramos el modal
          $("#taxModal").modal("hide");
          // Reseteamos el formulario
          this.ResetData();
          // Disparamos el evento para recargar la lista
          this.$emit("list-taxes");
        })
        .catch(error => {
          this.assignErrors(error);
        });
    },

    /**
     * Abre el formulario para editar un impuesto existente
     */
    OpenEditTax(tax) {
      $("#taxModal").modal("show");
      // Clonamos el objeto tax para llenar formTax
      this.formTax = { ...tax };
      // Como tributoId no existe directamente en tax, lo dejamos vacío (o podrías buscar si coincide)
      this.selectedTributeId = "";
    },

    /**
     * Actualiza un impuesto existente con PUT /api/taxes/{id}
     */
    EditTax() {
      this.assignErrors(false);
      axios
        .put("api/taxes/" + this.formTax.id, this.formTax, this.$root.config)
        .then(() => {
          $("#taxModal").modal("hide");
          this.ResetData();
          this.$emit("list-taxes");
        })
        .catch(error => {
          this.assignErrors(error);
        });
    },

    /**
     * Limpia los campos y cierra el modal
     */
    ResetData() {
      $("#taxModal").modal("hide");
      this.formTax = {
        id: null,
        code: "",
        name: "",
        percentage: 0,
        description: ""
      };
      this.selectedTributeId = "";
      this.assignErrors(false);
    },

    /**
     * Asigna los errores de validación al formulario (si los hay)
     */
    assignErrors(error) {
      if (error && error.response && error.response.data.errors) {
        const errs = error.response.data.errors;
        this.formErrors.code = errs.code ? errs.code[0] : "";
        this.formErrors.name = errs.name ? errs.name[0] : "";
        this.formErrors.percentage = errs.percentage ? errs.percentage[0] : "";
        this.formErrors.description = errs.description ? errs.description[0] : "";
      } else {
        this.formErrors = {
          code: "",
          name: "",
          percentage: "",
          description: ""
        };
      }
    }
  },
  mounted() {
    this.fetchProductTributes();
  }
};
</script>

<style scoped>
.container {
  padding: 20px;
}
</style>
