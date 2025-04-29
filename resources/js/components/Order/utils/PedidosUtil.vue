<template>
    <div>
      <h2>Utilidad de Pedidos / Pre-cuenta</h2>
      <!-- Se carga la lista de órdenes filtradas por estado 1 -->
      <OrdersList :status="1" />
      <button class="btn btn-primary mt-3" @click="printOrder">
        Imprimir Pedido / Pre-cuenta Seleccionado
      </button>
    </div>
  </template>
  
  <script>
  import OrdersList from "./OrdersList.vue";
  export default {
    name: "PedidosUtil",
    components: {
      OrdersList
    },
    methods: {
      printOrder() {
        // Aquí implementa la lógica para imprimir el pedido o precuenta.
        alert("Imprimir pedido / precuenta (lógica a implementar)");
      }
    }
  };
  </script>
  
  <style scoped>
  /* Estilos específicos para PedidosUtil */
  </style>
  