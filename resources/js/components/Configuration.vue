<template>
  <div class="col-12">
<<<<<<< HEAD
    <h3 class="text-center page-header">Configuración</h3>
    <div class="d-flex justify-content-center">
      <form
        id="form_configuration"
        action="#"
        @submit.prevent="saveConfiguration"
      >
        <div class="form-row">
          <div class="form-group col-12 col-md-6">
            <label for="name">Nombre de la empresa</label>
            <input
              type="text"
              class="form-control"
              id="name"
              name="name"
              placeholder="Ingresar nombre"
              :value="formConfiguration.name"
            />
            <small id="nameHelp" class="form-text text-danger">{{
              formErrors.name
            }}</small>
          </div>
          <div class="form-group col-12 col-md-6">
            <label for="legal_representative">Representante legal</label>
            <input
              type="text"
              class="form-control"
              id="legal_representative"
              name="legal_representative"
              placeholder="Ingresar representante legal"
              :value="formConfiguration.legal_representative"
            />
            <small
              id="legal_representativeHelp"
              class="form-text text-danger"
            >{{ formErrors.legal_representative }}</small>
          </div>
        </div>
        <div class="form-row">
          <div class="form-group col-12 col-md-6">
            <label for="nit">Nit</label>
            <input
              type="text"
              class="form-control"
              id="nit"
              name="nit"
              placeholder="Ingresar Nit"
              :value="formConfiguration.nit"
            />
            <small id="nitHelp" class="form-text text-danger">{{
              formErrors.nit
            }}</small>
          </div>
          <div class="form-group col-12 col-md-6">
            <label for="address">Dirección</label>
            <input
              type="text"
              class="form-control"
              id="address"
              name="address"
              placeholder="Ingresar dirección"
              :value="formConfiguration.address"
            />
            <small id="addressHelp" class="form-text text-danger">{{
              formErrors.address
            }}</small>
          </div>
        </div>
        <div class="form-row">
          <div class="form-group col-12 col-md-6">
            <label for="email">Email</label>
            <input
              type="email"
              class="form-control"
              id="email"
              name="email"
              placeholder="Ingresar email"
              :value="formConfiguration.email"
            />
            <small id="emailHelp" class="form-text text-danger">{{
              formErrors.email
            }}</small>
          </div>
          <div class="form-group col-12 col-md-6">
            <label for="tax_regime">Regimen tributario</label>
            <input
              type="text"
              class="form-control"
              id="tax_regime"
              name="tax_regime"
              placeholder="Ingresar régimen tributario"
              :value="formConfiguration.tax_regime"
            />
            <small id="tax_regimeHelp" class="form-text text-danger">{{
              formErrors.tax_regime
            }}</small>
          </div>
          <div class="form-group col-12 col-md-6">
            <label for="telephone">Teléfono</label>
            <input
              type="tel"
              class="form-control"
              id="telephone"
              name="telephone"
              placeholder="Ingresar teléfono"
              :value="formConfiguration.telephone"
            />
            <small id="telephoneHelp" class="form-text text-danger">{{
              formErrors.telephone
            }}</small>
          </div>
          <div class="form-group col-12 col-md-6">
            <label for="mobile">Celular</label>
            <input
              type="tel"
              class="form-control"
              id="mobile"
              name="mobile"
              placeholder="Ingresar celular"
              :value="formConfiguration.mobile"
            />
            <small id="mobileHelp" class="form-text text-danger">{{
              formErrors.mobile
            }}</small>
          </div>
          <div class="form-group col-12 col-md-6">
            <label for="condition_order">Condiciones de ticket</label>
            <textarea
              class="form-control"
              name="condition_order"
              id="condition_order"
              cols="30"
              rows="4"
              :value="formConfiguration.condition_order"
            ></textarea>
            <small id="condition_orderHelp" class="form-text text-danger">{{
              formErrors.condition_order
            }}</small>
          </div>
          <div class="form-group col-12 col-md-6">
            <label for="condition_quotation">Condiciones de cotización</label>
            <ckeditor
              :editor="editor"
              :config="editorConfig"
              v-model="formConfiguration.condition_quotation"
              :tagName="'textarea'"
              id="condition_quotation"
              name="condition_quotation"
            ></ckeditor>
            <small id="condition_quotationHelp" class="form-text text-danger">{{
              formErrors.condition_quotation
            }}</small>
          </div>
          <div class="form-group col-12 col-md-6">
            <label for="printer">Impresora POS</label>
            <input
              type="tel"
              class="form-control"
              id="printer"
              name="printer"
              placeholder="Ingresar nombre de impresora pos"
              :value="formConfiguration.printer"
            />
            <small id="printerHelp1" class="text-muted">
              * Esta impresora debe estar previamente configurada en su sistema
            </small>
            <small id="printerHelp2" class="form-text text-danger">{{
              formErrors.printer
            }}</small>
          </div>
        </div>
        <div class="form-group">
          <label for="logo">Logo</label>
          <div class="p-0 border">
            <div style="height: 230px" class="d-flex justify-content-center my-2">
              <img
                id="image"
                class="border"
                style="height: 230px; width: 200px; object-fit: cover"
                :src="formConfiguration.logo"
                alt="logo"
              />
            </div>
            <label class="btn border btn-block mb-0">
              <i class="bi bi-pen"></i>
              <input
                id="logo"
                name="file0"
                data-info="image"
                type="file"
                style="display: none"
                @change="(event) => { readImage(event.target); }"
