<template>
  <div
    class="modal fade"
    id="observationsModal"
    tabindex="-1"
    role="dialog"
    aria-labelledby="observationsModalLabel"
    aria-hidden="true"
  >
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="observationsModalLabel">Observaciones del Producto</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <!-- Creación arriba -->
          <div class="mb-4">
            <div class="form-group">
              <label for="newObservation">Nueva observación</label>
              <textarea
                id="newObservation"
                class="form-control"
                v-model="newObservation"
                rows="3"
              ></textarea>
            </div>
            <button class="btn btn-primary" @click="addObservation">Agregar</button>
          </div>

          <!-- Lista abajo con varios registros por fila -->
          <div class="row">
            <div
              class="col-md-4 mb-3"
              v-for="obs in observations"
              :key="obs.id"
            >
              <div class="card">
                <div class="card-body position-relative">
                  <!-- Icono de eliminar en lugar de texto -->
                  <button
                    type="button"
                    class="btn p-0 position-absolute"
                    style="top:0.5rem; right:0.5rem;"
                    @click="deleteObservation(obs.id)"
                  >
                    <i class="bi bi-x-circle-fill text-danger"></i>
                  </button>
                  <p class="card-text">{{ obs.observation }}</p>
                </div>
              </div>
            </div>
            <div
              v-if="observations.length === 0"
              class="col-12 text-center text-muted"
            >
              No hay observaciones.
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios'

export default {
  name: 'ProductObservations',
  data() {
    return {
      productId: null,
      observations: [],
      newObservation: ''
    }
  },
  methods: {
    open(id) {
      this.productId = id
      this.fetchObservations()
      $('#observationsModal').modal('show')
    },
    fetchObservations() {
      axios
        .get(`api/products/${this.productId}/observations`, { headers: this.$root.config.headers })
        .then(res => {
          this.observations = res.data.observations
        })
    },
    addObservation() {
      if (!this.newObservation) return
      axios
        .post(
          `api/products/${this.productId}/observations`,
          { observation: this.newObservation },
          { headers: this.$root.config.headers }
        )
        .then(() => {
          this.newObservation = ''
          this.fetchObservations()
        })
    },
    deleteObservation(obsId) {
      axios
        .delete(`api/products/observations/${obsId}`, { headers: this.$root.config.headers })
        .then(() => {
          this.fetchObservations()
        })
    }
  }
}
</script>
