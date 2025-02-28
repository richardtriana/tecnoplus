<template>
	<div class="kitchen-container w-100 p-3" ref="kitchenContainer">
	  <!-- ENCABEZADO -->
	  <header class="page-header d-flex justify-content-between align-items-center mb-3">
		<!-- TÍTULO: Cocina + Pendientes en rojo -->
		<h3 class="display-4 mb-0">
		  Cocina
		  <span class="ms-3 text-danger" style="font-size: 1em;">
			Pendientes: {{ pendingCount }}
		  </span>
		</h3>
  
		<!-- BOTONES -->
		<div class="d-flex align-items-center">
		  <!-- Botón para pantalla completa (color warning) -->
		  <button class="btn btn-warning me-2" @click="toggleFullScreen">
			Pantalla Completa
		  </button>
  
		  <!-- Botón Recargar -->
		  <button class="btn btn-primary" @click="getOrders(OrderList.current_page, true)" v-if="$root.validatePermission('kitchen.index')">
			Recargar
		  </button>
		</div>
	  </header>
  
	  <!-- CONTENIDO PRINCIPAL -->
	  <section>
		<div class="row">
		  <!-- Iteramos las órdenes -->
		  <div
			v-for="o in OrderList.data"
			:key="o.id"
			class="col-12 mb-3"
		  >
			<!-- Tarjeta con estilo personalizado -->
			<div
			  class="order-card p-3 d-flex flex-column flex-md-row align-items-center justify-content-between"
			  :class="{
				'blink-red': isOldOrder(o), 
				'blink-green': !isOldOrder(o)
			  }"
			>
			  <!-- Izquierda: Detalles del pedido -->
			  <div class="left-info">
				<!-- Lista de productos -->
				<div
				  v-for="p in o.detail_orders"
				  :key="p.id"
				  class="mb-3"
				>
				  <div class="d-flex align-items-center">
					<!-- Cantidad en un círculo, con mayor separación (me-4) -->
					<span class="quantity-circle me-4">
					  {{ p.quantity }}
					</span>
					<!-- Nombre del producto (tachado si deleted_at != null) -->
					<span
					  class="product-name"
					  :class="{'deleted-product': p.deleted_at}"
					>
					  {{ p.product }}
					</span>
				  </div>
				  <!-- Observaciones del producto, si existen (en negrita) -->
				  <div
					v-if="p.observaciones"
					class="product-observations mt-1"
				  >
					* <strong>{{ p.observaciones }}</strong>
				  </div>
				</div>
			  </div>
  
			  <!-- Derecha: Info de la mesa, mesero, tiempos -->
			  <div class="right-info text-center mt-3 mt-md-0">
				<!-- Mesa -->
				<h4 class="table-name mb-2">
				  <strong>{{ o.table ? o.table.table : 'Sin Mesa' }}</strong>
				</h4>
  
				<!-- Mesero (mismo estilo que la mesa) -->
				<h4 class="table-name mb-3">
				  Mesero:
				  <strong>
					<!-- IMPORTANTE: Se requiere Order::with('user') en el backend -->
					<span v-if="o.user && o.user.name">
					  {{ o.user.name }}
					</span>
					<span v-else>
					  #{{ o.user_id }}
					</span>
				  </strong>
				</h4>
  
				<!-- Hora de creación -->
				<div class="text-muted mb-3">
				  {{ o.created_at | moment("DD-MM-YYYY h:mm:ss a") }}
				</div>
  
				<!-- Botón de preparado -->
				<button
				  class="btn btn-success w-100 mb-2"
				  style="font-size: 1.2rem;"
				  @click="prepareOrder(o.id)"
				>
				  Preparado
				</button>
  
				<!-- Contador de espera -->
				<div class="wait-time mt-2">
				  <span v-if="timeCounters[o.id]" class="fs-3 fw-bold text-danger">
					Espera <br> {{ timeCounters[o.id] }}
				  </span>
				</div>
			  </div>
			</div>
		  </div>
		</div>
  
		<!-- Paginación -->
		<pagination :align="'center'" :data="OrderList" :limit="8" @pagination-change-page="getOrders">
		  <span slot="prev-nav"><i class="bi bi-chevron-double-left"></i></span>
		  <span slot="next-nav"><i class="bi bi-chevron-double-right"></i></span>
		</pagination>
	  </section>
	</div>
