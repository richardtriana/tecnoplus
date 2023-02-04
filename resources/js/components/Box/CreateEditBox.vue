<template>
  <div>
    <div
      class="modal fade"
      id="boxModal"
      tabindex="-1"
      aria-labelledby="boxModalLabel"
      aria-hidden="true"
      data-backdrop="static"
    >
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="boxModalLabel">Caja</h5>
            <button
              type="button"
              class="close"
              data-dismiss="modal"
              aria-label="Close"
            >
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <form id="formBox">
              <div class="form-group">
                <label for="formGroupExampleInput">Nombre o Numero</label>
                <input
                  type="text"
                  class="form-control"
                  id="name"
                  placeholder="Ingresar nombre o número"
                  v-model="formBox.name"
                />
                <small id="nameHelp" class="form-text text-danger">{{
                  formErrors.name
                }}</small>
              </div>
              <div class="form-group">
                <label for="formGroupExampleInput">Prefijo</label>
                <input
                  type="text"
                  class="form-control"
                  id="prefix"
                  placeholder="Ingresar prefijo"
                  v-model="formBox.prefix"
                  :disabled ="formBox.process"
                />
                <small id="nameHelp" class="form-text text-danger">{{
                  formErrors.prefix
                }}</small>
              </div>  
              <div class="form-row py-3">
                <div class="col">
                  <h5>
                    Consecutivos
                  </h5>
                </div>
                <div class="col text-right">
                  <button type="button" class="btn btn-primary" @click="addConsecutive">
                    Añadir
                  </button>
                </div>
                <table class="table table-sm table-bordered table-responsive-sm mt-3">
                  <thead>
                    <th>#</th>
                    <th>Consecutivo inicial</th>
                    <th>Consecutivo final</th>
                    <th>Fecha de inicio</th>
                    <th>Fecha vencimiento</th>
                    <th>Opciones</th>
                  </thead>
                  <tbody>
                    <tr v-for="(item, key) in consecutive_box" :key="key">
                      <td>
                        {{key + 1}}
                      </td>
                      <td>
                        <input type="number" class="form-control form-control-sm" placeholder="Ingresar consecutivo desde" v-model="item.from_nro" :disabled="item.process">
                        <small v-if="consecutive_boxErrors['consecutive_box.'+key+'.from_nro']" class="form-text text-danger">
                          {{ consecutive_boxErrors['consecutive_box.'+key+'.from_nro'][0] }}
                        </small>
                      </td>
                      <td>
                        
                        <input type="number" class="form-control form-control-sm" placeholder="Ingresar consecutivo hasta" v-model="item.until_nro" :disabled="item.process ">
                        <small v-if="consecutive_boxErrors['consecutive_box.'+key+'.until_nro']" class="form-text text-danger">
                          {{ consecutive_boxErrors['consecutive_box.'+key+'.until_nro'][0] }}
                        </small>
                      </td>
                      <td>
                        
                        <input type="date" class="form-control form-control-sm" placeholder="Ingresar fecha desde" v-model="item.from_date" :disabled="item.process ">
                         <small v-if="consecutive_boxErrors['consecutive_box.'+key+'.from_date']" class="form-text text-danger">
                          {{ consecutive_boxErrors['consecutive_box.'+key+'.from_date'][0] }}
                        </small>   
                      </td>
                      <td>
                        <input type="date" class="form-control form-control-sm" placeholder="Ingresar fecha hasta" v-model="item.until_date" :disabled="item.process ">
                        <small v-if="consecutive_boxErrors['consecutive_box.'+key+'.until_date']" class="form-text text-danger">
                          {{ consecutive_boxErrors['consecutive_box.'+key+'.until_date'][0] }}
                        </small>
                      </td>
                      <td>
                        <button v-if="item.process" class="btn" disabled>
                          <i  class="bi bi-x-circle"></i>
                        </button>
                        <button v-else class="btn btn-outline-danger" @click="removeConsecutive(key)">
                          <i class="bi bi-x-circle"></i>
                        </button>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </form>
          </div>
          <div class="modal-footer">
            <button
              type="button"
              class="btn btn-secondary"
              data-dismiss="modal"
            >
              Cerrar
            </button>
            <button
              type="button"
              class="btn btn-primary"
              @click="formBox.id ? EditBox() : CreateBox()"
            >
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
      formBox: {
        name: "",
        prefix: "",
        process: null,
        consecutive_box: [],
        consecutive_load: []
      },
      consecutive_box: [],
      consecutive_boxErrors: {},
      formErrors: {
        name: "",
        prefix: "",
      },
      
    };
  },
  created() {},
  methods: {
    getConsecutiveAllByBox(box){
      axios.
      get("api/boxes/"+box+"/consecutiveAll", this.$root.config)
      .then(response => {
        this.formBox.consecutive_load = response.data.consecutive;
        this.consecutive_box = response.data.consecutive;
      })
      .catch(response => {
        this.consecutive_box = [];
      });
    },
    CreateBox() {
      let me = this;
      me.assignErrors(false);
      me.formBox.consecutive_box = this.consecutive_box;
      axios
        .post("api/boxes", this.formBox, this.$root.config)
        .then(function () {
          me.ResetData();
          me.$emit("list-boxes");
        })
        .catch((response) => {
          me.assignErrors(response);
        });
    },
    OpenEditBox(box) {
      let me = this;
      me.ResetData();
      $("#boxModal").modal("show");
      me.formBox = box;
      me.getConsecutiveAllByBox(me.formBox.id);
      
    },

    EditBox() {
      let me = this;
      me.assignErrors(false);
      me.formBox.consecutive_box = this.consecutive_box;

      axios
        .put("api/boxes/" + this.formBox.id, this.formBox, this.$root.config)
        .then(function () {
          me.ResetData();
          me.$emit("list-boxes");
        })
        .catch((response) => {
          me.assignErrors(response);
        });
    },
    addConsecutive(){
      this.consecutive_box.push({
        from_nro:'',
        until_nro:'',
        from_date:'',
        until_date:'',
        process:false 
      });
    },
    removeConsecutive(index){
      this.consecutive_box.splice(index, 1);
    },
    ResetData() {
      let me = this;
      $("#boxModal").modal("hide");
      me.consecutive_box = [];
      me.formBox = {
        name: "",
        prefix: "",
        process: null,
        consecutive_box: [],
        consecutive_load: []
      }
      me.assignErrors(false);
    },
    assignErrors(response) {
      if (response) {
        var errors = response.response.data.errors;
        this.consecutive_boxErrors = errors;

        if (errors.name) {
          this.formErrors.name = errors.name[0];
        }

        if (errors.prefix) {
          this.formErrors.prefix = errors.prefix[0];
        }
      } else {
        this.formErrors.name = "";
        this.formErrors.prefix = "";
        this.consecutive_boxErrors = {};      
      }
    },
  },
  mounted() {},
};
</script>