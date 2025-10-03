/**
 * resources/js/app.js
 *
 * Este archivo carga las dependencias JavaScript, configura Vue, el router,
 * interceptores de Axios y asigna el usuario autenticado para que esté disponible
 * globalmente (this.$root.user).
 */

require('./bootstrap');

import Vue from 'vue'
import VueRouter from 'vue-router'
import { VueSpinners } from '@saeris/vue-spinners'
import vSelect from 'vue-select'
import JsonExcel from "vue-json-excel";
import 'vue-select/dist/vue-select.css';
import { dollarFilter } from './filters';

// Bootstrap & BootstrapVue
import 'bootstrap/dist/css/bootstrap.css';
import 'bootstrap-vue/dist/bootstrap-vue.css';
import BootstrapVue from 'bootstrap-vue';
import IconsPlugin from 'bootstrap-vue/dist/bootstrap-vue-icons';

Vue.use(BootstrapVue);
Vue.use(IconsPlugin);

import Login from './components/Login.vue'
import NoFound from './components/NoFound.vue';
import Clients from './components/Client/Clients.vue'
import CreateEditClient from './components/Client/CreateEditClient.vue'

import Products from './components/Product/Products.vue'
import CreateEditProduct from './components/Product/CreateEditProduct.vue'
import ImportProducts from './components/Product/ImportProducts'
import Checker from './components/Product/Checker'
import Stock from './components/Product/Stock'

import Taxes from './components/Tax/Taxes.vue'
import CreateEditTax from './components/Tax/CreateEditTax.vue'

import Categories from './components/Category/Categories.vue'
import CreateEditCategory from './components/Category/CreateEditCategory.vue'

import Brands from './components/Brand/Brands.vue'
import CreateEditBrand from './components/Brand/CreateEditBrand.vue'

import Suppliers from './components/Supplier/Suppliers.vue'
import CreateEditSupplier from './components/Supplier/CreateEditSupplier.vue'

import Orders from './components/Order/Orders.vue'
import DetailsOrder from './components/Order/DetailsOrder.vue'
import CreateEditOrder from './components/Order/CreateEditOrder.vue'
import PosView from './components/Order/PosView.vue';
import CreateEditOrderMobile from './components/Mobile/CreateEditOrder.vue'

import Billings from './components/Billing/Billings.vue'
import DetailsBilling from './components/Billing/DetailsBilling.vue'
import CreateEditBilling from './components/Billing/CreateEditBilling.vue'

import Credits from './components/Credit/Credits.vue'
import DetailsCredit from './components/Credit/DetailsCredit.vue'
import CreateEditCredit from './components/Credit/CreateEditCredit.vue'

import ReportSale from './components/Report/ReportSale'
import ReportProductSales from './components/Report/ReportProductSales'
import ReportGeneralSales from './components/Report/ReportGeneralSales'
import ReportClosing from './components/Report/ReportClosing'
import ReportInvoicedProducts from './components/Report/ReportInvoicedProducts'

<<<<<<< HEAD
=======
// Caja
import CashReconciliation from './components/Cash/CashReconciliation'
import CashReconciliationHistory from './components/Cash/CashReconciliationHistory.vue'

// Boxes
>>>>>>> 0ed4468 (factuara electronica + reserva + caja)
import Boxes from './components/Box/Boxes.vue'

import Roles from './components/Rol/Roles.vue';
import Users from './components/User/Users.vue';
import Configuration from './components/Configuration.vue';
import Profile from './components/Profile.vue';
import Tables from './components/Table/Tables.vue'
import Zones from './components/Zone/Zones.vue'

// Nueva importación del componente Factus
import Factus from './components/Factus/Factus.vue'

// NUEVAS IMPORTACIONES para el módulo de Porciones:
import Portions from './components/Portion/Portions.vue';
import PortionOrders from './components/Portion/PortionOrders.vue';

// NUEVAS IMPORTACIONES para el módulo de Vouchers:
import Vouchers from './components/Vouchers/Vouchers.vue';
import CreateEditVoucher from './components/Vouchers/CreateEditVoucher.vue';