</template>
  
<script>
import moment from 'moment';
  
export default {
	data() {
	  return {
		OrderList: {},
		filter: {
		  from: "",
		  to: "",
		  nro_results: 15
		},
		refreshInterval: null,
		timeCounters: {},
		isFullScreen: false
	  };
	},
	created() {
	  this.$root.validateToken();
	  this.getOrders(1, true);
	  // Auto-refresco cada 20 segundos
	  this.refreshInterval = setInterval(() => {
		this.getOrders(this.OrderList.current_page || 1, false);
	  }, 20000);
  
	  // Actualiza los contadores de tiempo cada segundo
	  setInterval(() => {
		this.updateTimeCounters();
	  }, 1000);
	},
	beforeDestroy() {
	  if (this.refreshInterval) {
		clearInterval(this.refreshInterval);
	  }
	},
	computed: {
	  // Cantidad de órdenes pendientes (state=1, proccess=0)
	  pendingCount() {
		if (!this.OrderList.data) return 0;
		return this.OrderList.data.filter(o => o.state === 1 && o.proccess === 0).length;
	  }
	},
	methods: {
	  getOrders(page = 1, showAlert = true) {
		let data = {
		  page: page,
		  from: this.filter.from,
		  to: this.filter.to,
		  nro_results: this.filter.nro_results
		};
  
		axios
		  .get(`api/orders/kitchen`, { params: data, headers: this.$root.config.headers })
		  .then((response) => {
			this.OrderList = response.data.orders;
			// Inicializa los contadores para las órdenes recién cargadas
			this.initTimeCounters();
			if (showAlert) {
			  Swal.fire({
				icon: "success",
				title: "Excelente",
				text: "Los datos se han cargado correctamente",
			  });
			}
		  })
		  .catch((error) => {
			console.log("error", error);
			Swal.fire({
			  icon: "error",
			  title: "Oops...",
			  text: "Hubo un error al cargar los datos",
			});
		  });
	  },
  
	  prepareOrder(order_id) {
		axios
		  .put(`api/orders/kitchen/${order_id}`, null, this.$root.config)
		  .then(() => {
			this.getOrders(1, true);
			Swal.fire({
			  icon: "success",
			  title: "Excelente",
			  text: "Los datos se han actualizado correctamente",
			});
		  })
		  .catch((error) => {
			Swal.fire({
			  icon: "error",
			  title: "Oops...",
			  text: "Hubo un error al actualizar la orden",
			});
		  });
	  },
  
	  // Determina si la orden tiene más de 20 minutos
	  isOldOrder(order) {
		const created = moment(order.created_at);
		const now = moment();
		return now.diff(created, "minutes") > 20;
	  },
  
	  // Inicializa los contadores de tiempo para cada orden
	  initTimeCounters() {
		if (!this.OrderList.data) return;
		this.OrderList.data.forEach((o) => {
		  this.timeCounters[o.id] = "00:00:00";
		});
		this.updateTimeCounters(); // Calcula inmediatamente
	  },
  
	  // Actualiza los contadores de tiempo en tiempo real
	  updateTimeCounters() {
		if (!this.OrderList.data) return;
		this.OrderList.data.forEach((o) => {
		  const diffInSeconds = moment().diff(moment(o.created_at), "seconds");
		  this.timeCounters[o.id] = this.formatTime(diffInSeconds);
		});
	  },
  
	  // Convierte segundos a formato HH:MM:SS
	  formatTime(totalSeconds) {
		const hours = Math.floor(totalSeconds / 3600);
		const minutes = Math.floor((totalSeconds % 3600) / 60);
		const seconds = totalSeconds % 60;
		return `${this.pad(hours)}:${this.pad(minutes)}:${this.pad(seconds)}`;
	  },
	  // Añade cero a la izquierda si < 10
	  pad(num) {
		return num < 10 ? "0" + num : num;
	  },
  
	  // Activar/Desactivar pantalla completa
	  toggleFullScreen() {
		if (!this.isFullScreen) {
		  // Solicita pantalla completa al contenedor
		  const elem = this.$refs.kitchenContainer;
		  if (elem.requestFullscreen) {
			elem.requestFullscreen();
		  } else if (elem.webkitRequestFullscreen) {
			elem.webkitRequestFullscreen();
		  } else if (elem.msRequestFullscreen) {
			elem.msRequestFullscreen();
		  }
		  this.isFullScreen = true;
		} else {
		  // Salir de pantalla completa
		  if (document.exitFullscreen) {
			document.exitFullscreen();
		  } else if (document.webkitExitFullscreen) {
			document.webkitExitFullscreen();
		  } else if (document.msExitFullscreen) {
			document.msExitFullscreen();
		  }
		  this.isFullScreen = false;
		}
	  }
	},
	filters: {
	  moment(value, format) {
		return moment(value).format(format);
	  }
	}
  };
