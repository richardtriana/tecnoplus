<template>
  <div class="page">
    <div class="page-header">
      <div class="row">
        <div class="col">
          <h3 class="page-title">Factus - Sincronización de Datos</h3>
        </div>
      </div>
    </div>

    <!-- Cards con el conteo de registros locales -->
    <div class="row mb-3">
      <div class="col-md-3" v-for="(card, index) in localStats" :key="index">
        <div class="card text-center">
          <div class="card-body">
            <h5>{{ card.title }}</h5>
            <h2>{{ card.count }}</h2>
            <p>{{ card.subtitle }}</p>
          </div>
        </div>
      </div>
    </div>

    <div class="page-content">
      <!-- Botón para iniciar la sincronización -->
      <button class="btn btn-primary" @click="syncData" :disabled="isSyncing">
        Sincronizar Datos
      </button>
    </div>

    <!-- Modal de sincronización -->
    <div v-if="isSyncing" class="modal-overlay">
      <div class="modal-content">
        <div class="spinner">
          <!-- Reemplaza moon-loader por el spinner que uses, o un simple ícono/loading -->
          <moon-loader :loading="isSyncing" :color="'#032F6C'" :size="50" />
        </div>
        <p>Sincronizando datos, por favor espere...</p>

        <!-- Log de sincronización dentro del modal -->
        <div class="sync-logs mt-3">
          <ul>
            <li v-for="(log, idx) in syncLogs" :key="idx">
              {{ log }}
            </li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: 'Factus',
  data() {
    return {
      isSyncing: false,
      syncLogs: [],
      localStats: [
        { title: 'Rangos de Numeración', count: 0, subtitle: 'Registros locales' },
        { title: 'Municipios', count: 0, subtitle: 'Registros locales' },
        { title: 'Tributos de Productos', count: 0, subtitle: 'Registros locales' },
        { title: 'Unidades de Medida', count: 0, subtitle: 'Registros locales' },
      ]
    };
  },
  created() {
    // Al montar el componente, cargamos las estadísticas locales
    this.loadLocalStats();
  },
  methods: {
    /**
     * Carga los conteos de registros locales desde el endpoint /factus/local-stats
     */
    async loadLocalStats() {
      try {
        const response = await axios.get('api/factus/local-stats');
        const data = response.data;
        this.localStats[0].count = data.numberingRanges || 0;
        this.localStats[1].count = data.municipalities || 0;
        this.localStats[2].count = data.tributes || 0;
        this.localStats[3].count = data.units || 0;
      } catch (error) {
        console.error('Error al cargar estadísticas locales:', error);
      }
    },

    /**
     * Inicia la sincronización de datos (rangos, municipios, tributos, unidades)
     * y muestra un log paso a paso dentro del modal.
     */
    async syncData() {
      this.isSyncing = true;
      this.syncLogs = [];

      try {
        // 1. Rangos de Numeración
        this.syncLogs.push('Iniciando sincronización de Rangos de Numeración...');
        const respNumbering = await axios.get('api/factus/sync/numbering-ranges');
        this.syncLogs.push(`Sincronizados ${respNumbering.data.count} rangos de numeración.`);

        // 2. Municipios
        this.syncLogs.push('Sincronizando Municipios...');
        const respMunicipalities = await axios.get('api/factus/sync/municipalities');
        this.syncLogs.push(`Sincronizados ${respMunicipalities.data.count} municipios.`);

        // 3. Tributos de Productos
        this.syncLogs.push('Sincronizando Tributos de Productos...');
        const respTributes = await axios.get('api/factus/sync/tributes');
        this.syncLogs.push(`Sincronizados ${respTributes.data.count} tributos de productos.`);

        // 4. Unidades de Medida
        this.syncLogs.push('Sincronizando Unidades de Medida...');
        const respUnits = await axios.get('api/factus/sync/measurement-units');
        this.syncLogs.push(`Sincronizadas ${respUnits.data.count} unidades de medida.`);

        this.syncLogs.push('Sincronización completada exitosamente.');

        // Actualiza los conteos locales tras la sincronización
        await this.loadLocalStats();
      } catch (error) {
        this.syncLogs.push('Error durante la sincronización: ' + error.message);
        console.error('Error sincronizando datos:', error);
      } finally {
        this.isSyncing = false;
      }
    }
  }
};
</script>

<style scoped>
.page {
  padding: 20px;
}

/* Cards */
.card {
  margin-bottom: 1rem;
}

/* Modal */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0,0,0,0.5);
  z-index: 1000;
  display: flex;
  align-items: center;
  justify-content: center;
}

.modal-content {
  background: #fff;
  padding: 30px;
  border-radius: 5px;
  text-align: center;
  width: 480px;
  max-width: 90%;
}

.spinner {
  margin-bottom: 20px;
}

.sync-logs {
  max-height: 200px;
  overflow-y: auto;
  text-align: left;
}
</style>
