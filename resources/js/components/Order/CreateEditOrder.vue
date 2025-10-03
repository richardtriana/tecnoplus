<template>
  <div class="row px-2" id="create-edit-order">
    <!-- Overlay de Loading -->
    <!-- Lo mantenemos en el template pero ya no lo usamos en el script -->
    <div v-if="loading" class="loading-overlay">
      <div class="spinner-border text-primary" role="status">
        <span class="sr-only">Cargando...</span>
      </div>
      <p>Cargando, por favor espere...</p>
    </div>

    <!-- PANEL LATERAL IZQUIERDO: Totales, Forma de Pago, etc. -->
    <div class="col-md-3">
      <section class="card">
        <!-- Estado de envío a DIAN -->
        <div class="alert m-2" :class="enviadoDianAlertClass" role="alert">
          <strong>Estado de envío a DIAN:</strong>
          <span v-if="selectedVoucherInfo">
            <span :style="{ color: selectedVoucherInfo.enviado_dian ? 'green' : 'red' }">
              {{ selectedVoucherInfo.enviado_dian ? 'Se enviará a DIAN' : 'No se enviará a DIAN' }}
            </span>
          </span>
          <span v-else class="text-muted">
            (No se ha seleccionado comprobante)
          </span>
        </div>

        <!-- Tabla de Subtotal, IVA, etc. -->
        <div>
          <table class="table table-sm table-teal text-right">
            <tr>
              <th colspan="7">Subtotal:</th>
<<<<<<< HEAD
              <!-- Formato con separador de miles y 2 decimales -->
=======
>>>>>>> 0ed4468 (factuara electronica + reserva + caja)
              <th>
                $ {{ total_tax_exc.toLocaleString('es-CO', { minimumFractionDigits: 2 }) }}
              </th>
            </tr>
            <tr>
              <th colspan="7">IVA (total):</th>
              <th>
                $ {{ (total_tax_inc_without_discount - total_tax_exc).toLocaleString('es-CO', { minimumFractionDigits: 2 }) }}
              </th>
            </tr>
<<<<<<< HEAD
            <!-- Desglose de IVA -->
=======
>>>>>>> 0ed4468 (factuara electronica + reserva + caja)
            <tr v-for="(taxValue, rate) in taxBreakdown" :key="rate">
              <th colspan="7">IVA {{ rate }}%:</th>
              <th>
                $ {{ taxValue.toLocaleString('es-CO', { minimumFractionDigits: 2 }) }}
              </th>
            </tr>
            <tr>
              <th colspan="7">Descuento:</th>
              <th>
                $ {{ total_discount.toLocaleString('es-CO', { minimumFractionDigits: 2 }) }}
              </th>
            </tr>
<<<<<<< HEAD
            <!-- Fila total, usando la clase "total-row" -->
=======
>>>>>>> 0ed4468 (factuara electronica + reserva + caja)
            <tr class="total-row">
              <th colspan="7">Total:</th>
              <th>
                $ {{ total_tax_inc.toLocaleString('es-CO', { minimumFractionDigits: 2 }) }}
              </th>
            </tr>

            <!-- Forma de Pago -->
            <tr>
              <th colspan="7">Forma de Pago:</th>
              <th>
                <select class="form-control" v-model="order.payment_form_id" @change="onChangePaymentForm">
                  <option v-for="form in paymentForms" :key="form.id" :value="form.id">
                    {{ form.name }}
                  </option>
                </select>
              </th>
            </tr>

            <!-- Método de Pago: solo si code==1 (Pago de contado) -->
            <tr v-if="selectedPaymentForm && selectedPaymentForm.code == '1'">
              <th colspan="7">Método de Pago:</th>
              <th>
                <select class="form-control" v-model="order.payment_method_id">
                  <option v-for="method in paymentMethods" :key="method.id" :value="method.id">
                    {{ method.name }}
                  </option>
                </select>
              </th>
            </tr>

            <!-- Referencia de Pago: solo si NO es efectivo (code != '10') -->
            <tr
              v-if="
                selectedPaymentForm &&
                selectedPaymentForm.code == '1' &&
                selectedPaymentMethod &&
                selectedPaymentMethod.code != '10'
              "
            >
              <th colspan="7">Referencia de Pago:</th>
              <th>
                <input
                  type="text"
                  class="form-control"
                  v-model="order.payment_reference"
                  placeholder="Ingrese referencia de pago"
                />
              </th>
            </tr>

            <tr>
              <th colspan="7">Observaciones:</th>
              <th>
                <input type="text" class="form-control" v-model="order.observations" autocomplete="on" />
              </th>
            </tr>

            <!-- Comprobante -->
            <tr>
              <th colspan="7">Comprobante:</th>
              <th>
                <select class="form-control" v-model="selectedVoucher" v-if="activeVouchers.length">
                  <option v-for="voucher in activeVouchers" :key="voucher.id" :value="voucher.id">
                    {{ voucher.document }} - {{ voucher.prefix }}
                  </option>
                </select>
                <p class="text-muted" v-else>
                  No hay comprobantes activos
                </p>
              </th>
            </tr>
          </table>
        </div>
      </section>

      <!-- BOTONES DE ACCIÓN: 3 por fila, más grandes, con separación -->
      <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 g-2 mt-2">
        <!-- Botón Facturar (F1) -->
        <div class="col">
          <button
            type="button"
            class="btn btn-lg btn-aqua w-100"
            @click="facturarF1"
          >
            <i class="bi bi-receipt"></i>
            <b>F1</b>
            <br />
            Facturar
          </button>
        </div>
        <!-- Botón Pagar -->
        <div class="col">
          <router-link
            to="/orders"
<<<<<<< HEAD
=======
            type="button"
>>>>>>> 0ed4468 (factuara electronica + reserva + caja)
            class="btn btn-lg btn-secondary w-100 text-center"
          >
            <i class="bi bi-cart-x"></i>
            <br />
            Pagar
          </router-link>
        </div>
<<<<<<< HEAD

=======
>>>>>>> 0ed4468 (factuara electronica + reserva + caja)
        <!-- Botón Pedido -->
        <div class="col">
          <button
            type="button"
            class="btn btn-lg btn-outline-aqua w-100"
            @click="createOrUpdateOrder(1)"
          >
            <i class="bi bi-clock-fill"></i>
            <br />
            Pedido
          </button>
        </div>
<<<<<<< HEAD
=======
        <!-- Botón Reserva -->
        <div class="col">
          <button
            type="button"
            class="btn btn-lg btn-outline-aqua w-100"
            @click="openReservationModal"
          >
            <i class="bi bi-calendar-check"></i>
            <br />
            Reserva
          </button>
        </div>
>>>>>>> 0ed4468 (factuara electronica + reserva + caja)
        <!-- Botón Cotizar -->
        <div class="col">
          <button
            type="button"
            class="btn btn-lg btn-outline-aqua w-100"
            @click="createOrUpdateOrder(3)"
          >
            <i class="bi bi-list-check"></i>
            <br />
            Cotizar
          </button>
        </div>
        <!-- Botón Cancelar (solo si order_id != 0) -->
        <div class="col" v-if="order_id != 0">
          <router-link
            to="/orders"
<<<<<<< HEAD
=======
            type="button"
>>>>>>> 0ed4468 (factuara electronica + reserva + caja)
            class="btn btn-lg btn-outline-secondary w-100 text-center"
          >
            <i class="bi bi-cart-x"></i>
            <br />
            Cancelar
          </router-link>
        </div>
