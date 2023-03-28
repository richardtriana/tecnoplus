/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');

import Vue from 'vue'
import VueRouter from 'vue-router'
import { VueSpinners } from '@saeris/vue-spinners'
import vSelect from 'vue-select'
import JsonExcel from "vue-json-excel";
import 'vue-select/dist/vue-select.css';
import { dollarFilter } from './filters';

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

import Boxes from './components/Box/Boxes.vue'

import Roles from './components/Rol/Roles.vue';
import Users from './components/User/Users.vue';
import Configuration from './components/Configuration.vue';
import Profile from './components/Profile.vue';
import Tables from './components/Table/Tables.vue'
import Zones from './components/Zone/Zones.vue'

//Services
import global from './services/global.js';
import axios from 'axios';

import CKEditor from '@ckeditor/ckeditor5-vue2';
import Swal from "sweetalert2";


Vue.use(VueRouter)
Vue.use(VueSpinners)
Vue.use(CKEditor)
Vue.use(require('vue-moment'));

Vue.filter('currency', dollarFilter)

Vue.component('pagination', require('laravel-vue-pagination'));
Vue.component("downloadExcel", JsonExcel);
Vue.component('v-select', vSelect)

window.Swal = Swal;

const routes = [

  { path: '', component: CreateEditOrder, props: { order_id: 0 }, name: 'main' },

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

  { path: '/orders', component: Orders, alias: "order.index" , props:{ status:1}},
  { path: '/orders/:order_id/details-order', component: DetailsOrder, props: true, name: 'details-order', alias: "order.index" },
  { path: '/create-edit-order/:order_id', component: CreateEditOrder, props: true, name: 'create-edit-order', alias: "order.store" },
  { path: '/create-edit-order-mobile/:order_id', component: CreateEditOrderMobile, props: true, name: 'create-edit-order-mobile', alias: "order.store" },

  { path: '/billings', component: Billings, alias: "billing.index" },
  { path: '/billings/:billing_id/details-billing', component: DetailsBilling, props: true, name: 'details-billing', alias: "billing.index" },
  { path: '/create-edit-billing/:billing_id', component: CreateEditBilling, props: true, name: 'create-edit-billing', alias: "billing.store" },

  { path: '/reports/report-sale', component: ReportSale, props: true, name: 'report-sale' },
  { path: '/reports/report-general-sales', component: ReportGeneralSales, props: true, name: 'report-general-sales' },
  { path: '/reports/report-product-sales', component: ReportProductSales, props: true, name: 'report-product-sales' },
  { path: '/reports/closing', component: ReportClosing, props: true, name: 'report-closing' },

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

  { path: '**', name: 'NoFound', component: NoFound },

]

const router = new VueRouter({
  routes // short for `routes: routes`
})

export default router;

router.beforeEach(async (to, from, next) => {
  // redirect to login if not authenticated in and trying to access a restricted route
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
    isAuthenticated
  }
  if (authRequired && !isAuthenticated) {
    return next({ name: "Login", query: { redirect: to.fullPath } });
  }

  if (isAuthenticated) {

    let alias = to.matched[0].alias;
    if (alias != "") {
      if (!global.validatePermission(undefined, alias)) {
        return next({ name: "NoFound" });
      }
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
    config: Object({
      headers: {
        Authorization: "",
      },
    }),
    box: '',
    listBoxes: []
  },
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
  router,
  created() {
    this.assignDataRequired();
    this.selectedBox();
  },
  methods: {
    assignDataRequired() {
      this.user = JSON.parse(localStorage.getItem("user"));
      this.token = localStorage.getItem("token");

      if (this.user) {
        this.permissions = this.user.permissions;
      }

      this.config.headers.Authorization = "Bearer " + this.token;
    },
    logout() {
      this.user = {};
      this.token = "";
      this.permissions = [];
      this.config.headers.Authorization = "";
      localStorage.clear();
      this.$router.push('/login');
    },
    validatePermission(permission) {
      return global.validatePermission(this.permissions, permission);
    },
    validateToken() {
      axios.get('api/users/' + this.user.sub, this.config)
        .then(response => {
          return true;
        })
        .catch(response => {
          this.logout();
        });
    },
    selectedBox() {
      axios.
        get('api/boxes/byUser', this.config)
        .then((response) => {
          this.listBoxes = response.data.boxes;
        })
        .catch((response) => {
          this.listBoxes = [];
        });
    }
  }
});

