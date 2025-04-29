<template>
    <div>
      <h2>Utilidad de Facturas</h2>
      <!-- Se carga la lista de órdenes filtradas por estado 2 -->
      <OrdersList :status="2" />
      <button class="btn btn-primary mt-3" @click="printInvoice">
        Imprimir Factura Seleccionada
      </button>
    </div>
  </template>
  
  <script>
  import OrdersList from "./OrdersList.vue";
  export default {
    name: "FacturasUtil",
    components: {
      OrdersList
    },
    methods: {
      printInvoice() {
        // Aquí implementa la lógica para imprimir la factura seleccionada.
        // Por ejemplo, podrías emitir un evento o llamar a un servicio.
        alert("Imprimir factura (lógica a implementar)");
      }
    }
  };
  </script>
  
  <style scoped>
  /* Estilos específicos para FacturasUtil */
  </style>
  