<<<<<<< HEAD
        <!-- Botón Recibos -->
        <div class="col">
          <router-link
            to="/orders"
=======
        <!-- Botón Comprobantes -->
        <div class="col">
          <router-link
            to="/orders"
            type="button"
>>>>>>> 0ed4468 (factuara electronica + reserva + caja)
            class="btn btn-lg btn-info w-100 text-center"
          >
            <i class="bi bi-file-text"></i>
            <br />
            Recibos
          </router-link>
<<<<<<< HEAD
=======
        </div>
        <!-- Botón Dividir cuenta -->
        <div class="col" v-if="order_id != 0 && order.state === 1">
          <button
            type="button"
            class="btn btn-warning btn-lg w-100"
            @click="openSplitModal"
          >
            <i class="bi bi-files"></i><br>
            Dividir cuenta
          </button>
>>>>>>> 0ed4468 (factuara electronica + reserva + caja)
        </div>
      </div>
    </div>

    <!-- PANEL PRINCIPAL DERECHO: Barra superior + Tabla de productos -->
    <div class="col-9 p-2">
      <!-- Barra Superior: Caja a la izquierda y TOTAL a la derecha -->
      <div class="row top-bar align-items-center px-3 py-2 mb-2">
        <!-- Info Caja -->
        <div class="col-auto">
          <h5 class="mb-0">
            Caja:
            <span v-if="selectedBoxData" class="font-weight-bold">
              {{ selectedBoxData.name }}
            </span>
            <span v-else class="text-muted">
              (No hay caja seleccionada)
            </span>
          </h5>
          <button class="btn btn-caja btn-sm mt-1" @click="openSelectBoxModal">
            <i class="bi bi-arrow-repeat"></i> Cambiar Caja
          </button>
        </div>

        <!-- Espacio flexible -->
        <div class="col"></div>

        <!-- Total Grande a la derecha -->
        <div class="col-auto text-right">
          <span class="big-total">
            $ {{ total_tax_inc.toLocaleString('es-CO', { minimumFractionDigits: 2 }) }}
          </span>
        </div>
      </div>

      <!-- Fila de búsqueda: productos, clientes y mesa -->
      <div class="row position-sticky sticky-top bg-light p-1 mb-2" style="top: 0.5rem">
        <!-- Buscador de producto -->
        <div class="input-group col-6">
          <input
            type="text"
            class="form-control"
            placeholder="Código de barras"
<<<<<<< HEAD
=======
            aria-label="with two button addons"
>>>>>>> 0ed4468 (factuara electronica + reserva + caja)
            aria-describedby="button-add-product"
            v-model="filters.product"
            autofocus
            @keypress.enter="searchProduct()"
          />
          <div class="input-group-append" id="button-add-product">
<<<<<<< HEAD
            <button class="btn btn-outline-secondary" @click="openAddProductModal">
=======
            <button class="btn btn-outline-secondary" type="button" @click="openAddProductModal">
>>>>>>> 0ed4468 (factuara electronica + reserva + caja)
              <b>F10</b> Añadir Producto
            </button>
            <button class="btn btn-outline-secondary" data-toggle="modal" data-target="#addProductModal">
              <i class="bi bi-card-checklist"></i>
            </button>
          </div>
        </div>
        <!-- Buscador de cliente -->
        <div class="input-group col-6">
          <input
            type="text"
            class="form-control"
            placeholder="Buscar Cliente"
<<<<<<< HEAD
=======
            aria-label="with two button addons"
>>>>>>> 0ed4468 (factuara electronica + reserva + caja)
            aria-describedby="button-addon4"
            v-model="filters.client"
            @keypress.enter="searchClient()"
          />
          <div class="input-group-append" id="button-addon4">
            <button class="btn btn-outline-secondary" @click="searchClient()">
              Añadir Cliente
            </button>
            <button class="btn btn-outline-secondary" data-toggle="modal" data-target="#addClientModal">
              <i class="bi bi-person-lines-fill"></i>
            </button>
          </div>
        </div>
        <!-- Selección de mesa -->
        <div class="input-group col-6 mt-2">
          <v-select
            :options="tableList"
            placeholder="Seleccionar mesa"
<<<<<<< HEAD
            label="table"
            :reduce="t => t.id"
            v-model="order.table_id"
            class="w-full form-input p-0 w-100"
=======
            class="w-full form-input p-0 w-100"
            label="table"
            :reduce="(table) => table.id"
            v-model="order.table_id"
>>>>>>> 0ed4468 (factuara electronica + reserva + caja)
          />
        </div>
      </div>

      <!-- Listado de productos en el pedido -->
      <section>
        <div>
          <table class="table table-sm table-responsive-sm table-bordered table-hover">
            <thead class="bg-secondary text-white position-sticky sticky-top" style="top: 6.6rem">
              <tr>
                <th></th>
                <th>Código</th>
                <th>Producto</th>
                <th>Cantidad</th>
                <th>Precio (sin IVA)</th>
                <th>Porcentaje IVA</th>
                <th>Descuento %</th>
                <th>Total</th>
              </tr>
            </thead>
            <tbody v-if="productsOrderList.length > 0">
              <tr v-for="(p, index) in productsOrderList" :key="p.id">
                <td>
<<<<<<< HEAD
                  <button class="btn text-danger" @click="removeProduct(index, p.id)"><i class="bi bi-trash"></i></button>
                  <button class="btn text-primary" @click="toggleObservaciones(index)"><i class="bi bi-pencil-square"></i></button>
                </td>
                <td>{{ p.barcode }}</td>
                <td>{{ p.product }}</td>
                <td><input type="number" step="any" class="form-control form-control-sm" v-model="p.quantity" style="max-width:60px" /></td>
                <td><input type="number" step="any" class="form-control form-control-sm" v-model="p.price_tax_exc" style="max-width:100px" /></td>
                <td class="text-right">{{ p.price_tax_exc>0?((p.price_tax_inc/p.price_tax_exc-1)*100).toFixed(2)+'%':'0%' }}</td>
                <td><input type="number" step="any" class="form-control form-control-sm" v-model="p.discount_percentage" style="max-width:60px" /></td>
                <td class="text-right">{{ ((p.price_tax_inc*p.quantity)-(p.price_tax_exc*p.quantity*(p.discount_percentage/100))).toLocaleString('es-CO',{minimumFractionDigits:2}) }}</td>
              </tr>
              <tr v-for="(p,index) in productsOrderList" v-if="p.showObservaciones" :key="'obs-'+p.id">
                <td colspan="8"><input type="text" class="form-control" placeholder="Observaciones del producto" v-model="p.observaciones" @input="updateObservaciones(index)" /></td>
