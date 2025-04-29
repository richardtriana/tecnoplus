<template>
  <div class="document-list container-fluid">
    <h1 class="my-3">Listado de Documentos Soporte</h1>

    <!-- Botones para crear documento y nota de ajuste -->
    <div class="mb-3">
      <router-link class="btn btn-success mr-2" :to="{ name: 'CreateEditDocument' }">
        <i class="bi bi-plus-circle"></i> Crear Documento
      </router-link>
      <!-- El botón de Nota de Ajuste se mostrará sólo cuando se seleccione un documento creado -->
      <router-link
        v-if="selectedDocument"
        class="btn btn-info"
        :to="{ name: 'CreateAdjustmentNote', params: { support_document_id: selectedDocument.id } }"
      >
        <i class="bi bi-plus-circle-dotted"></i> Crear Nota de Ajuste
      </router-link>
    </div>

    <!-- Filtros de búsqueda -->
    <div class="card mb-3">
      <div class="card-body">
        <form @submit.prevent="applyFilters">
          <div class="form-row">
            <div class="form-group col-md-4">
              <label for="filterReference">Código de Referencia</label>
              <input
                type="text"
                id="filterReference"
                class="form-control"
                v-model="filters.reference_code"
                placeholder="Buscar por código de referencia"
              />
            </div>
            <div class="form-group col-md-4">
              <label for="filterProvider">Proveedor</label>
              <input
                type="text"
                id="filterProvider"
                class="form-control"
                v-model="filters.provider"
                placeholder="Buscar por proveedor"
              />
            </div>
            <div class="form-group col-md-3">
              <label for="filterDate">Fecha (YYYY-MM-DD)</label>
              <input
                type="date"
                id="filterDate"
                class="form-control"
                v-model="filters.date"
              />
            </div>
            <div class="form-group col-md-1 align-self-end">
              <button type="submit" class="btn btn-primary btn-block">Filtrar</button>
            </div>
          </div>
        </form>
      </div>
    </div>

    <!-- Tabla de documentos -->
    <div class="table-responsive">
      <table class="table table-bordered table-hover">
        <thead class="thead-dark">
          <tr>
            <th>ID</th>
            <th>Código de Referencia</th>
            <th>Proveedor</th>
            <th>Total</th>
            <th>Fecha de Creación</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="document in documents.data"
            :key="document.id"
            @click="selectDocument(document)"
            :class="{ 'table-active': selectedDocument && selectedDocument.id === document.id }"
          >
            <td>{{ document.id }}</td>
            <td>{{ document.reference_code }}</td>
            <td>{{ document.supplier }}</td>
            <td>${{ formatNumber(document.total) }}</td>
            <td>{{ formatDate(document.created_at) }}</td>
            <td>
              <button class="btn btn-sm btn-info mr-1" @click.stop="viewDocument(document.id)">
                <i class="bi bi-eye"></i>
              </button>
              <router-link :to="{ name: 'CreateEditDocument', params: { document_id: document.id } }" class="btn btn-sm btn-warning mr-1">
                <i class="bi bi-pencil"></i>
              </router-link>
              <button class="btn btn-sm btn-danger" @click.stop="deleteDocument(document.id)">
                <i class="bi bi-trash"></i>
              </button>
            </td>
          </tr>
          <tr v-if="documents.data.length === 0">
            <td colspan="6" class="text-center">No se encontraron registros</td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Paginación -->
    <nav v-if="documents.last_page > 1">
      <ul class="pagination justify-content-center">
        <li class="page-item" :class="{ disabled: !documents.prev_page_url }">
          <a class="page-link" href="#" @click.prevent="changePage(documents.current_page - 1)">Anterior</a>
        </li>
        <li
          class="page-item"
          v-for="page in pages"
          :key="page"
          :class="{ active: page === documents.current_page }"
        >
          <a class="page-link" href="#" @click.prevent="changePage(page)">{{ page }}</a>
        </li>
        <li class="page-item" :class="{ disabled: !documents.next_page_url }">
          <a class="page-link" href="#" @click.prevent="changePage(documents.current_page + 1)">Siguiente</a>
        </li>
      </ul>
    </nav>
  </div>
</template>

<script>
import axios from "axios";
import Swal from "sweetalert2";

export default {
  name: "DocumentList",
  data() {
    return {
      documents: {
        data: [],
        current_page: 1,
        last_page: 1,
        prev_page_url: null,
        next_page_url: null,
      },
      filters: {
        reference_code: "",
        provider: "",
        date: ""
      },
      selectedDocument: null, // Almacena el documento seleccionado para crear nota de ajuste
    };
  },
  computed: {
    pages() {
      const pages = [];
      for (let i = 1; i <= this.documents.last_page; i++) {
        pages.push(i);
      }
      return pages;
    },
  },
  mounted() {
    this.loadDocuments();
  },
  methods: {
    loadDocuments(page = 1) {
      const params = {
        page,
        reference_code: this.filters.reference_code,
        provider: this.filters.provider,
        date: this.filters.date
      };
      axios
        .get("api/support-documents", { params, ...this.$root.config })
        .then((response) => {
          this.documents = response.data.documents;
          // Limpiar documento seleccionado cuando se recargue la lista
          this.selectedDocument = null;
        })
        .catch((error) => {
          console.error("Error al cargar documentos:", error);
          Swal.fire({
            icon: "error",
            title: "Error",
            text: "No se pudieron cargar los documentos."
          });
        });
    },
    applyFilters() {
      this.loadDocuments(1);
    },
    changePage(page) {
      if (page < 1 || page > this.documents.last_page) return;
      this.loadDocuments(page);
    },
    formatNumber(value) {
      return Number(value).toLocaleString('es-CO', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
      });
    },
    formatDate(dateStr) {
      const options = { year: 'numeric', month: '2-digit', day: '2-digit', hour: '2-digit', minute: '2-digit' };
      return new Date(dateStr).toLocaleDateString('es-CO', options);
    },
    viewDocument(id) {
      this.$router.push({ name: 'CreateEditDocument', params: { document_id: id } });
    },
    deleteDocument(id) {
      Swal.fire({
        title: "¿Estás seguro?",
        text: "Esta acción no se puede deshacer",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Sí, eliminar",
        cancelButtonText: "Cancelar"
      }).then((result) => {
        if (result.isConfirmed) {
          axios
            .delete(`api/support-documents/${id}`, this.$root.config)
            .then(() => {
              Swal.fire({
                icon: "success",
                title: "Eliminado",
                text: "El documento fue eliminado exitosamente",
                timer: 1500,
                showConfirmButton: false
              });
              this.loadDocuments(this.documents.current_page);
            })
            .catch((error) => {
              console.error("Error al eliminar documento:", error);
              Swal.fire({
                icon: "error",
                title: "Error",
                text: "No se pudo eliminar el documento"
              });
            });
        }
      });
    },
    // Selecciona un documento para que se habilite el botón de Nota de Ajuste
    selectDocument(document) {
      this.selectedDocument = document;
    },
  },
};
</script>

<style scoped>
.document-list {
  font-size: 1rem;
}

.table th,
.table td {
  vertical-align: middle;
  cursor: pointer;
}

.table-active {
  background-color: #e9ecef;
}

.pagination .page-item.active .page-link {
  background-color: #007bff;
  border-color: #007bff;
}
</style>
