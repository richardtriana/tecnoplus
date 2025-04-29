<template>
    <div>
      <h3>Listado de Órdenes (estado: {{ status }})</h3>
      <div v-if="loading">Cargando órdenes...</div>
      <div v-else>
        <table class="table table-hover">
          <thead>
            <tr>
              <th>ID</th>
              <th>Nro Factura</th>
              <th>Total Pago</th>
              <th>Cliente</th>
              <th>Fecha</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="order in orders" :key="order.id">
              <td>{{ order.id }}</td>
              <td>{{ order.bill_number }}</td>
              <td>{{ order.total_paid | currency }}</td>
              <td>{{ order.client }}</td>
              <td>{{ order.created_at | moment("DD-MM-YYYY h:mm:ss a") }}</td>
            </tr>
          </tbody>
        </table>
        <div v-if="orders.length === 0">
          No se encontraron órdenes.
        </div>
      </div>
    </div>
  </template>
  
  <script>
  import axios from 'axios';
  import moment from 'moment';
  
  export default {
    name: 'OrdersList',
    props: {
      // Recibimos 'status' para filtrar las órdenes (por ejemplo, 2 para facturas, 1 para pedidos)
      status: {
        type: Number,
        default: 1
      }
    },
    data() {
      return {
        orders: [],
        loading: false
      };
    },
    filters: {
      currency(value) {
        if (!value) return '$0.00';
        return '$' + parseFloat(value).toFixed(2);
      },
      moment(value, formatStr) {
        return moment(value).format(formatStr);
      }
    },
    methods: {
      fetchOrders() {
        this.loading = true;
        // Llama a la ruta pública para reimpresión (sin auth)
        // Ajusta la URL según tu estructura. Aquí usamos reprint-list
        axios
          .get(`api/orders/reprint-list?status=${this.status}`)
          .then(response => {
            this.orders = response.data.orders || [];
          })
          .catch(error => {
            console.error("Error al obtener las órdenes:", error);
          })
          .finally(() => {
            this.loading = false;
          });
      }
    },
    mounted() {
      this.fetchOrders();
    }
  };
  </script>
  
  <style scoped>
  table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 1rem;
  }
  th, td {
    padding: 8px;
    border: 1px solid #ddd;
    text-align: left;
  }
  </style>
  