=======
                  <button class="btn text-danger" @click="removeProduct(index, p.id)">
                    <i class="bi bi-trash"></i>
                  </button>
                  <button class="btn text-primary" @click="toggleObservaciones(index)">
                    <i class="bi bi-pencil-square"></i>
                  </button>
                </td>
                <td>{{ p.barcode }}</td>
                <td>{{ p.product }}</td>
                <td>
                  <input
                    type="number"
                    step="any"
                    placeholder="Cantidad"
                    class="form-control form-control-sm"
                    v-model="p.quantity"
                    style="max-width: 60px"
                  />
                </td>
                <td>
                  <input
                    type="number"
                    step="any"
                    placeholder="Precio sin IVA"
                    class="form-control form-control-sm"
                    v-model="p.price_tax_exc"
                    style="max-width: 100px"
                  />
                </td>
                <td class="text-right">
                  {{
                    p.price_tax_exc > 0
                      ? ((p.price_tax_inc / p.price_tax_exc - 1) * 100).toFixed(2) + '%'
                      : '0%'
                  }}
                </td>
                <td>
                  <input
                    type="number"
                    step="any"
                    placeholder="Desc %"
                    class="form-control form-control-sm"
                    v-model="p.discount_percentage"
                    style="max-width: 60px"
                  />
                </td>
                <td class="text-right">
                  {{

                    (
                      (p.price_tax_inc * p.quantity) -
                      (p.price_tax_exc * p.quantity * (p.discount_percentage / 100))
                    ).toLocaleString('es-CO', { minimumFractionDigits: 2 })
                  }}
                </td>
              </tr>
              <tr
                v-for="(p, index) in productsOrderList"
                :key="'obs-' + p.id"
                v-if="p.showObservaciones"
              >
                <td colspan="8">
                  <input
                    type="text"
                    class="form-control"
                    placeholder="Observaciones del producto"
                    v-model="p.observaciones"
                    @input="updateObservaciones(index)"
                  />
                </td>
>>>>>>> 0ed4468 (factuara electronica + reserva + caja)
              </tr>
            </tbody>
            <tbody v-else>
              <tr><td colspan="8">No se han añadido productos</td></tr>
            </tbody>
          </table>
        </div>
      </section>
    </div>

<<<<<<< HEAD
    <!-- MODAL DE PAGO -->
    <div class="modal fade" id="paymentModal" tabindex="-1" aria-labelledby="paymentModalLabel" aria-hidden="true">
      <div class="modal-dialog"><div class="modal-content">
        <div class="modal-header text-white bg-success">
          <h5 class="modal-title">Terminar Venta</h5> 
          <button type="button" class="close" data-dismiss="modal" aria-label="Close" @click="removeModalKeydown">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <div class="d-flex justify-content-around mb-3">
            <div class="text-center">
              <h5>Pago</h5>
              <template v-if="selectedPaymentMethod?.code==='10'">
                <input id="cashPayment" type="number" class="form-control form-control-lg text-center" v-model="order.payment_methods.cash" @keypress.enter="createOrUpdateOrder(2)" />
              </template>
              <template v-else>
                <input type="text" class="form-control form-control-lg text-center" v-model="order.payment_reference" placeholder="Ref. de pago" />
              </template>
            </div>
            <div class="text-center">
              <h5>Total</h5>
              <h3 class="text-primary">
                $ {{ total_tax_inc.toLocaleString('es-CO',{minimumFractionDigits:2}) }}
              </h3>
            </div>
          </div>
          <div class="text-center mb-3">
            <h2 class="text-success" style="font-size:2rem;">
              Cambio: $ {{ payment_return.toLocaleString('es-CO',{minimumFractionDigits:2}) }}
            </h2>
=======
    <!-- MODAL RESERVA -->
    <div
      class="modal fade"
      id="reservationModal"
      tabindex="-1"
      aria-labelledby="reservationModalLabel"
      aria-hidden="true"
      :class="{ show: showReservationModal }"
      :style="{ display: showReservationModal ? 'block' : 'none' }"
    >
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="reservationModalLabel">Nueva Reserva</h5>
            <button type="button" class="btn-close" @click="closeReservationModal"></button>
          </div>
          <div class="modal-body">
            <div class="mb-3">
              <label for="reservationAt" class="form-label">Fecha y hora</label>
              <input
                type="datetime-local"
                id="reservationAt"
                class="form-control"
                v-model="reservationAt"
              />
            </div>
            <div class="mb-3">
              <label for="reservationObservations" class="form-label">Observaciones</label>
              <textarea
                id="reservationObservations"
                class="form-control"
                v-model="reservationObservations"
              ></textarea>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" @click="closeReservationModal">
              Cancelar
            </button>
            <button
              type="button"
              class="btn btn-primary"
              @click="confirmReservation"
              :disabled="!reservationAt"
            >
              Guardar Reserva
            </button>
          </div>
        </div>
      </div>
    </div>
    <div
      class="modal-backdrop fade"
      :class="{ show: showReservationModal }"
      v-if="showReservationModal"
    ></div>

    <!-- MODAL DIVIDIR CUENTA -->
    <div
      class="modal fade"
      id="splitModal"
      tabindex="-1"
      aria-labelledby="splitModalLabel"
      aria-hidden="true"
      :class="{ show: showSplitModal }"
      :style="{ display: showSplitModal ? 'block' : 'none' }"
    >
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="splitModalLabel">Dividir cuenta</h5>
            <button type="button" class="btn-close" @click="closeSplitModal"></button>
          </div>
          <div class="modal-body">
            <p>Selecciona los productos y la cantidad a trasladar:</p>
            <table class="table table-sm table-hover">
              <thead class="bg-secondary text-white">
                <tr>
                  <th style="width: 2rem;"></th>
                  <th>Producto</th>
                  <th>Cantidad disponible</th>
                  <th>Cantidad a trasladar</th>
                  <th>Total</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="p in productsOrderList" :key="'split-'+p.id">
                  <td class="align-middle">
                    <input
                      type="checkbox"
                      class="form-check-input"
                      :value="p.id"
                      v-model="selectedSplitDetails"
                      :disabled="splitQuantities[p.id] === 0"
                    />
                  </td>
                  <td class="align-middle">{{ p.product }}</td>
                  <td class="align-middle">{{ p.quantity }}</td>
                  <td class="d-flex align-items-center">
                    <button
                      type="button"
                      class="btn btn-outline-secondary btn-sm"
                      @click="decrementSplit(p.id)"
                      :disabled="splitQuantities[p.id] <= 1"
                    >–</button>
                    <input
                      type="number"
                      class="form-control form-control-sm mx-1 text-center"
                      v-model.number="splitQuantities[p.id]"
                      :min="1"
                      :max="p.quantity"
                      style="width: 60px;"
                    />
                    <button
                      type="button"
                      class="btn btn-outline-secondary btn-sm"
                      @click="incrementSplit(p.id)"
                      :disabled="splitQuantities[p.id] >= p.quantity"
                    >+</button>
                  </td>
                  <td class="text-right align-middle">
                    {{
                      (
                        (p.price_tax_inc * splitQuantities[p.id]) -
                        (p.price_tax_exc * splitQuantities[p.id] * (p.discount_percentage/100))
                      ).toLocaleString('es-CO', { minimumFractionDigits: 2 })
                    }}
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
          <div class="modal-footer">
            <button
              type="button"
              class="btn btn-secondary"
              @click="closeSplitModal"
            >
              Cancelar
            </button>
            <button
              type="button"
              class="btn btn-primary"
              :disabled="!selectedSplitDetails.length"
              @click="confirmSplit"
            >
              Confirmar
            </button>
          </div>
        </div>
      </div>
    </div>
    <div class="modal-backdrop fade" :class="{ show: showSplitModal }" v-if="showSplitModal"></div>

    <!-- MODAL DE PAGO -->
    <div class="modal fade" id="paymentModal" tabindex="-1" aria-labelledby="paymentModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <!-- Encabezado verde -->
          <div class="modal-header text-white bg-success">
            <h5 class="modal-title">Terminar Venta</h5>
            <button
              type="button"
              class="close"
              data-dismiss="modal"
              aria-label="Close"
              @click="removeModalKeydown"
            >
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <!-- Cuerpo del modal con nuevo diseño -->
          <div class="modal-body">
            <div class="d-flex justify-content-around mb-3">
              <!-- Columna Pago -->
              <div class="text-center">
                <h5>Pago</h5>
                <!-- Si es efectivo (code '10'), mostramos input de pago -->
                <template v-if="selectedPaymentMethod && selectedPaymentMethod.code == '10'">
                  <input
                    id="cashPayment"
                    type="number"
                    class="form-control form-control-lg text-center"
                    v-model="order.payment_methods.cash"
                    @keypress.enter="createOrUpdateOrder(2)"
                  />
                </template>
                <!-- Si NO es efectivo, mostramos referencia de pago -->
                <template v-else>
                  <input
                    type="text"
                    class="form-control form-control-lg text-center"
                    v-model="order.payment_reference"
                    placeholder="Ref. de pago"
                  />
                </template>
              </div>
              <!-- Columna Total -->
              <div class="text-center">
                <h5>Total</h5>
                <h3 class="text-primary">
                  $ {{ total_tax_inc.toLocaleString('es-CO', { minimumFractionDigits: 2 }) }}
                </h3>
              </div>
            </div>

            <!-- Cambio en grande (verde, sin recuadro) -->
            <div class="text-center mb-3">
              <h2 class="text-success" style="font-size: 2rem;">
                Cambio: $ {{ payment_return.toLocaleString('es-CO', { minimumFractionDigits: 2 }) }}
              </h2>
            </div>
          </div>

          <!-- Footer del modal con botones grandes -->
          <div class="modal-footer justify-content-center">
            <button
              type="button"
              class="btn btn-primary btn-lg mx-2"
              @click="createOrUpdateOrder(2)"
            >
              <i class="bi bi-receipt"></i> Facturar (F1)
            </button>
            <button
              type="button"
              class="btn btn-success btn-lg mx-2"
              @click="createOrUpdateOrder(4)"
            >
              <i class="bi bi-printer"></i> Facturar Imprimir (F2)
            </button>
            <button
              type="button"
              class="btn btn-danger btn-lg mx-2"
              data-dismiss="modal"
              @click="removeModalKeydown"
            >
              Salir (Esc)
            </button>