=======
    <h3 class="text-center page-header mb-4">Configuración General</h3>
    <div class="card">
      <div class="card-body">
        <form id="form_configuration" @submit.prevent="saveConfiguration" enctype="multipart/form-data">
          <div class="row g-3">
            <!-- Empresa y Representante -->
            <div class="col-md-6">
              <label for="name" class="form-label">Nombre de la empresa</label>
              <input
                type="text"
                class="form-control"
                id="name"
                name="name"
                v-model="formConfiguration.name"
>>>>>>> 0ed4468 (factuara electronica + reserva + caja)
              />
              <small class="form-text text-danger">{{ formErrors.name }}</small>
            </div>
            <div class="col-md-6">
              <label for="legal_representative" class="form-label">Representante legal</label>
              <input
                type="text"
                class="form-control"
                id="legal_representative"
                name="legal_representative"
                v-model="formConfiguration.legal_representative"
              />
              <small class="form-text text-danger">{{ formErrors.legal_representative }}</small>
            </div>

            <!-- Nit y Dirección -->
            <div class="col-md-6">
              <label for="nit" class="form-label">Nit</label>
              <input
                type="text"
                class="form-control"
                id="nit"
                name="nit"
                v-model="formConfiguration.nit"
              />
              <small class="form-text text-danger">{{ formErrors.nit }}</small>
            </div>
            <div class="col-md-6">
              <label for="address" class="form-label">Dirección</label>
              <input
                type="text"
                class="form-control"
                id="address"
                name="address"
                v-model="formConfiguration.address"
              />
              <small class="form-text text-danger">{{ formErrors.address }}</small>
            </div>

            <!-- Email y Régimen -->
            <div class="col-md-6">
              <label for="email" class="form-label">Email</label>
              <input
                type="email"
                class="form-control"
                id="email"
                name="email"
                v-model="formConfiguration.email"
              />
              <small class="form-text text-danger">{{ formErrors.email }}</small>
            </div>
            <div class="col-md-6">
              <label for="tax_regime" class="form-label">Régimen tributario</label>
              <input
                type="text"
                class="form-control"
                id="tax_regime"
                name="tax_regime"
                v-model="formConfiguration.tax_regime"
              />
              <small class="form-text text-danger">{{ formErrors.tax_regime }}</small>
            </div>

            <!-- Teléfono y Celular -->
            <div class="col-md-6">
              <label for="telephone" class="form-label">Teléfono</label>
              <input
                type="tel"
                class="form-control"
                id="telephone"
                name="telephone"
                v-model="formConfiguration.telephone"
              />
              <small class="form-text text-danger">{{ formErrors.telephone }}</small>
            </div>
            <div class="col-md-6">
              <label for="mobile" class="form-label">Celular</label>
              <input
                type="tel"
                class="form-control"
                id="mobile"
                name="mobile"
                v-model="formConfiguration.mobile"
              />
              <small class="form-text text-danger">{{ formErrors.mobile }}</small>
            </div>

            <!-- Condiciones de Ticket -->
            <div class="col-md-6">
              <label for="condition_order" class="form-label">Condiciones de ticket</label>
              <textarea
                class="form-control"
                id="condition_order"
                name="condition_order"
                rows="4"
                v-model="formConfiguration.condition_order"
              ></textarea>
              <small class="form-text text-danger">{{ formErrors.condition_order }}</small>
            </div>

            <!-- Condiciones de Cotización -->
            <div class="col-md-6">
              <label for="condition_quotation" class="form-label">Condiciones de cotización</label>
              <ckeditor
                :editor="editor"
                :config="editorConfig"
                v-model="formConfiguration.condition_quotation"
                id="condition_quotation"
                name="condition_quotation"
              ></ckeditor>
              <small class="form-text text-danger">{{ formErrors.condition_quotation }}</small>
            </div>

            <!-- Impresora POS -->
            <div class="col-md-6">
              <label for="printer" class="form-label">Impresora POS</label>
              <input
                type="text"
                class="form-control"
                id="printer"
                name="printer"
                v-model="formConfiguration.printer"
              />
              <small class="form-text text-muted">
                * Esta impresora debe estar previamente configurada en su sistema
              </small>
              <small class="form-text text-danger">{{ formErrors.printer }}</small>
            </div>

            <!-- Enviar Comanda (slider) -->
            <div class="col-md-6 d-flex align-items-center">
              <label for="shipments" class="form-label me-3 mb-0">Enviar comanda a cocina : </label>
              <label class="switch mb-0">
                <input type="checkbox" id="shipments" v-model="formConfiguration.shipments" />
                <span class="slider round"></span>
              </label>
            </div>

            <!-- Logo -->
            <div class="col-12">
              <label class="form-label">Logo</label>
              <div class="border p-3 text-center">
                <img
                  :src="formConfiguration.logo"
                  alt="Logo"
                  class="img-fluid"
                  style="max-height: 200px; object-fit: contain;"
                />
                <div class="mt-2">
                  <label class="btn btn-outline-secondary mb-0">
                    <i class="bi bi-pen"></i> Cambiar
                    <input type="file" accept="image/*" @change="readImage" hidden />
                  </label>
                </div>
                <small class="form-text text-danger">{{ formErrors.file0 }}</small>
              </div>
            </div>
          </div>
          <!-- Botón Guardar -->
          <div class="mt-4 text-end">
            <button type="submit" class="btn btn-primary px-4">Guardar cambios</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script>