import FactusVoucherslist from './components/Order/FactusVoucherslist.vue'
<<<<<<< HEAD
//notas credito
=======
// notas crédito
>>>>>>> 0ed4468 (factuara electronica + reserva + caja)
import CreateCreditNote from './components/Order/CreateCreditNote.vue';
import Services from './components/Service/Services.vue'
import DocumentList from './components/Support_document/DocumentList.vue';
import CreateEditDocument from './components/Support_document/CreateEditDocument.vue';
import CreateAdjustmentNote from './components/Support_document/CreateAdjustmentNote.vue';

<<<<<<< HEAD
//recepcion de documentos
import ReceptionDocuments from './components/Reception_documents/ReceptionDocuments.vue';


=======
// recepción de documentos
import ReceptionDocuments from './components/Reception_documents/ReceptionDocuments.vue';

>>>>>>> 0ed4468 (factuara electronica + reserva + caja)
import global from './services/global.js';
import axios from 'axios';

import CKEditor from '@ckeditor/ckeditor5-vue2';
import Swal from "sweetalert2";
import Kitchen from './components/Kitchen/Kitchen.vue';

Vue.use(VueRouter)
Vue.use(VueSpinners)
Vue.use(CKEditor)
Vue.use(require('vue-moment'));

Vue.filter('currency', dollarFilter)

Vue.component('pagination', require('laravel-vue-pagination'));
Vue.component("downloadExcel", JsonExcel);
Vue.component('v-select', vSelect)

window.Swal = Swal;