>>>>>>> 0ed4468 (factuara electronica + reserva + caja)
          </div>
        </div>
        <div class="modal-footer justify-content-center">
          <button class="btn btn-primary btn-lg mx-2" @click="createOrUpdateOrder(2)"><i class="bi bi-receipt"></i> Facturar (F1)</button>
          <button class="btn btn-success btn-lg mx-2" @click="createOrUpdateOrder(4)"><i class="bi bi-printer"></i> Facturar Imprimir (F2)</button>
          <button class="btn btn-warning btn-lg mx-2" @click="openSplitModal"><i class="bi bi-columns-gap"></i> Dividir</button>
          <button class="btn btn-danger btn-lg mx-2" data-dismiss="modal" @click="removeModalKeydown">Salir (Esc)</button>
        </div>
      </div></div>
    </div>

<<<<<<< HEAD
    <!-- MODAL PARA DIVIDIR PEDIDO -->
    <div class="modal fade" :class="{ show: showSplitModal }" :style="{ display: showSplitModal ? 'block' : 'none' }" tabindex="-1" role="dialog" aria-hidden="true">
      <div class="modal-dialog modal-lg"><div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Dividir Pedido</h5>
          <button type="button" class="btn-close" @click="closeSplitModal"></button>
        </div>
        <div class="modal-body">
          <p>Selecciona los productos y cantidades a facturar por separado:</p>
          <table class="table table-sm">
            <thead>
              <tr><th>✔</th><th>Producto</th><th>Total</th><th>Cant. a facturar</th></tr>
            </thead>
            <tbody>
              <tr v-for="(p, idx) in productsOrderList" :key="idx">
                <td><input type="checkbox" v-model="splitSelection" :value="idx" /></td>
                <td>{{ p.product }}</td>
                <td>{{ p.quantity }}</td>
                <td>
                  <input
                    type="number"
                    class="form-control form-control-sm"
                    v-model.number="splitQuantities[idx]"
                    :disabled="!splitSelection.includes(idx)"
                    :max="p.quantity"
                    min="1"
                  />
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        <div class="modal-footer">
          <button class="btn btn-secondary" @click="closeSplitModal">Cancelar</button>
          <button class="btn btn-primary" @click="confirmSplit">Crear factura</button>
        </div>
      </div></div>
    </div>

=======
>>>>>>> 0ed4468 (factuara electronica + reserva + caja)
    <!-- Otros componentes: AddProduct, AddClient, ModalBox, SelectBoxModal -->
    <add-product @add-product="addProduct($event)" />
    <add-client @add-client="addClient($event)" />
    <modal-box ref="ModalBox"></modal-box>
    <select-box-modal ref="SelectBoxModal" @box-selected="onBoxSelected" />
  </div>
</template>

<script>
import AddProduct from "./AddProductPOS.vue";
import AddClient from "./AddClient.vue";
import ModalBox from "./../ModalBox.vue";
import SelectBoxModal from "./SelectBoxModal.vue";
import axios from "axios";
import Swal from "sweetalert2";

