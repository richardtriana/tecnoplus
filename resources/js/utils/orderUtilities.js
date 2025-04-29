// resources/js/utils/orderUtilities.js
import axios from "axios";
import Swal from "sweetalert2";

/**
 * Genera y descarga el PDF de una orden.
 * @param {number} orderId 
 * @param {object} config -> Configuración con headers, etc.
 */
export function generatePdf(orderId, config) {
  return axios
    .get(`api/orders/generatePdf/${orderId}`, config)
    .then((response) => {
      const pdf = response.data.pdf;
      const a = document.createElement("a");
      a.href = "data:application/pdf;base64," + pdf;
      a.download = `Order-${orderId}.pdf`;
      a.click();
    })
    .catch((error) => {
      Swal.fire({
        icon: "error",
        title: "Error",
        text: `No se pudo generar el PDF de la orden #${orderId}`
      });
    });
}

/**
 * Reimprime una orden (descarga o imprime PDF).
 * @param {number} orderId 
 * @param {object} config -> Configuración con headers, etc.
 */
export function reprintOrder(orderId, config) {
  return axios
    .get(`api/orders/reprint/${orderId}`, config)
    .then((response) => {
      const pdf = response.data.pdf;
      const a = document.createElement("a");
      a.href = "data:application/pdf;base64," + pdf;
      a.download = `ReprintOrder-${orderId}.pdf`;
      a.click();
    })
    .catch((error) => {
      Swal.fire({
        icon: "error",
        title: "Error",
        text: `No se pudo reimprimir la orden #${orderId}`
      });
    });
}

/**
 * Imprime el ticket de una orden (por impresora POS, por ejemplo).
 * @param {number} orderId 
 * @param {object} config -> Configuración con headers, etc.
 */
export function printTicket(orderId, config) {
  return axios
    .get(`api/print-order/${orderId}`, config)
    .then(() => {
      Swal.fire({
        icon: "success",
        title: "Ticket",
        text: `Orden #${orderId} enviada a la impresora de tickets`
      });
    })
    .catch((error) => {
      Swal.fire({
        icon: "error",
        title: "Error",
        text: `No se pudo imprimir el ticket de la orden #${orderId}`
      });
    });
}

/**
 * Imprime la pre-cuenta de una orden (similar al ticket, pero sin facturar).
 * @param {number} orderId 
 * @param {object} config -> Configuración con headers, etc.
 */
export function printPreCuenta(orderId, config) {
  return axios
    .get(`api/print-precuenta/${orderId}`, config)
    .then(() => {
      Swal.fire({
        icon: "success",
        title: "Pre-cuenta",
        text: `Pre-cuenta de la orden #${orderId} enviada a la impresora`
      });
    })
    .catch((error) => {
      Swal.fire({
        icon: "error",
        title: "Error",
        text: `No se pudo imprimir la pre-cuenta de la orden #${orderId}`
      });
    });
}