// Definición de rutas
const routes = [
  { path: '', component: CreateEditOrder, props: { order_id: 0 }, name: 'main', alias:"order.store" },
  { path: '/kitchen', component: Kitchen, name: "kitchen", alias: "kitchen.index", props:{ status:1 } },
  { path: '/clients', component: Clients, alias: "client.index" },
  { path: '/create-edit-client', component: CreateEditClient },
  { path: '/products', component: Products, alias: "product.index" },
  { path: '/create-edit-product', component: CreateEditProduct },
  { path: '/stock', component: Stock },
  { path: '/checker', component: Checker },
  { path: '/taxes', component: Taxes, alias: "tax.index" },
  { path: '/suppliers', component: Suppliers, alias: "supplier.index" },
  { path: '/create-edit-supplier', component: CreateEditSupplier },
  { path: '/categories', component: Categories, alias: "category.index" },
  { path: '/create-edit-category', component: CreateEditCategory },
  { path: '/brands', component: Brands, alias: "brand.index" },
<<<<<<< HEAD
  // Rutas para Inventario de Porciones:
=======
>>>>>>> 0ed4468 (factuara electronica + reserva + caja)
  { path: '/portions', component: Portions, name: 'Portions' },
  { path: '/portion-orders', component: PortionOrders, name: 'PortionOrders'},
  { path: '/orders', component: Orders, alias: "order.index", props:{ status:1 } },
  { path: '/orders/facturado', component: Orders, name: 'orders.facturado', alias: 'order.facturado', props: { status: 2 }},
  { path: '/orders/:order_id/details-order', component: DetailsOrder, props: true, name: 'details-order', alias: "order.index" },
  { path: '/create-edit-order/:order_id', component: CreateEditOrder, props: true, name: 'create-edit-order', alias: "order.store" },
  { path: '/create-edit-order-mobile/:order_id', component: CreateEditOrderMobile, props: true, name: 'create-edit-order-mobile', alias: "order.store" },
  { path: '/billings', component: Billings, alias: "billing.index" },
  { path: '/billings/:billing_id/details-billing', component: DetailsBilling, props: true, name: 'details-billing', alias: "billing.index" },
  { path: '/create-edit-billing/:billing_id', component: CreateEditBilling, props: true, name: 'create-edit-billing', alias: "billing.store" },
  { path: '/reports/report-sale', component: ReportSale, props: true, name: 'report-sale', alias:'report.index' },
  { path: '/reports/report-general-sales', component: ReportGeneralSales, props: true, name: 'report-general-sales', alias:'report.index' },
  { path: '/reports/report-product-sales', component: ReportProductSales, props: true, name: 'report-product-sales', alias:'report.index' },
  { path: '/reports/closing', component: ReportClosing, props: true, name: 'report-closing', alias:'report.index' },
  { path: '/reports/invoiced-products', component: ReportInvoicedProducts, props: true, name: 'report-invoiced-products', alias: 'report.index' },
  { path: '/boxes', component: Boxes, alias: 'box.index' },
  { path: '/credits', component: Credits, alias: "credit.index" },
  { path: '/credits/:order_id/details-credit', component: DetailsCredit, props: true, name: 'details-credit', alias: "credit.index" },
  { path: '/create-edit-credit/:order_id', component: CreateEditCredit, props: true, name: 'create-edit-credit', alias: "credit.store" },
  { path: '/login', name: 'Login', component: Login },
  { path: '/roles', name: 'Roles', component: Roles, alias: "rol.index" },
  { path: '/users', name: 'Users', component: Users, alias: "user.index" },
  { path: '/configuration', name: 'Configuration', component: Configuration, alias: "configuration" },
  { path: '/profile', name: 'Profile', component: Profile },
  { path: '/zones', component: Zones, name: 'Zone', alias: 'zone.index' },
  { path: '/tables', component: Tables, name: 'Table', alias: 'table.index' },
<<<<<<< HEAD
  // Componente Factus
  { path: '/factus', name: 'Factus', component: Factus },
  { path: '/factus-vouchers', component: FactusVoucherslist, name: 'FactusVoucherslist'},
  // Rutas para Vouchers
  { path: '/vouchers',component: Vouchers, name: 'Vouchers',},
  {path: '/vouchers/create' ,component: CreateEditVoucher,name: 'vouchers.create'},
  //nota credito
  { path: '/credit-notes/create', component: CreateCreditNote, name: 'credit_notes.create' },
  { path: '/services', component: Services, name: 'service' },
  { path: '/pos', component: PosView,name: 'PosView'},
  { path: '/support-documents',name: 'DocumentList',component: DocumentList},
  { path: '/support-document',name: 'CreateEditDocument',component: CreateEditDocument},
  { path: '/adjustment-note', name: 'CreateAdjustmentNote', component: CreateAdjustmentNote },

  { path: '/reception-documents',name: 'ReceptionDocuments',component: ReceptionDocuments},
    
    
    
  
  
  //no encontrada
=======
  { path: '/factus', name: 'Factus', component: Factus },
  { path: '/factus-vouchers', component: FactusVoucherslist, name: 'FactusVoucherslist'},
  { path: '/vouchers', component: Vouchers, name: 'Vouchers' },
  { path: '/vouchers/create', component: CreateEditVoucher, name: 'vouchers.create' },
  { path: '/credit-notes/create', component: CreateCreditNote, name: 'credit_notes.create' },
  { path: '/services', component: Services, name: 'service' },
  { path: '/pos', component: PosView, name: 'PosView'},
  { path: '/support-documents', name: 'DocumentList', component: DocumentList},
  { path: '/support-document', name: 'CreateEditDocument', component: CreateEditDocument},
  { path: '/adjustment-note', name: 'CreateAdjustmentNote', component: CreateAdjustmentNote },
  { path: '/reception-documents', name: 'ReceptionDocuments', component: ReceptionDocuments},
  { path: '/cash/reconciliation', component: CashReconciliation, props: true, name: 'cash-reconciliation'},
  { path: '/cash/history', name: 'cash-history', component: CashReconciliationHistory,},
>>>>>>> 0ed4468 (factuara electronica + reserva + caja)
  { path: '*', name: 'NoFound', component: NoFound },
];

const router = new VueRouter({
  routes
});

router.beforeEach(async (to, from, next) => {
  const publicRoutes = ["Login"];
  const authRequired = !publicRoutes.includes(to.name);
  let isAuthenticated = false;
  try {
    isAuthenticated =
      localStorage.getItem("token") &&
      localStorage.getItem("user") &&
      JSON.parse(localStorage.getItem("user"))
        ? true
        : false;
  } catch (e) {
    isAuthenticated = false;
  }
  if (authRequired && !isAuthenticated) {
    return next({ name: "Login", query: { redirect: to.fullPath } });
  }
  if (isAuthenticated) {
<<<<<<< HEAD
    // Recuperamos los permisos del usuario
    const user = JSON.parse(localStorage.getItem("user"));
    const permissions = user.permissions;
    const alias = to.matched[0]?.alias;
    // Si el alias no es "voucher.index", se realiza la validación de permisos
=======
    const user = JSON.parse(localStorage.getItem("user"));
    const permissions = user.permissions;
    const alias = to.matched[0]?.alias;
>>>>>>> 0ed4468 (factuara electronica + reserva + caja)
    if (alias && alias !== "voucher.index" && !global.validatePermission(permissions, alias)) {
      return next({ name: "NoFound" });
    }
  }
  next();
});