export default {
  name: "create-edit-order",
<<<<<<< HEAD
  components: { AddProduct, AddClient, ModalBox, SelectBoxModal },
=======
  components: {
    AddProduct,
    AddClient,
    ModalBox,
    SelectBoxModal
  },
>>>>>>> 0ed4468 (factuara electronica + reserva + caja)
  props: ["order_id"],
  data() {
    return {
      loading: false,
<<<<<<< HEAD
      filters: { product: "", client: "" },
=======
      filters: {
        product: "",
        client: ""
      },
>>>>>>> 0ed4468 (factuara electronica + reserva + caja)
      productsOrderList: [],
      tableList: [],
      disabled: false,
      paymentForms: [],
      paymentMethods: [],
      order: {
        id_client: 1,
        client: "Sin Cliente",
        state: 1,
        total_tax_inc: 0.0,
        total_tax_exc: 0.0,
        total_discount: 0.0,
        total_cost_price_tax_inc: 0.0,
        productsOrder: [],
        payment_date: "",
        proccess: true,
        payment_form_id: null,
        payment_method_id: null,
        payment_reference: "",
        numbering_range_id: null,
<<<<<<< HEAD
        payment_methods: { cash: 0.0, card: 0.0, nequi: 0.0, others: 0.0 }
      },
      selectedVoucher: null,
      showSplitModal: false,
      splitSelection: [],
      splitQuantities: []
=======
        payment_methods: {
          cash: 0.0,
          card: 0.0,
          nequi: 0.0,
          others: 0.0
        }
      },
      selectedVoucher: null,
      showSplitModal: false,
      selectedSplitDetails: [],
      splitQuantities: {},
      // NUEVOS para reserva
      showReservationModal: false,
      reservationAt: "",
      reservationObservations: ""
>>>>>>> 0ed4468 (factuara electronica + reserva + caja)
    };
  },
  computed: {
    selectedBoxData() {
      if (!this.$root.box || !this.$root.listBoxes) return null;
      return this.$root.listBoxes.find(b => b.id == this.$root.box) || null;
    },
    activeVouchers() {
      if (!this.selectedBoxData) return [];
<<<<<<< HEAD
      const active = this.selectedBoxData.numbering_ranges.filter(v => v.is_active == 1);
      if (!this.selectedVoucher && active.length) this.selectedVoucher = active[0].id;
      return active;
    },
    selectedVoucherInfo() {
      return this.activeVouchers.find(v => v.id == this.selectedVoucher) || null;
    },
    enviadoDianAlertClass() {
      if (!this.selectedVoucherInfo) return "alert alert-secondary";
      return this.selectedVoucherInfo.enviado_dian ? "alert alert-success" : "alert alert-danger";
=======
      if (!this.selectedBoxData.numbering_ranges) return [];
      const active = this.selectedBoxData.numbering_ranges.filter(v => v.is_active == 1);
      if (!this.selectedVoucher && active.length) {
        this.selectedVoucher = active[0].id;
      }
      return active;
    },
    selectedVoucherInfo() {
      if (!this.selectedVoucher) return null;
      return this.activeVouchers.find(v => v.id == this.selectedVoucher) || null;
    },
    enviadoDianAlertClass() {
      if (!this.selectedVoucherInfo) {
        return "alert alert-secondary";
      }
      return this.selectedVoucherInfo.enviado_dian
        ? "alert alert-success"
        : "alert alert-danger";
>>>>>>> 0ed4468 (factuara electronica + reserva + caja)
    },
    total_tax_exc() {
      return this.productsOrderList.reduce((sum, p) => sum + p.price_tax_exc * p.quantity, 0);
    },
    total_discount() {
<<<<<<< HEAD
      return this.productsOrderList.reduce((sum, p) =>
        sum + p.quantity * p.price_tax_exc * (p.discount_percentage / 100), 0);
    },
    total_tax_inc() {
      return this.productsOrderList.reduce((sum, p) =>
        sum + p.price_tax_inc * p.quantity - p.price_tax_exc * p.quantity * (p.discount_percentage / 100), 0);
=======
      return this.productsOrderList.reduce((sum, p) => sum + p.quantity * p.price_tax_exc * (p.discount_percentage / 100), 0);
    },
    total_tax_inc() {
      return this.productsOrderList.reduce((sum, p) =>
        sum +
        p.price_tax_inc * p.quantity -
        p.price_tax_exc * p.quantity * (p.discount_percentage / 100)
      , 0);
>>>>>>> 0ed4468 (factuara electronica + reserva + caja)
    },
    total_tax_inc_without_discount() {
      return this.productsOrderList.reduce((sum, p) => sum + p.quantity * p.price_tax_inc, 0);
    },
    taxBreakdown() {
      const taxes = {};
      this.productsOrderList.forEach(p => {
        let rate = p.price_tax_exc > 0 ? (p.price_tax_inc / p.price_tax_exc - 1) * 100 : 0;
        rate = parseFloat(rate.toFixed(2));
        const key = rate.toFixed(0);
        const lineSubtotal = p.price_tax_exc * p.quantity;
        const discountBase = lineSubtotal * (p.discount_percentage / 100);
        const baseAfterDiscount = lineSubtotal - discountBase;
        const lineTax = baseAfterDiscount * (rate / 100);
        taxes[key] = (taxes[key] || 0) + lineTax;
      });
      return taxes;
    },
    paid_value() {
      const m = this.order.payment_methods;
      return Number(m.cash) + Number(m.card) + Number(m.nequi) + Number(m.others);
    },
    payment_return() {
      return this.paid_value - this.total_tax_inc;
    },
    selectedPaymentForm() {
      return this.paymentForms.find(f => f.id == this.order.payment_form_id) || null;
    },
    selectedPaymentMethod() {
      return this.paymentMethods.find(m => m.id == this.order.payment_method_id) || null;
    }
  },
