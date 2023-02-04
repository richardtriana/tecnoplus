<template>
  <div class="container">
    <div
      class="modal fade"
      id="clientModal"
      tabindex="-1"
      aria-labelledby="clientModalLabel"
      aria-hidden="true"
    >
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="clientModalLabel">Client</h5>
            <button
              type="button"
              class="close"
              @click="closeModal()"
            >
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <form>
            <div class="modal-body">
              <div class="form-row">
                <div class="input-group mb-3">
                  <div class="input-group-prepend">
                    <select
                      class="custom-select"
                      id="type_documento"
                      v-model="formClient.type_document"
                      required
                      autocomplete="address"
                    >
                      <option selected disabled value="">Documento...</option>
                      <option value="TI">Tarjeta de identidad</option>
                      <option value="CC">Cédula de ciudadania</option>
                      <option value="CE">Cédula de extranjería</option>
                      <option value="NIT">NIT</option>
                    </select>
                  </div>
                  <input
                    type="text"
                    class="form-control"
                    aria-label="Text input with dropdown button"
                    v-model="formClient.document"
                    placeholder = "Ingresar documento de identificación"
                  />
                </div>

                <div class="form-group col-6">
                  <label for="name">Nombre / Razon social</label>
                  <input
                    type="text"
                    class="form-control"
                    id="name"
                    placeholder="Ingresar nombre o razón social"
                    name="name"
                    v-model="formClient.name"
                  />
                </div>
              </div>
              <div class="form-row">
                <div class="col-md-7 border-right border-gray">
                  <div class="form-group">
                    <label for="address">Direccion</label>
                    <input
                      type="text"
                      class="form-control"
                      id="address"
                      placeholder="Ingresar dirección"
                      name="address"
                      v-model="formClient.address"
                    />
                  </div>

                  <div class="form-group">
                    <label for="mobile">Celular</label>
                    <input
                      type="text"
                      class="form-control"
                      id="mobile"
                      placeholder="Ingresar celular"
                      name="mobile"
                      v-model="formClient.mobile"
                    />
                  </div>
                  <div class="form-row">
                    <span class="col-12">Contacto</span>
                    <div class="form-group">
                      <input
                        type="text"
                        class="form-control"
                        id="contact"
                        placeholder="Ingresar contacto"
                        name="contact"
                        v-model="formClient.contact"
                      />
                    </div>
                  </div>
                  <div class="form-group">
                    <label for="email">Correo electronico</label>
                    <input
                      type="enail"
                      class="form-control"
                      id="email"
                      placeholder="Ingresar email"
                      name="email"
                      v-model="formClient.email"
                    />
                  </div>

                  <div class="form-row">
                    <div class="form-group">
                      <label for="type_person">Tipo</label>
                      <select
                        class="form-control"
                        id="type_person"
                        name="type_person"
                        v-model="formClient.type_person"
                      >
                        <option value="" disabled>Seleccionar tipo</option>
                        <option value="Juridica">Juridica</option>
                        <option value="Natural">Natural</option>
                      </select>
                    </div>
                  </div>
                </div>
                <div class="col-md-5">
                  <div class="form-group">
                    <label for="departament">Departamento</label>
                    <select
                      class="form-control"
                      id="departament"
                      name="departament"
                      v-model="formClient.departament"
                      @change="getMunicipalities(formClient.departament)"
                    >
                      <option value="" disabled>
                        Selecciona un departamento
                      </option>
                      <option
                        v-for="department in departments"
                        :value="department.id"
                        :key="department.id"
                      >
                        {{ department.name }}
                      </option>
                    </select>
                  </div>
                  <div class="form-group">
                    <label for="city">Municipio</label>
                    <select
                      class="form-control"
                      id="city"
                      name="city"
                      v-model="formClient.municipality_id"
                    >
                      <option value="" disabled>Selecciona un municipio</option>
                      <option
                        v-for="municipality in municipalities"
                        :value="municipality.id"
                        :key="municipality.id"
                      >
                        {{ municipality.name }}
                      </option>
                    </select>
                  </div>

                  <div class="form-group">
                    <div class="form-check">
                      <input
                        class="form-check-input"
                        type="checkbox"
                        value="1"
                        id="active"
                        v-model="formClient.active"
                      />
                      <label class="form-check-label" for="active">
                        Cliente está activo?
                      </label>
                    </div>
                    <div class="form-check">
                      <input
                        class="form-check-input"
                        type="checkbox"
                        value="1"
                        id="impuesto_incluido"
                        v-model="formClient.tax"
                      />
                      <label class="form-check-label" for="impuesto_incluido">
                        Impuesto Incluido
                      </label>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="modal-footer">
              <button
                type="button"
                class="btn btn-secondary"
                @click="closeModal()"
              >
                Cerrar
              </button>
              <button
                type="button"
                class="btn btn-primary"
                @click="formClient.id ? EditClient() : CreateClient()"
              >
                Guardar
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      formClient: {
        name: "",
        address: "",
        mobile: "",
        contact: "",
        email: "",
        type_person: "",
        departament: "",
        municipality_id: "",
        type_document: "",
        document: "",
        active: "",
        tax: "",
      },
      departments: [],
      municipalities: [],
    };
  },
  created() {
    this.getDepartments();
  },
  methods: {
    CreateClient() {
      let me = this;
      axios
        .post("api/clients", this.formClient, this.$root.config)
        .then(function () {
          $("#clientModal").modal("hide");
          me.ResetData();
        });
    },
    OpenEditClient(client) {
      let me = this;
      $("#clientModal").modal("show");
      me.formClient = client;

      if (client.municipality) {
        me.formClient.departament = client.municipality.department_id;
        this.getMunicipalities(me.formClient.departament);
      } else {
        me.formSupplier.departament = "";
        me.formSupplier.municipality_id = "";
      }
    },

    EditClient() {
      let me = this;
      axios
        .put(
          "api/clients/" + this.formClient.id,
          this.formClient,
          this.$root.config
        )
        .then(function () {
          $("#clientModal").modal("hide");
          me.ResetData();
        });
    },
    ResetData() {
      let me = this;
      $("#clientModal").modal("hide");
      Object.keys(this.formClient).forEach(function (key, index) {
        me.formClient[key] = "";
      });
      this.$emit("list-clients");
    },

    closeModal: function () {
      let me = this;
      this.ResetData();
    },
    getDepartments() {
      axios.get("api/departments", this.$root.config).then((response) => {
        this.departments = response.data.departments;
      });
    },
    getMunicipalities(department) {
      axios
        .get(
          "api/departments/" + department + "/getMunicipalities",
          this.$root.config
        )
        .then((response) => {
          this.municipalities = response.data.municipalities;
        });
    },
  },
  mounted() {
    console.log("Component mounted.");
  },
};
</script>