Vue.config.keyCodes = {
  f4: 115,
};

const app = new Vue({
  el: '#app',
  data: {
    user: Object,
    token: String,
    permissions: [],
    config: {
      headers: {
        Authorization: "",
        "Content-Type": "application/json",
        Accept: "application/json"
      }
    },
    factusConfig: {
      headers: {
        Authorization: "",
        "Content-Type": "application/json",
        Accept: "application/json"
      }
    },
    box: '',
    listBoxes: []
  },
  router,
  watch: {
    $route(to, from) {
      this.assignDataRequired();
    },
    box() {
      localStorage.setItem("box_worker", this.box);
    },
    user: {
      deep: true,
      handler() {
        localStorage.setItem("user", JSON.stringify(this.user));
      }
    }
  },
  created() {
    this.assignDataRequired();
    this.selectedBox();

    axios.interceptors.response.use(
      response => response,
      async error => {
        const originalRequest = error.config;
        if (error.response && error.response.status === 401 &&
            originalRequest.headers.Authorization && originalRequest.headers.Authorization.includes("Bearer")) {
          if (!originalRequest._retry) {
            originalRequest._retry = true;
            const factusRefreshToken = localStorage.getItem("factus_refresh_token");
            if (factusRefreshToken) {
              try {
                const refreshResponse = await axios.post("api/refresh-token", { refresh_token: factusRefreshToken }, {
<<<<<<< HEAD
                  headers: {
                    "Content-Type": "application/json",
                    Accept: "application/json"
                  }
=======
                  headers: { "Content-Type": "application/json", Accept: "application/json" }
>>>>>>> 0ed4468 (factuara electronica + reserva + caja)
                });
                const newAccessToken = refreshResponse.data.access_token;
                localStorage.setItem("factus_token", newAccessToken);
                this.factusConfig.headers.Authorization = "Bearer " + newAccessToken;
                originalRequest.headers.Authorization = "Bearer " + newAccessToken;
                return axios(originalRequest);
              } catch (refreshError) {
                return Promise.reject(refreshError);
              }
            }
          }
        }
        return Promise.reject(error);
      }
    );
  },
  methods: {
    assignDataRequired() {
      this.user = JSON.parse(localStorage.getItem("user"));
      this.token = localStorage.getItem("token");
<<<<<<< HEAD
      if (this.user) {
        this.permissions = this.user.permissions;
      }
      this.config.headers.Authorization = "Bearer " + this.token;
      console.log("App Token asignado:", this.config.headers.Authorization);
      const factusToken = localStorage.getItem("factus_token");
      this.factusConfig.headers.Authorization = "Bearer " + factusToken;
      console.log("Factus Token asignado:", this.factusConfig.headers.Authorization);
=======
      this.permissions = this.user?.permissions || [];
      this.config.headers.Authorization = "Bearer " + this.token;
      const factusToken = localStorage.getItem("factus_token");
      this.factusConfig.headers.Authorization = "Bearer " + factusToken;
>>>>>>> 0ed4468 (factuara electronica + reserva + caja)
    },
    logout() {
      this.user = {};
      this.token = "";
      this.permissions = [];
      this.config.headers.Authorization = "";
      this.factusConfig.headers.Authorization = "";
      localStorage.clear();
      this.$router.push('/login');
    },
    validatePermission(permission) {
      return global.validatePermission(this.permissions, permission);
    },
    validateToken() {
      axios.get('api/users/' + this.user.sub, this.config)
        .then(() => true)
        .catch(() => this.logout());
    },
    selectedBox() {
      axios.get('api/boxes/byUser', this.config)
<<<<<<< HEAD
        .then((response) => {
          this.listBoxes = response.data.boxes;
        })
        .catch((response) => {
          this.listBoxes = [];
        });
    }
=======
        .then(({ data }) => { this.listBoxes = data.boxes; })
        .catch(() => { this.listBoxes = []; });
    },
    // Otros métodos...
>>>>>>> 0ed4468 (factuara electronica + reserva + caja)
  }
});