</script>
  
<style scoped>
.kitchen-container {
	background: #f8f9fa;
	min-height: 100vh;
}
  
/* Tarjeta de cada orden */
.order-card {
	border: 2px solid #28a745; /* verde por defecto */
	border-radius: 12px;
	background: #fff;
	transition: background-color 0.5s;
	font-size: 1.3rem; /* Texto base un poco grande */
}
  
/* Clases para parpadeo (pulse) en verde/rojo */
@keyframes pulseRed {
	0% {
	  box-shadow: 0 0 10px rgba(255, 0, 0, 0.3);
	}
	50% {
	  box-shadow: 0 0 20px rgba(255, 0, 0, 0.6);
	}
	100% {
	  box-shadow: 0 0 10px rgba(255, 0, 0, 0.3);
	}
}
@keyframes pulseGreen {
	0% {
	  box-shadow: 0 0 10px rgba(0, 255, 0, 0.3);
	}
	50% {
	  box-shadow: 0 0 20px rgba(0, 255, 0, 0.6);
	}
	100% {
	  box-shadow: 0 0 10px rgba(0, 255, 0, 0.3);
	}
}
.blink-red {
	animation: pulseRed 2s infinite;
	border-color: red !important;
}
.blink-green {
	animation: pulseGreen 2s infinite;
	border-color: green !important;
}
  
/* Parte izquierda (productos) */
.left-info {
	flex: 1;
	font-size: 1.5rem; /* Texto grande */
}
  
/* Cantidad en un círculo */
.quantity-circle {
	display: inline-block;
	background: #0056b3;
	color: #fff;
	font-weight: bold;
	border-radius: 50%;
	width: 3rem;
	height: 3rem;
	text-align: center;
	line-height: 3rem;
	font-size: 1.4rem;
}
  
/* Nombre del producto */
.product-name {
	font-weight: bold;
	color: #0b5394;
}
  
/* Si el producto está eliminado, tachado en rojo */
.deleted-product {
	text-decoration: line-through;
	text-decoration-color: red;
}
  
/* Observaciones del producto (en negrita) - aumentado a 1.5rem */
.product-observations {
	margin-left: 4.5rem; /* más espacio para separarlo de la cantidad */
	font-size: 1.5rem;
	color: #b10f0f; /* color rojo oscuro */
	font-weight: bold;
}
  
/* Parte derecha (mesa, mesero, tiempo) */
.right-info {
	min-width: 200px;
}
  
/* Estilo para la mesa y el mesero (mismo color/tamaño) */
.table-name {
	font-size: 1.6rem;
	color: #007bff;
	margin-bottom: 0;
}
  
/* Espera (tiempo en rojo, grande) */
.wait-time {
	color: #dc3545; /* rojo */
}
.fs-3 {
	font-size: 1.75rem;
}
.fw-bold {
	font-weight: bold;
}
</style>
