<template>
    <div class="container py-4">
      <h2 class="mb-4">Recepción de Documentos - Factus</h2>
  
      <!-- Sección Cargar Factura -->
      <section class="card mb-4">
        <div class="card-header">Cargar Factura Electrónica</div>
        <div class="card-body">
          <form @submit.prevent="uploadInvoice">
            <div class="mb-3">
              <label for="trackId" class="form-label">CUFE (track_id)</label>
              <input
                type="text"
                id="trackId"
                v-model="uploadForm.track_id"
                class="form-control"
                required
              />
            </div>
            <div class="mb-3">
              <label for="invoiceFile" class="form-label">Archivo (PDF/XML)</label>
              <input
                type="file"
                id="invoiceFile"
                @change="handleFileUpload"
                class="form-control"
                required
              />
            </div>
            <button type="submit" class="btn btn-primary">Cargar Factura</button>
          </form>
        </div>
      </section>
  
      <!-- Sección Emitir Evento -->
      <section class="card mb-4">
        <div class="card-header">Emitir Evento en Factura</div>
        <div class="card-body">
          <form @submit.prevent="emitEvent">
            <div class="mb-3">
              <label for="billId" class="form-label">ID de Factura</label>
              <input
                type="text"
                id="billId"
                v-model="eventForm.bill_id"
                class="form-control"
                required
              />
            </div>
            <div class="mb-3">
              <label for="eventType" class="form-label">Tipo de Evento</label>
              <select
                id="eventType"
                v-model="eventForm.event_type"
                class="form-select"
                required
              >
                <option value="" disabled>Seleccione un evento</option>
                <option
                  v-for="event in eventTypes"
                  :key="event.id"
                  :value="event.code"
                >
                  {{ event.name }}
                </option>
              </select>
            </div>
            <!-- Campos adicionales para datos de la persona que realiza el evento -->
            <div class="mb-3">
              <label for="firstName" class="form-label">Nombre</label>
              <input
                type="text"
                id="firstName"
                v-model="eventForm.first_name"
                class="form-control"
                required
              />
            </div>
            <div class="mb-3">
              <label for="lastName" class="form-label">Apellido</label>
              <input
                type="text"
                id="lastName"
                v-model="eventForm.last_name"
                class="form-control"
                required
              />
            </div>
            <!-- Puedes agregar más campos según lo requiera la documentación -->
            <button type="submit" class="btn btn-success">Emitir Evento</button>
          </form>
        </div>
      </section>
  
      <!-- Sección Ver Facturas -->
      <section class="card mb-4">
        <div class="card-header">Ver Facturas</div>
        <div class="card-body">
          <form @submit.prevent="getInvoices">
            <div class="row g-3 mb-3">
              <div class="col-md-3">
                <label for="filterId" class="form-label">ID</label>
                <input
                  type="text"
                  id="filterId"
                  class="form-control"
                  v-model="filters.id"
                  placeholder="ID"
                />
              </div>
              <div class="col-md-3">
                <label for="filterNumber" class="form-label">Número de Factura</label>
                <input
                  type="text"
                  id="filterNumber"
                  class="form-control"
                  v-model="filters.number"
                  placeholder="Número"
                />
              </div>
              <div class="col-md-3">
                <label for="filterIssueDate" class="form-label">Fecha de Emisión</label>
                <input
                  type="date"
                  id="filterIssueDate"
                  class="form-control"
                  v-model="filters.issue_date"
                />
              </div>
              <div class="col-md-3">
                <label for="filterCUFE" class="form-label">CUFE</label>
                <input
                  type="text"
                  id="filterCUFE"
                  class="form-control"
                  v-model="filters.cufe"
                  placeholder="CUFE"
                />
              </div>
            </div>
            <div class="row g-3 mb-3">
              <div class="col-md-3">
                <label for="filterCompanyNit" class="form-label">NIT Emisor</label>
                <input
                  type="text"
                  id="filterCompanyNit"
                  class="form-control"
                  v-model="filters.company_nit"
                  placeholder="NIT"
                />
              </div>
              <div class="col-md-3">
                <label for="filterCompanyName" class="form-label">Nombre Emisor</label>
                <input
                  type="text"
                  id="filterCompanyName"
                  class="form-control"
                  v-model="filters.company_name"
                  placeholder="Nombre"
                />
              </div>
              <div class="col-md-3">
                <label for="filterCompletedEvents" class="form-label">Eventos Completados</label>
                <select
                  id="filterCompletedEvents"
                  class="form-select"
                  v-model="filters.completed_events"
                >
                  <option value="">Todos</option>
                  <option value="1">Sin eventos pendientes</option>
                  <option value="0">Con eventos pendientes</option>
                </select>
              </div>
              <div class="col-md-3 d-flex align-items-end">
                <button type="submit" class="btn btn-success w-100">Buscar Facturas</button>
              </div>
            </div>
          </form>
          <div v-if="invoices.length">
            <table class="table table-striped">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Número</th>
                  <th>Fecha de Emisión</th>
                  <th>CUFE</th>
                  <th>NIT Emisor</th>
                  <th>Nombre Emisor</th>
                  <th>Total</th>
                  <th>Creado</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="invoice in invoices" :key="invoice.ID">
                  <td>{{ invoice.ID }}</td>
                  <td>{{ invoice.number }}</td>
                  <td>{{ invoice.issue_date }} {{ invoice.issue_time }}</td>
                  <td>{{ invoice.cufe }}</td>
                  <td>{{ invoice.company_nit }}</td>
                  <td>{{ invoice.company_name }}</td>
                  <td>{{ invoice.total | currency }}</td>
                  <td>{{ invoice.created_at }}</td>
                </tr>
              </tbody>
            </table>
          </div>
          <div v-else class="text-center">
            <p>No se encontraron facturas.</p>
          </div>
        </div>
      </section>
    </div>
  </template>
  
  <script>
  import axios from 'axios';
  import Swal from 'sweetalert2';
  
  export default {
    name: "ReceptionDocuments",
    data() {
      return {
        // Formulario para cargar factura
        uploadForm: {
          track_id: '',
          file: null,
        },
        // Formulario para emitir evento
        eventForm: {
          bill_id: '',
          event_type: '',
          first_name: '',
          last_name: '',
          // Otros campos según lo requiera la API
        },
        // Filtros para consultar facturas
        filters: {
          id: '',
          number: '',
          issue_date: '',
          cufe: '',
          company_nit: '',
          company_name: '',
          completed_events: '',
        },
        invoices: [],
        eventTypes: [] // Se poblará desde la API (modelo EventCode)
      };
    },
    created() {
      this.getEventCodes();
    },
    methods: {
      getEventCodes() {
        // Se asume que existe un endpoint en la API para obtener los tipos de evento, por ejemplo: GET /api/event-codes
        axios.get('api/event-codes')
          .then(response => {
            // Suponemos que la respuesta tiene un formato { data: [...] }
            this.eventTypes = response.data.data || [];
          })
          .catch(error => {
            console.error("Error al obtener los tipos de evento:", error);
            Swal.fire({
              icon: 'error',
              title: 'Error',
              text: 'Error al obtener los tipos de evento'
            });
          });
      },
      handleFileUpload(event) {
        this.uploadForm.file = event.target.files[0];
      },
      uploadInvoice() {
        const formData = new FormData();
        formData.append('track_id', this.uploadForm.track_id);
        formData.append('file', this.uploadForm.file);
  
        axios.post('api/factus-receptions/upload', formData, {
          headers: {
            'Content-Type': 'multipart/form-data'
          }
        })
        .then(response => {
          Swal.fire({
            icon: 'success',
            title: 'Éxito',
            text: 'Factura cargada exitosamente'
          });
          // Limpia el formulario si es necesario
          this.uploadForm.track_id = '';
          this.uploadForm.file = null;
        })
        .catch(error => {
          console.error("Error al cargar la factura:", error);
          Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Error al cargar la factura'
          });
        });
      },
      emitEvent() {
        const { bill_id, event_type, first_name, last_name } = this.eventForm;
        // Prepara los datos del evento. Ajusta los campos según lo que requiera la API.
        const data = {
          identification_document_code: '01', // Ejemplo; deberás obtener este valor según tu lógica
          identification: '123456789',          // Ejemplo
          first_name,
          last_name,
          // Agrega otros campos requeridos (por ejemplo, claim_concept_code si es reclamo)
        };
  
        axios.post(`api/factus-receptions/bills/${bill_id}/radian/events/${event_type}`, data)
          .then(response => {
            Swal.fire({
              icon: 'success',
              title: 'Éxito',
              text: 'Evento emitido exitosamente'
            });
            // Limpia el formulario de evento si es necesario
            this.eventForm.bill_id = '';
            this.eventForm.event_type = '';
            this.eventForm.first_name = '';
            this.eventForm.last_name = '';
          })
          .catch(error => {
            console.error("Error al emitir evento:", error);
            Swal.fire({
              icon: 'error',
              title: 'Error',
              text: 'Error al emitir evento'
            });
          });
      },
      getInvoices() {
        // Construye los parámetros de consulta en el formato que espera la API
        axios.get('api/factus-receptions/bills', {
          params: {
            'filter[id]': this.filters.id,
            'filter[number]': this.filters.number,
            'filter[issue_date]': this.filters.issue_date,
            'filter[cufe]': this.filters.cufe,
            'filter[company_nit]': this.filters.company_nit,
            'filter[company_name]': this.filters.company_name,
            'filter[completed_events]': this.filters.completed_events,
          }
        })
        .then(response => {
          // Suponemos que la respuesta viene con la estructura { data: { data: [...] } }
          this.invoices = response.data.data.data || [];
        })
        .catch(error => {
          console.error("Error al obtener facturas:", error);
          Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Error al obtener facturas'
          });
        });
      }
    },
    filters: {
      currency(value) {
        if (typeof value !== "number") {
          const num = parseFloat(value);
          if (isNaN(num)) return value;
          value = num;
        }
        return new Intl.NumberFormat("es-CO", {
          style: "currency",
          currency: "COP"
        }).format(value);
      }
    }
  };
  </script>
  
  <style scoped>
  .container {
    max-width: 800px;
  }
  .card {
    margin-bottom: 1.5rem;
  }
  </style>
  