<template>
  <div>
    <section class="page-header">
      <h4 class="w-100 text-center">Detalles de Factura</h4>
      <table class="table table-bordered w-100 table-sm">
        <tbody>
          <tr>
            <td>No. Factura</td>
            <th>
              {{ billingInformation.no_invoice }}
            </th>
          </tr>
          <tr>
            <td>Fecha</td>
            <th>
              {{ billingInformation.updated_at }}
            </th>
          </tr>
          <tr>
            <th colspan="2" class="text-center">Proveedor</th>
          </tr>
          <tr>
            <td>Nombres:</td>
            <th>
              {{ billingInformation.supplier.name }}
            </th>
          </tr>
          <tr>
            <td>Documento / Nit:</td>
            <th>
              {{ billingInformation.supplier.document }}
            </th>
          </tr>
          <tr>
            <td>Direccion</td>
            <td>{{ billingInformation.supplier.address }}</td>
          </tr>
           <tr>
            <td>Email</td>
            <td>{{ billingInformation.supplier.email }}</td>
          </tr>
           <tr>
            <td>Celular / Télefono</td>
            <td>{{ billingInformation.supplier.mobile }}</td>
          </tr>
        </tbody>
      </table>
    </section>
    <section class="mt-5">
      <h5 class="text-center">Detalles</h5>
      <table class="table table-sm table-bordered">
        <thead>
          <tr>
            <th>#</th>
            <th>Código de barras</th>
            <th>Producto</th>
            <th>Cantidad</th>
            <th>Precio sin IVA</th>
            <th>Precio con IVA</th>
            <th>Descuento %</th>
            <th>Descuento $</th>
            <th>Precio Total</th>
          </tr>
        </thead>
        <tbody class="">
          <tr v-for="(i, index) in ItemList" :key="i.id">
            <td>{{ index + 1 }}</td>
            <td>{{ i.barcode }}</td>
            <td>{{ i.product }}</td>
            <td>{{ i.quantity }}</td>
            <td>$ {{ i.price_tax_exc }}</td>
            <td>$ {{ i.price_tax_inc }}</td>
            <td>{{ i.discount_percentage }} %</td>
            <td>$ {{ i.discount_price }}</td>
            <td class="text-right">$ {{ i.price_tax_inc_total }}</td>
          </tr>
        </tbody>
        <tfoot class="table-secondary">
          <tr>
            <td colspan="8">Subtotal</td>
            <td class="text-right">
            $  {{ billingInformation.total_iva_exc }}
            </td>
          </tr>
          <tr>
            <td colspan="8">Descuento</td>
            <td class="text-right">
            $  {{ billingInformation.total_discount }}
            </td>
          </tr>
          <tr>
            <td colspan="8">Total</td>
            <th class="h5 text-right">
            $  {{ billingInformation.total_iva_inc }}
            </th>
          </tr>
        </tfoot>
      </table>
    </section>
  </div>
</template>

<script>
export default {
  props: ["billing_id"],
  data() {
    return {
      billingInformation: {
        supplier: "",
      },
      ItemList: {},
    };
  },
  created() {
    this.getDetailsBilling();
  },
  methods: {
    getDetailsBilling() {
      let me = this;
      axios
        .get(`api/billings/${this.billing_id}`, this.$root.config)
        .then(function (response) {
          me.billingInformation = response.data.billing_information;
          me.ItemList = response.data.billing_details;
        });
    },
  },
};
</script>