import ClassicEditor from "@ckeditor/ckeditor5-build-classic";
import "@ckeditor/ckeditor5-build-classic/build/translations/es";
<<<<<<< HEAD
=======
import axios from "axios";
>>>>>>> 0ed4468 (factuara electronica + reserva + caja)
import Swal from "sweetalert2";

export default {
  data() {
    return {
      formConfiguration: {
        id: null,
        name: "",
        legal_representative: "",
        nit: "",
        address: "",
        email: "",
        tax_regime: "",
        telephone: "",
        mobile: "",
        logo: "",
        printer: "",
        condition_order: "",
        condition_quotation: "",
        shipments: false
      },
      formErrors: {
        name: "",
        legal_representative: "",
        nit: "",
        address: "",
        email: "",
        tax_regime: "",
        telephone: "",
        mobile: "",
        file0: "",
        printer: "",
        condition_order: "",
        condition_quotation: ""
      },
      editor: ClassicEditor,
      editorConfig: {
        toolbar: {
          removeItems: [
            "uploadImage",
            "mediaEmbed",
            "blockQuote",
            "insertTable",
            "outdent",
            "indent",
            "link"
          ],
          shouldNotGroupWhenFull: true
        },
        language: "es"
      }
    };
  },
  created() {
    this.$root.validateToken();
    this.getConfiguration();
  },
  methods: {
<<<<<<< HEAD
    getCofiguration() {
      axios.get("api/configurations", this.$root.config).then((response) => {
        if (response.data.configuration) {
          this.formConfiguration = response.data.configuration;
          this.formConfiguration.condition_quotation =
            this.formConfiguration.condition_quotation ?? "";
        }
      });
=======
    getConfiguration() {
      axios.get("api/configurations", this.$root.config)
        .then(({ data }) => {
          const cfg = data.configuration;
          Object.assign(this.formConfiguration, cfg, { shipments: cfg.shipments ?? false });
          this.formConfiguration.condition_quotation = cfg.condition_quotation || "";
        });
>>>>>>> 0ed4468 (factuara electronica + reserva + caja)
    },
    saveConfiguration() {
      this.clearErrors();
      const form = new FormData(document.getElementById("form_configuration"));
      form.append("id", this.formConfiguration.id);
      form.set("condition_quotation", this.formConfiguration.condition_quotation);
      form.set("shipments", this.formConfiguration.shipments ? 1 : 0);

<<<<<<< HEAD
      axios
        .post("api/configurations", form, this.$root.config)
        .then((response) => {
          this.formConfiguration = response.data.configuration;
          // Mostrar ventana emergente con SweetAlert2
          Swal.fire({
            icon: "success",
            title: "Guardado",
            text: response.data.message,
            timer: 2000,
            showConfirmButton: false
          });
=======
      axios.post("api/configurations", form, this.$root.config)
        .then(({ data }) => {
          Swal.fire({ icon: 'success', title: 'Guardado', text: data.message, timer: 2000, showConfirmButton: false });
>>>>>>> 0ed4468 (factuara electronica + reserva + caja)
        })
        .catch(err => this.assignErrors(err));
    },
    clearErrors() {
      Object.keys(this.formErrors).forEach(k => (this.formErrors[k] = ""));
    },
    assignErrors(response) {
      const errors = response.response.data.errors;
      for (let field in this.formErrors) {
        if (errors[field]) this.formErrors[field] = errors[field][0];
      }
    },
    readImage(event) {
      const file = event.target.files[0];
      if (!file) return;
      const reader = new FileReader();
      reader.onload = e => (this.formConfiguration.logo = e.target.result);
      reader.readAsDataURL(file);
    }
  }
};
</script>

<style scoped>
.page-header { font-size: 1.5rem; }
.form-label { font-weight: 600; }

/* Slider para "Enviar comanda" */
.switch {
  position: relative;
  display: inline-block;
  width: 50px;
  height: 24px;
}
.switch input {
  opacity: 0;
  width: 0;
  height: 0;
}
.slider {
  position: absolute;
  cursor: pointer;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: #ccc;
  transition: .4s;
  border-radius: 24px;
}
.slider:before {
  position: absolute;
  content: "";
  height: 18px;
  width: 18px;
  left: 3px;
  bottom: 3px;
  background-color: white;
  transition: .4s;
  border-radius: 50%;
}
input:checked + .slider {
  background-color: #34BF9B;
}
input:checked + .slider:before {
  transform: translateX(26px);
}
</style>