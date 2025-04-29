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
            <h5 class="modal-title" id="clientModalLabel">Cliente</h5>
            <button type="button" class="close" @click="closeModal()">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <!-- Se utiliza @submit.prevent para ejecutar handleSubmit() -->
          <form id="formClient" @submit.prevent="handleSubmit">
            <div class="modal-body">
              <!-- Select de Tipo de Organización (se muestra primero) -->
              <div class="form-group">
                <label for="organization_type_id">Tipo de Organización</label>
                <select
                  class="form-control"
                  id="organization_type_id"
                  v-model="formClient.organization_type_id"
                  required
                >
                  <option value="" disabled>Selecciona un tipo de organización</option>
                  <option
                    v-for="org in organizationTypes"
                    :value="org.id"
                    :key="org.id"
                  >
                    {{ org.name }}
                  </option>
                </select>
                <div v-if="errors.organization_type_id" class="text-danger">
                  {{ errors.organization_type_id }}
                </div>
              </div>

              <!-- Campo para Tipo de Documento y Número -->
              <div class="form-row">
                <div class="input-group mb-3">
                  <div class="input-group-prepend">
                    <select
                      class="custom-select"
                      id="identity_document_type_id"
                      v-model="formClient.identity_document_type_id"
                      required
                      autocomplete="off"
                    >
                      <option selected disabled value="">
                        Selecciona un tipo de documento...
                      </option>
                      <option
                        v-for="docType in identityDocumentTypes"
                        :value="docType.id"
                        :key="docType.id"
                      >
                        {{ docType.name }}
                      </option>
                    </select>
                  </div>
                  <input
                    type="text"
                    class="form-control"
                    aria-label="Documento"
                    v-model="formClient.document"
                    placeholder="Número de documento"
                    required
                  />
                </div>
                <div v-if="errors.identity_document_type_id" class="text-danger">
                  {{ errors.identity_document_type_id }}
                </div>
                <div v-if="errors.document" class="text-danger">
                  {{ errors.document }}
                </div>
              </div>

              <!-- Mostrar nombres y apellidos solo si la organización es Persona Natural (id == 2) -->
              <div v-if="formClient.organization_type_id == 2">
                <!-- Fila para Nombres -->
                <div class="form-row">
                  <div class="form-group col-md-6">
                    <label for="first_name">Primer Nombre</label>
                    <input
                      type="text"
                      class="form-control"
                      id="first_name"
                      placeholder="Primer nombre"
                      v-model="formClient.first_name"
                      required
                    />
                    <div v-if="errors.first_name" class="text-danger">
                      {{ errors.first_name }}
                    </div>
                  </div>
                  <div class="form-group col-md-6">
                    <label for="second_name">Segundo Nombre</label>
                    <input
                      type="text"
                      class="form-control"
                      id="second_name"
                      placeholder="Segundo nombre"
                      v-model="formClient.second_name"
                    />
                  </div>
                </div>
                <!-- Fila para Apellidos -->
                <div class="form-row">
                  <div class="form-group col-md-6">
                    <label for="first_lastname">Primer Apellido</label>
                    <input
                      type="text"
                      class="form-control"
                      id="first_lastname"
                      placeholder="Primer apellido"
                      v-model="formClient.first_lastname"
                      required
                    />
                    <div v-if="errors.first_lastname" class="text-danger">
                      {{ errors.first_lastname }}
                    </div>
                  </div>
                  <div class="form-group col-md-6">
                    <label for="second_lastname">Segundo Apellido</label>
                    <input
                      type="text"
                      class="form-control"
                      id="second_lastname"
                      placeholder="Segundo apellido"
                      v-model="formClient.second_lastname"
                    />
                  </div>
                </div>
              </div>

              <!-- Campo para Razón Social (obligatorio) -->
              <div class="form-group">
                <label for="razon_social">Razón Social</label>
                <input
                  type="text"
                  class="form-control"
                  id="razon_social"
                  placeholder="Ingresar razón social"
                  v-model="formClient.razon_social"
                  required
                />
                <div v-if="errors.razon_social" class="text-danger">
                  {{ errors.razon_social }}
                </div>
              </div>

              <!-- Select para Régimen (client_tribute_id) -->
              <div class="form-group">
                <label for="client_tribute_id">Régimen</label>
                <select
                  class="form-control"
                  id="client_tribute_id"
                  v-model="formClient.client_tribute_id"
                  required
                >
                  <option value="" disabled>Selecciona un régimen</option>
                  <option
                    v-for="tribute in clientTributes"
                    :value="tribute.id"
                    :key="tribute.id"
                  >
                    {{ tribute.name }}
                  </option>
                </select>
                <div v-if="errors.client_tribute_id" class="text-danger">
                  {{ errors.client_tribute_id }}
                </div>
              </div>

              <!-- Datos de Contacto -->
              <div class="form-row">
                <div class="form-group col-6">
                  <label for="address">Dirección</label>
                  <input
                    type="text"
                    class="form-control"
                    id="address"
                    placeholder="Ingresar dirección"
                    v-model="formClient.address"
                  />
                </div>
                <div class="form-group col-6">
                  <label for="phone">Teléfono</label>
                  <input
                    type="text"
                    class="form-control"
                    id="phone"
                    placeholder="Ingresar teléfono"
                    v-model="formClient.phone"
                  />
                </div>
              </div>
              <div class="form-row">
                <div class="form-group col-6">
                  <label for="email">Correo Electrónico</label>
                  <input
                    type="email"
                    class="form-control"
                    id="email"
                    placeholder="Ingresar email"
                    v-model="formClient.email"
                  />
                </div>
              </div>

              <!-- Mostrar Dígito de Verificación solo si el tipo de documento es NIT (id == 6) -->
              <div class="form-group" v-if="formClient.identity_document_type_id == 6">
                <label for="div_verification">Dígito de Verificación</label>
                <input
                  type="text"
                  class="form-control"
                  id="div_verification"
                  placeholder="Opcional"
                  v-model="formClient.div_verification"
                />
              </div>

              <!-- Ubicación: Departamento y Municipio -->
              <div class="form-row">
                <div class="col-md-7 border-right border-gray">
                  <div class="form-group">
                    <label for="departament">Departamento</label>
                    <select
                      class="form-control"
                      id="departament"
                      v-model="formClient.departament"
                      @change="getMunicipalities(formClient.departament)"
                      required
                    >
                      <option value="" disabled>
                        Selecciona un departamento
                      </option>
                      <option
                        v-for="department in departments"
                        :value="department.department"
                        :key="department.department"
                      >
                        {{ department.department }}
                      </option>
                    </select>
                    <div v-if="errors.departament" class="text-danger">
                      {{ errors.departament }}
                    </div>
                  </div>
                  <div class="form-group">
                    <label for="city">Municipio</label>
                    <select
                      class="form-control"
                      id="city"
                      v-model="formClient.municipality_id"
                      required
                    >
                      <option value="" disabled>
                        Selecciona un municipio
                      </option>
                      <option
                        v-for="municipality in municipalities"
                        :value="municipality.id"
                        :key="municipality.id"
                      >
                        {{ municipality.name }}
                      </option>
                    </select>
                    <div v-if="errors.municipality_id" class="text-danger">
                      {{ errors.municipality_id }}
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" @click="closeModal()">
                Cerrar
              </button>
              <button type="submit" class="btn btn-primary">
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
        id: null,
        first_name: "",
        second_name: "",
        first_lastname: "",
        second_lastname: "",
        razon_social: "",
        address: "",
        phone: "",
        email: "",
        document: "",
        div_verification: "",
        departament: "",
        municipality_id: "",
        identity_document_type_id: "",
        client_tribute_id: "",
        organization_type_id: "",
        active: "1",
      },
      departments: [],
      municipalities: [],
      identityDocumentTypes: [],
      clientTributes: [],
      organizationTypes: [],
      errors: {},
    };
  },
  created() {
    this.getDepartments();
    this.getIdentityDocumentTypes();
    this.getClientTributes();
    this.getOrganizationTypes();
  },
  methods: {
    validateForm() {
      this.errors = {};

      // Validar campos obligatorios
      if (!this.formClient.razon_social) {
        this.errors.razon_social = "La razón social es obligatoria.";
      }
      if (!this.formClient.document) {
        this.errors.document = "El documento es obligatorio.";
      }
      if (!this.formClient.identity_document_type_id) {
        this.errors.identity_document_type_id = "El tipo de documento es obligatorio.";
      }
      if (!this.formClient.client_tribute_id) {
        this.errors.client_tribute_id = "El régimen es obligatorio.";
      }
      if (!this.formClient.organization_type_id) {
        this.errors.organization_type_id = "El tipo de organización es obligatorio.";
      }
      if (!this.formClient.municipality_id) {
        this.errors.municipality_id = "El municipio es obligatorio.";
      }
      // Si la organización es Persona Natural (id == 2) se requieren nombres y primer apellido
      if (this.formClient.organization_type_id == 2) {
        if (!this.formClient.first_name) {
          this.errors.first_name = "El primer nombre es obligatorio.";
        }
        if (!this.formClient.first_lastname) {
          this.errors.first_lastname = "El primer apellido es obligatorio.";
        }
      }
      return Object.keys(this.errors).length === 0;
    },
    handleSubmit() {
      if (this.validateForm()) {
        if (this.formClient.id) {
          this.EditClient();
        } else {
          this.CreateClient();
        }
      }
    },
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
      me.formClient = { ...client };
      if (client.municipality) {
        me.formClient.departament = client.municipality.department;
        me.formClient.municipality_id = client.municipality.id;
      } else {
        me.formClient.departament = "";
        me.formClient.municipality_id = "";
      }
      if (client.identityDocumentType) {
        me.formClient.identity_document_type_id = client.identityDocumentType.id;
      }
      if (client.clientTribute) {
        me.formClient.client_tribute_id = client.clientTribute.id;
      }
      if (client.organizationType) {
        me.formClient.organization_type_id = client.organizationType.id;
      }
    },
    EditClient() {
      let me = this;
      axios
        .put("api/clients/" + this.formClient.id, this.formClient, this.$root.config)
        .then(function () {
          $("#clientModal").modal("hide");
          me.ResetData();
        });
    },
    ResetData() {
      $("#clientModal").modal("hide");
      Object.keys(this.formClient).forEach((key) => {
        this.formClient[key] = key === "active" ? "1" : "";
      });
      this.errors = {};
      this.$emit("list-clients");
    },
    closeModal() {
      this.ResetData();
    },
    getDepartments() {
      axios.get("api/departments", this.$root.config).then((response) => {
        this.departments = response.data.departments;
      });
    },
    getMunicipalities(departmentValue) {
      axios
        .get("api/departments/" + encodeURIComponent(departmentValue) + "/getMunicipalities", this.$root.config)
        .then((response) => {
          this.municipalities = response.data.municipalities;
        });
    },
    getIdentityDocumentTypes() {
      axios.get("api/identity-document-types", this.$root.config).then((response) => {
        this.identityDocumentTypes = response.data.identityDocumentTypes;
      });
    },
    getClientTributes() {
      axios.get("api/client-tributes", this.$root.config).then((response) => {
        this.clientTributes = response.data.clientTributes;
      });
    },
    getOrganizationTypes() {
      axios.get("api/organization-types", this.$root.config).then((response) => {
        this.organizationTypes = response.data.organizationTypes;
      });
    },
  },
  mounted() {
    console.log("Component mounted.");
  },
};
</script>

<style scoped>
.container {
  padding: 20px;
}
.text-danger {
  font-size: 0.875rem;
}
</style>
