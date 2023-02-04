<template>
  <div class="container">
    <div class="modal fade" id="supplierModal" tabindex="-1" aria-labelledby="supplierModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="supplierModalLabel">Proveedor</h5>
            <button type="reset" class="close" @click="closeModal()">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <form id="formSupplier">
              <div class="form-row">
                <div class="input-group mb-3">
                  <div class="input-group-prepend">
                    <select class="custom-select" id="type_documento" v-model="formSupplier.type_document">
                      <option selected disabled value="">Documento...</option>
                      <option value="CC">Cédula de ciudadania</option>
                      <option value="CE">Cédula de extranjería</option>
                      <option value="NIT">NIT</option>
                    </select>
                  </div>
                  <input type="text" class="form-control" aria-label="Text input with dropdown button"
                    v-model="formSupplier.document" placeholder="Ingresar documento de identificación" />
                </div>

                <div class="form-group col-6">
                  <label for="name">Nombre / Razon social</label>
                  <input type="text" class="form-control" id="name" placeholder="Ingresar nombre o razón social"
                    name="name" v-model="formSupplier.name" />
                </div>
              </div>
              <div class="form-row">
                <div class="col-md-7 border-right border-gray">
                  <div class="form-group">
                    <label for="address">Direccion</label>
                    <input type="text" class="form-control" id="address" placeholder="Ingresar dirección" name="address"
                      v-model="formSupplier.address" />
                  </div>

                  <div class="form-group">
                    <label for="mobile">Celular</label>
                    <input type="text" class="form-control" id="mobile" placeholder="Ingresar celular" name="mobile"
                      v-model="formSupplier.mobile" />
                  </div>
                  <div class="form-row">
                    <span class="col-12">Contacto</span>
                    <div class="form-group">
                      <input type="text" class="form-control" id="contact" placeholder="Ingresar contacto"
                        name="contact" v-model="formSupplier.contact" />
                    </div>
                  </div>
                  <div class="form-group">
                    <label for="email">Correo electronico</label>
                    <input type="enail" class="form-control" id="email" placeholder="Ingresar email" name="email"
                      v-model="formSupplier.email" />
                  </div>

                  <div class="form-row">
                    <div class="form-group">
                      <label for="type_person">Tipo</label>
                      <select class="form-control" id="type_person" name="type_person"
                        v-model="formSupplier.type_person">
                        <option value="" disabled>Seleccionar tipo</option>
                        <option>Juridica</option>
                        <option>Natural</option>
                      </select>
                    </div>
                  </div>
                </div>
                <div class="col-md-5">
                  <div class="form-group">
                    <label for="departament">Departamento</label>
                    <select class="form-control" id="departament" name="departament" v-model="formSupplier.departament"
                      @change="getMunicipalities(formSupplier.departament)">
                      <option value="" disabled> Selecciona un departamento</option>
                      <option v-for="department in departments" :value="department.id" :key="department.id"> {{
                          department.name
                      }} </option>
                    </select>
                  </div>
                  <div class="form-group">
                    <label for="city">Municipio</label>
                    <select class="form-control" id="city" name="city" v-model="formSupplier.municipality_id">
                      <option value="" disabled> Selecciona un municipio</option>
                      <option v-for="municipality in municipalities" :value="municipality.id" :key="municipality.id"> {{
                          municipality.name
                      }} </option>
                    </select>
                  </div>

                  <div class="form-group">
                    <div class="form-check">
                      <input class="form-check-input" type="checkbox" value="1" id="active"
                        v-model="formSupplier.active" />
                      <label class="form-check-label" for="active">
                        ¿Proveedor está activo?
                      </label>
                    </div>
                    <div class="form-check">
                      <input class="form-check-input" type="checkbox" value="0" id="tax"
                        v-model="formSupplier.tax" />
                      <label class="form-check-label" for="tax">
                        Impuesto Incluido
                      </label>
                    </div>
                  </div>
                </div>
              </div>
            </form>
          </div>
          <div class="modal-footer">
            <button class="btn btn-secondary" type="reset" @click="closeModal()">
              Cerrar
            </button>
            <button type="button" class="btn btn-primary" @click="formSupplier.id ? EditSupplier() : CreateSupplier()">
              Guardar
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      formSupplier: {
        name: "",
        address: "",
        mobile: "",
        fax: "",
        contact: "",
        email: "",
        type_person: "",
        departament: "",
        municipality_id: "",
        type_document: "",
        document: "",
        active: "1",
        tax: false,
      },
      departments: [],
      municipalities: []
    };
  },
  created() {
    this.getDepartments();
  },
  methods: {
    CreateSupplier() {
      let me = this;
      axios
        .post("api/suppliers", this.formSupplier, this.$root.config)
        .then(function () {
          $("#supplierModal").modal("hide");
          me.ResetData();
        });
    },
    OpenEditSupplier(supplier) {
      let me = this;
      $("#supplierModal").modal("show");
      me.formSupplier = supplier;

      if (supplier.municipality) {
        me.formSupplier.departament = supplier.municipality.department_id;
        this.getMunicipalities(me.formSupplier.departament);
      } else {
        me.formSupplier.departament = "";
        me.formSupplier.municipality_id = "";
      }

    },

    EditSupplier() {
      let me = this;
      axios
        .put(
          "api/suppliers/" + this.formSupplier.id,
          this.formSupplier,
          this.$root.config
        )
        .then(function () {
          $("#supplierModal").modal("hide");
          me.ResetData();
        });
    },
    ResetData() {
      let me = this;
      $("#supplierModal").modal("hide");
      //$("#formSupplier")[0].reset();
      Object.keys(this.formSupplier).forEach(function (key, index) {
        me.formSupplier[key] = "";
      });
      this.$emit("list-suppliers");
    },

    closeModal: function () {
      let me = this;
      this.ResetData();
    },

    getDepartments() {
      axios.get('api/departments', this.$root.config).then((response) => {
        this.departments = response.data.departments;
      });
    },
    getMunicipalities(department) {
      axios.get('api/departments/' + department + '/getMunicipalities', this.$root.config).then((response) => {
        this.municipalities = response.data.municipalities;
      });
    }
  },
  mounted() { },
};
</script>