<<<<<<< HEAD
  watch: {
    productsOrderList(newList) {
      this.splitQuantities = newList.map(p => p.quantity);
    }
  },
  mounted() {
    const storedBox = localStorage.getItem("box_id");
    const storedVoucher = localStorage.getItem("selected_voucher");
    if (storedBox) { this.$root.box = storedBox; this.order.box_id = storedBox; }
    if (storedVoucher) { this.selectedVoucher = storedVoucher; this.order.numbering_range_id = storedVoucher; }
    if (this.order_id && this.order_id != 0) this.listItemsOrder();
    this.listTables(); this.loadPaymentForms(); this.loadPaymentMethods(); this.commands();
    if (!this.$root.box) this.$nextTick(() => this.$refs.SelectBoxModal?.openModal());
    else if (this.order.numbering_range_id) this.selectedVoucher = this.order.numbering_range_id;
    this.order.payment_form_id = 1; this.order.payment_method_id = 1;
    if (this.order.state == 1) {
      const pv = this.activeVouchers.find(v => v.document === "Pedido");
      if (pv) this.order.numbering_range_id = pv.id;
    }
  },
  methods: {
    openSelectBoxModal() { this.$refs.SelectBoxModal.openModal(); },
=======
  mounted() {
    const storedBox = localStorage.getItem("box_id");
    const storedVoucher = localStorage.getItem("selected_voucher");
    if (storedBox) {
      this.$root.box = storedBox;
      this.order.box_id = storedBox;
    }
    if (storedVoucher) {
      this.selectedVoucher = storedVoucher;
      this.order.numbering_range_id = storedVoucher;
    }
    if (this.order_id && this.order_id != 0) {
      this.listItemsOrder();
    }
    this.listTables();
    this.loadPaymentForms();
    this.loadPaymentMethods();
    this.commands();

    if (!this.$root.box) {
      this.$nextTick(() => {
        this.$refs.SelectBoxModal?.openModal();
      });
    } else if (this.order.numbering_range_id) {
      this.selectedVoucher = this.order.numbering_range_id;
    }

    this.order.payment_form_id = 1;
    this.order.payment_method_id = 1;
    if (this.order.state == 1) {
      const pv = this.activeVouchers.find(v => v.document === "Pedido");
      if (pv) this.order.numbering_range_id = pv.id;
    }
  },
  watch: {
    '$route.params.order_id': {
      immediate: true,
      handler(newId) {
        this.order_id = Number(newId);
        if (this.order_id) {
          this.listItemsOrder();
        }
      }
    }
  },
  methods: {
    openSelectBoxModal() {
      this.$refs.SelectBoxModal.openModal();
    },
>>>>>>> 0ed4468 (factuara electronica + reserva + caja)
    onBoxSelected({ boxId, voucherId }) {
      this.$root.box = boxId;
      localStorage.setItem("box_id", boxId);
      localStorage.setItem("selected_voucher", voucherId);
      this.order.box_id = boxId;
      this.order.numbering_range_id = voucherId;
      this.selectedVoucher = voucherId;
<<<<<<< HEAD
    },
    loadPaymentForms() {
      axios.get("api/payment_forms", this.$root.config)
        .then(res => this.paymentForms = res.data.payment_forms)
        .catch(console.error);
    },
    loadPaymentMethods() {
      axios.get("api/payment_methods", this.$root.config)
        .then(res => this.paymentMethods = res.data.payment_methods)
        .catch(console.error);
    },
    onChangePaymentForm() {
      if (this.selectedPaymentForm?.code == "2") {
        this.order.payment_method_id = null;
        this.order.payment_reference = "";
      } else if (!this.order.payment_method_id) {
        this.order.payment_method_id = 1;
      }
    },
    listItemsOrder() {
=======

      if (!this.selectedBoxData || !this.selectedBoxData.is_open) {
        return Swal.fire({
          title: 'Caja cerrada',
          text: 'Esta caja está cerrada. ¿Deseas abrirla?',
          icon: 'warning',
          showCancelButton: true,
          confirmButtonText: 'Sí, abrir caja',
          cancelButtonText: 'No'
        }).then(result => {
          if (result.isConfirmed) {
            this.$router.push({ name: 'cash-reconciliation' });
          } else {
            this.$root.box = null;
            localStorage.removeItem("box_id");
            localStorage.removeItem("selected_voucher");
            this.order.box_id = null;
            this.selectedVoucher = null;
          }
        });
      }

      this.listItemsOrder();
    },
    loadPaymentForms() {
      axios.get("api/payment_forms", this.$root.config)
        .then(res => this.paymentForms = res.data.payment_forms)
        .catch(console.error);
    },
    loadPaymentMethods() {
      axios.get("api/payment_methods", this.$root.config)
        .then(res => this.paymentMethods = res.data.payment_methods)
        .catch(console.error);
    },
    onChangePaymentForm() {
      if (this.selectedPaymentForm?.code == "2") {
        this.order.payment_method_id = null;
        this.order.payment_reference = "";
      } else if (!this.order.payment_method_id) {
        this.order.payment_method_id = 1;
      }
    },
    listItemsOrder() {
      if (!this.order_id) return;
>>>>>>> 0ed4468 (factuara electronica + reserva + caja)
      axios.get(`api/orders/${this.order_id}`, this.$root.config)
        .then(({ data }) => {
          this.order = data.order_information;
          this.order.id_client = this.order.client_id;
          this.order.client = this.order.client.razon_social || this.order.client.name;
          this.productsOrderList = data.order_details;
          this.order.payment_form_id = 1;
          this.order.payment_method_id = 1;
        })
        .catch(console.error);
    },
    listTables() {
      axios.get("api/tables/table-list?page=1", this.$root.config)
<<<<<<< HEAD
        .then(r => this.tableList = r.data.tables)
=======
        .then(resp => this.tableList = resp.data.tables)
>>>>>>> 0ed4468 (factuara electronica + reserva + caja)
        .catch(console.error);
    },
    searchProduct() {
      if (!this.filters.product) return;
<<<<<<< HEAD
      axios.post(`api/products/search-product?product=${this.filters.product}&state=1`, null, this.$root.config)
        .then(resp => resp.data.products ? this.addProduct(resp.data.products) : $("#no-results").toast("show"))
=======
      axios.post(
        `api/products/search-product?product=${this.filters.product}&state=1`,
        null,
        this.$root.config
      )
        .then(resp => {
          const np = resp.data.products;
          np ? this.addProduct(np) : $("#no-results").toast("show");
        })
>>>>>>> 0ed4468 (factuara electronica + reserva + caja)
        .catch(console.error);
      this.filters.product = "";
    },
    addProduct(new_product) {
      let exists = false;
      this.productsOrderList.forEach(prod => {
        if (prod.barcode === new_product.barcode) {
          exists = true;
          prod.quantity++;
          prod.price_tax_inc_total = prod.price_tax_inc * prod.quantity;
          prod.cost_price_tax_inc_total = prod.cost_price_tax_inc * prod.quantity;
        }
      });
      if (!exists) {
        const pd = {
          product_id: new_product.id,
          barcode: new_product.barcode,
          discount_percentage: 0,
          discount_price: 0,
          quantity: 1,
          price_tax_exc: new_product.sale_price_tax_exc,
          price_tax_inc: new_product.sale_price_tax_inc,
          product: new_product.product,
          price_tax_inc_total: new_product.sale_price_tax_inc,
          cost_price_tax_inc: new_product.cost_price_tax_inc,
          cost_price_tax_inc_total: new_product.cost_price_tax_inc,
          type: new_product.type,
          showObservaciones: false,
          observaciones: ""
        };
        if (new_product.uses_portions) {
          pd.portions = new_product.portions.map(portion => {
            let q = parseFloat(portion.quantity);
            if (!q || q <= 0) q = 1.00;
            return { ...portion, quantity: q.toFixed(2) };
          });
        }
        this.productsOrderList.push(pd);
      }
    },
    removeProduct(index, detail_id = null) {
      if (detail_id) {
        axios.delete(`api/orders/${this.order.id}/remove-product/${detail_id}`, this.$root.config)
          .then(() => {
            this.productsOrderList.splice(index, 1);
            alert("Producto eliminado y comanda de eliminación enviada.");
          })
          .catch(err => {
<<<<<<< HEAD
            console.error(err);
            alert("Error al eliminar el producto.");
          });
      } else {
        this.productsOrderList.splice(index, 1);
      }
    },
    toggleObservaciones(index) {
      this.productsOrderList[index].showObservaciones = !this.productsOrderList[index].showObservaciones;
    },
    updateObservaciones(index) {
      console.log(`Observaciones para: ${this.productsOrderList[index].product} => ${this.productsOrderList[index].observaciones}`);
    },
    openAddProductModal() {
      this.filters.product = "";
      $("#addProductModal").modal("show");
    },
    facturarF1() {
      if (!this.productsOrderList.length) {
        return Swal.fire({ icon: "error", title: "Oops...", text: "Debes añadir productos al carrito" });
      }
      this.openModalPayment();
    },
    openModalPayment() {
      if (this.selectedPaymentMethod?.code == "10") {
        this.order.payment_methods.cash = this.total_tax_inc;
      }
      $("#paymentModal").modal("show");
      $("#paymentModal").on("shown.bs.modal", () => {
        if (this.selectedPaymentMethod?.code == "10") {
          const ci = $("#cashPayment");
          ci.focus();
          ci.select();
        }
        document.addEventListener("keydown", this.modalKeyHandler);
      });
    },
    removeModalKeydown() {
      document.removeEventListener("keydown", this.modalKeyHandler);
    },
    modalKeyHandler(e) {
      if (e.key === "F1") {
        e.preventDefault();
        this.createOrUpdateOrder(2);
      }
      if (e.key === "F2") {
        e.preventDefault();
        this.createOrUpdateOrder(4);
      }
    },
    async createOrUpdateOrder(state_order) {
      // Verifica que haya productos
      if (!this.productsOrderList.length) {
        return Swal.fire({ icon: "error", title: "Oops...", text: "Debes añadir productos al carrito" });
      }
      // Verifica si es efectivo y no alcanza el pago
      if (state_order === 2 && this.selectedPaymentMethod?.code === "10" && this.paid_value < this.total_tax_inc) {
        return this.openModalPayment();
      }
      // Verifica si pasa a crédito sin cliente válido
      if (this.order.id_client === 1 && state_order === 5) {
        return alert("Debe seleccionar un cliente válido");
      }
      Swal.fire({ title: "Guardando, por favor espere...", allowOutsideClick: false, didOpen: () => Swal.showLoading() });
      this.order.state = state_order;
      this.order.box_id = this.$root.box;
      if (state_order === 1) {
        const pv = this.activeVouchers.find(v => v.document === "Pedido");
        if (pv) this.order.numbering_range_id = pv.id;
        this.order.proccess = false;
      } else {
        this.order.numbering_range_id = this.selectedVoucher;
      }
      this.order.total_tax_exc = this.total_tax_exc;
      this.order.total_tax_inc = this.total_tax_inc;
      this.order.total_discount = this.total_discount;
      this.order.total_cost_price_tax_inc = this.total_cost_price_tax_inc;
      this.order.productsOrder = this.productsOrderList;
      const req = this.order_id && this.order_id != 0
        ? axios.put(`api/orders/${this.order_id}`, this.order, this.$root.config)
        : axios.post("api/orders", this.order, this.$root.config);
      req.then(({ data }) => {
        let html = `<p>Los datos se han guardado correctamente.</p>`;
        if (data.warning) {
          html += `<div class="alert alert-warning text-left mt-3"><strong>Error de envío:</strong> ${data.warning}.<br></div>`;
        }
        Swal.fire({ icon: "success", title: "Excelente", html, confirmButtonText: "OK" }).then(() => window.location.reload());
      }).catch(err => {
        console.error(err);
        Swal.fire({ icon: "error", title: "Oops...", text: "Hubo un error al guardar los datos" });
      }).finally(() => {
        $("#paymentModal").modal("hide");
        this.$router.push({ path: "/" });
      });
=======
            console.error("Error al eliminar el producto:", err);
            alert("Error al eliminar el producto.");
          });
      } else {
        this.productsOrderList.splice(index, 1);
      }
    },
    toggleObservaciones(index) {
      this.productsOrderList[index].showObservaciones = !this.productsOrderList[index].showObservaciones;
    },
    updateObservaciones(index) {
      console.log(`Observaciones para: ${this.productsOrderList[index].product} => ${this.productsOrderList[index].observaciones}`);
    },
    openAddProductModal() {
      this.filters.product = "";
      $("#addProductModal").modal("show");
    },
    facturarF1() {
      if (!this.selectedBoxData || !this.selectedBoxData.is_open) {
        return Swal.fire({
          icon: 'warning',
          title: 'Caja cerrada',
          text: 'Debes abrir la caja antes de facturar.'});}
      if (!this.productsOrderList.length) {
        return Swal.fire({ icon: "error", title: "Oops...", text: "Debes añadir productos al carrito" });}
      this.openModalPayment();
    },
    openModalPayment() {
      if (this.selectedPaymentMethod?.code == "10") {
        this.order.payment_methods.cash = this.total_tax_inc;
      }
      $("#paymentModal").modal("show");
      $("#paymentModal").on("shown.bs.modal", () => {
        if (this.selectedPaymentMethod?.code == "10") {
          const ci = $("#cashPayment");
          ci.focus();
          ci.select();
        }
        document.addEventListener("keydown", this.modalKeyHandler);
      });
    },
    removeModalKeydown() {
      document.removeEventListener("keydown", this.modalKeyHandler);
    },
    modalKeyHandler(e) {
      if (e.key === "F1") {
        e.preventDefault();
        this.createOrUpdateOrder(2);
      }
      if (e.key === "F2") {
        e.preventDefault();
        this.createOrUpdateOrder(4);
      }
    },
    async createOrUpdateOrder(state_order) {
      if (!this.selectedBoxData || !this.selectedBoxData.is_open) {
        return Swal.fire({
          icon: 'warning',
          title: 'Caja cerrada',
          text: 'Debes abrir la caja antes de continuar.'});}
      if (!this.productsOrderList.length) {
        return Swal.fire({
          icon: "error",
          title: "Oops...",
          text: "Debes añadir productos al carrito"});}
      if (
        state_order === 2 &&
        this.selectedPaymentMethod?.code === "10" &&
        this.paid_value < this.total_tax_inc
      ) {
        return this.openModalPayment();
      }
      if (this.order.id_client === 1 && state_order === 5) {
        return alert("Debe seleccionar un cliente válido");
      }
      Swal.fire({
        title: "Guardando, por favor espere...",
        allowOutsideClick: false,
        didOpen: () => {
          Swal.showLoading();
        }
      });
      this.order.state = state_order;
      this.order.box_id = this.$root.box;
      if (state_order === 1) {
        const pv = this.activeVouchers.find(v => v.document === "Pedido");
        if (pv) this.order.numbering_range_id = pv.id;
        this.order.proccess = false;
      } else {
        this.order.numbering_range_id = this.selectedVoucher;
      }
      this.order.total_tax_exc = this.total_tax_exc;
      this.order.total_tax_inc = this.total_tax_inc;
      this.order.total_discount = this.total_discount;
      this.order.total_cost_price_tax_inc = this.total_cost_price_tax_inc;
      this.order.productsOrder = this.productsOrderList;
      const req = (this.order_id && this.order_id != 0)
        ? axios.put(`api/orders/${this.order_id}`, this.order, this.$root.config)
        : axios.post("api/orders", this.order, this.$root.config);
      req
        .then(({ data }) => {
          const warning = data.warning;
          let htmlMessage = `<p>Los datos se han guardado correctamente.</p>`;
          if (warning) {
            htmlMessage += `
              <div class="alert alert-warning text-left mt-3">
                <strong>Error de envío:</strong> ${warning}.<br>
              </div>
            `;
          }
          Swal.fire({
            icon: "success",
            title: "Excelente",
            html: htmlMessage,
            confirmButtonText: "OK"
          }).then(() => {
            window.location.reload();
          });
        })
        .catch(err => {
          console.error(err);
          Swal.fire({
            icon: "error",
            title: "Oops...",
            text: "Hubo un error al guardar los datos"
          });
        })
        .finally(() => {
          $("#paymentModal").modal("hide");
          this.$router.push({ path: "/" });
        });
    },

    // ---------------- Métodos de Reserva ----------------
    openReservationModal() {
      this.reservationAt = "";
      this.reservationObservations = "";
      this.showReservationModal = true;
      document.body.classList.add("modal-open");
>>>>>>> 0ed4468 (factuara electronica + reserva + caja)
    },
    closeReservationModal() {
      this.showReservationModal = false;
      document.body.classList.remove("modal-open");
    },
    confirmReservation() {
      this.order.is_reservation = true;
      this.order.reservation_at = this.reservationAt;
      this.order.observations = this.reservationObservations;
      this.closeReservationModal();
      this.createOrUpdateOrder(1);
    },

    // ---------------- Métodos de Dividir Cuenta ----------------
    openSplitModal() {
      this.selectedSplitDetails = [];
      this.splitQuantities = {};
      this.productsOrderList.forEach(p => {
        this.$set(this.splitQuantities, p.id, p.quantity);
      });
      this.showSplitModal = true;
      document.body.classList.add('modal-open');
    },
    closeSplitModal() {
      this.showSplitModal = false;
      document.body.classList.remove('modal-open');
    },
    decrementSplit(id) {
      if (this.splitQuantities[id] > 1) {
        this.splitQuantities[id]--;
      }
    },
    incrementSplit(id) {
      const p = this.productsOrderList.find(item => item.id === id);
      if (p && this.splitQuantities[id] < p.quantity) {
        this.splitQuantities[id]++;
      }
    },
    async confirmSplit() {
      try {
        const items = this.selectedSplitDetails.map(id => ({
          detail_id: id,
          quantity: this.splitQuantities[id]
        }));
        const { data } = await axios.post(
          `api/orders/${this.order.id}/split`,
          { items },
          this.$root.config
        );
        this.closeSplitModal();
        const newId = data.new_order.id;
        this.$router.push({ name: 'create-edit-order', params: { order_id: newId } });
      } catch (err) {
        console.error('Error al dividir cuenta:', err);
        Swal.fire({ icon: 'error', title: 'Oops...', text: 'No se pudo dividir la cuenta' });
      }
    },

    commands() {
      shortcut.add("F1", () => {
<<<<<<< HEAD
        if (!this.productsOrderList.length) Swal.fire({ icon: "error", title: "Oops...", text: "Debes añadir productos al carrito" });
        else this.openModalPayment();
      });
      shortcut.add("F2", () => {
        if (!this.productsOrderList.length) Swal.fire({ icon: "error", title: "Oops...", text: "Debes añadir productos al carrito" });
        else this.openModalPayment();
=======
        if (!this.productsOrderList.length) {
          Swal.fire({ icon: "error", title: "Oops...", text: "Debes añadir productos al carrito" });
        } else {
          this.openModalPayment();
        }
      });
      shortcut.add("F2", () => {
        if (!this.productsOrderList.length) {
          Swal.fire({ icon: "error", title: "Oops...", text: "Debes añadir productos al carrito" });
        } else {
          this.openModalPayment();
        }
>>>>>>> 0ed4468 (factuara electronica + reserva + caja)
      });
      shortcut.add("F10", () => this.openAddProductModal());
    },
    searchClient() {
      if (!this.filters.client) return;
      axios.post(`api/clients/search-client?client=${this.filters.client}`, null, this.$root.config)
<<<<<<< HEAD
        .then(resp => resp.data ? this.addClient(resp.data) : $("#no-results").toast("show"))
=======
        .then(resp => {
          const nc = resp.data;
          nc ? this.addClient(nc) : $("#no-results").toast("show");
        })
>>>>>>> 0ed4468 (factuara electronica + reserva + caja)
        .catch(console.error);
    },
    addClient(client) {
      this.order.id_client = client.id;
<<<<<<< HEAD
      const name = client.razon_social || client.name ||
        `${client.first_name} ${client.second_name || ""} ${client.first_lastname} ${client.second_lastname || ""}`.trim();
      this.order.client = name;
      this.filters.client = name;
    },
    openSplitModal() {
      this.splitSelection = [];
      this.splitQuantities = this.productsOrderList.map(p => p.quantity);
      this.showSplitModal = true;
    },
    closeSplitModal() {
      this.showSplitModal = false;
    },
    async confirmSplit() {
      if (!this.splitSelection.length) {
        return Swal.fire('Error', 'Debes seleccionar al menos un producto.', 'error');
      }
      const detalles = this.splitSelection.map(idx => ({
        detail_id: this.productsOrderList[idx].id,
        quantity: this.splitQuantities[idx]
      }));
      try {
        Swal.fire({ title: 'Procesando…', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
        const { data } = await axios.post(`api/orders/${this.order.id}/split`, { detalles }, this.$root.config);
        Swal.close();
        this.$router.push({ name: 'create-edit-order', params: { order_id: data.new_order_id } });
      } catch (err) {
        Swal.fire('Error', 'No se pudo dividir el pedido.', 'error');
      } finally {
        this.closeSplitModal();
        $('#paymentModal').modal('hide');
      }
    }
=======
      const cname = client.razon_social || client.name ||
        `${client.first_name} ${client.second_name || ""} ${client.first_lastname} ${client.second_lastname || ""}`.trim();
      this.order.client = cname;
      this.filters.client = cname;
    },
>>>>>>> 0ed4468 (factuara electronica + reserva + caja)
  }
};
</script>

<style scoped>
<<<<<<< HEAD
/* =============================
   PALETA DE COLORES (TEMA TEAL)
   ============================= */
.top-bar {
  background: linear-gradient(45deg, #006064 0%, #00838F 100%);
  color: #fff;
}
.big-total {
  font-size: 2rem;
  font-weight: bold;
}
.btn-caja {
  background-color: #006064;
  color: #fff;
  border: 1px solid #004f59;
}
.btn-caja:hover {
  background-color: #004f59;
  color: #fff;
}
.table-teal {
  background-color: #00838F !important;
  color: #fff !important;
}
.table-teal th, .table-teal td {
  color: #fff !important;
}
.table-teal tr.total-row {
  background-color: #006064 !important;
  font-weight: bold;
}
.btn-aqua {
  background-color: #00838F;
  color: #fff;
  border: none;
}
.btn-aqua:hover {
  background-color: #006f75;
}
.btn-outline-aqua {
  border: 1px solid #00838F;
  color: #00838F;
}
.btn-outline-aqua:hover {
  background-color: #00838F;
  color: #fff;
}
.loading-overlay {
  position: fixed;
  top: 0; left: 0; width: 100%; height: 100%;
  background: rgba(255,255,255,0.8);
  display: flex; justify-content: center; align-items: center;
  z-index: 5000;
}
#create-edit-order { font-size: 1.1rem; }
=======
/* 
  =============================
  PALETA DE COLORES (TEMA TEAL)
  -----------------------------
  - tealOscuro:   #006064
  - tealMedio:    #00838F
  - tealHover:    #004f59  (más oscuro)
  - textoBlanco:  #fff
  - fondoClaro:   #f0f9f9  (opcional para backgrounds)
  =============================
*/

/* 
  BARRA SUPERIOR
*/
.top-bar {
  background: linear-gradient(45deg, #006064 0%, #00838F 100%);
  color: #fff;
}
.big-total {
  font-size: 2rem;
  font-weight: bold;
}

/* Botón "Cambiar Caja" */
.btn-caja {
  background-color: #006064;
  color: #fff;
  border: 1px solid #004f59;
}
.btn-caja:hover {
  background-color: #004f59;
  color: #fff;
}

/* Tabla Totales */
.table-teal {
  background-color: #00838F !important;
  color: #fff !important;
}
.table-teal th,
.table-teal td {
  color: #fff !important;
}
.table-teal tr.total-row {
  background-color: #006064 !important;
  font-weight: bold;
  color: #fff !important;
}

/* Cabecera tabla de productos */
thead.bg-secondary {
  background-color: #006064 !important;
}
thead.bg-secondary th {
  color: #fff !important;
}

/* Botones Teal */
.btn-aqua {
  background-color: #00838F;
  color: #fff;
  border: none;
}
.btn-aqua:hover {
  background-color: #006f75;
  color: #fff;
}
.btn-outline-aqua {
  border: 1px solid #00838F;
  color: #00838F;
  background-color: transparent;
}
.btn-outline-aqua:hover {
  background-color: #00838F;
  color: #fff;
}

/* Overlay de Loading */
.loading-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(255, 255, 255, 0.8);
  z-index: 5000;
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
}

/* Ajustes generales */
#create-edit-order {
  font-size: 1.1rem;
}

/* Ajustes para modal dividir cuenta */
.modal-backdrop.show {
  z-index: 1050 !important;
}
.modal.show {
  z-index: 1055 !important;
}

/* ---------- NUEVAS REGLAS PARA LA TABLA DE DIVIDIR CUENTA ---------- */
#splitModal .table {
  width: 100%;
  margin-bottom: 0;
}
#splitModal thead {
  background-color: #00838F;
}
#splitModal thead th {
  color: #fff;
  text-align: center;
  vertical-align: middle;
  padding: 0.75rem;
}
#splitModal tbody td {
  vertical-align: middle;
  padding: 0.5rem;
}
#splitModal tbody td.align-middle {
  text-align: center;
}
#splitModal tbody td.text-right {
  text-align: right;
}
#splitModal .form-check-input {
  margin: 0 auto;
  display: block;
}
#splitModal .btn-outline-secondary.btn-sm {
  padding: 0.25rem 0.5rem;
  line-height: 1;
}
#splitModal .form-control-sm {
  padding: 0.25rem 0.5rem;
  height: auto;
  text-align: center;
}
>>>>>>> 0ed4468 (factuara electronica + reserva + caja)
</style>
