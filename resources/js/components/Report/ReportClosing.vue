<template>
    <div class="page w-100">
        <div class="page-header">
            <h3>Reporte de corte</h3>
        </div>
        <div class="page-search mx-2 my-2 border p-2">
            <div class="form-row">
                <div class="form-group col-3">
                    <label for="category">Estado</label>
                    <v-select :options="statusOrders" label="status" :reduce="(status) => status.id"
                        v-model="filter.status" />
                </div>
                <div class="form-group col-md-3">
                    <label for="from_date">Desde</label>
                    <input type="date" class="form-control" id="from_date" v-model="filter.from" />
                </div>
                <div class="form-group col-md-3">
                    <label for="to_date">Hasta</label>
                    <input type="date" class="form-control" id="to_date" v-model="filter.to" />
                </div>
                <div class="form-group col-3">
                    <label for="category">Usuario</label>
                    <v-select :options="userList" label="name" :reduce="(user) => user.id" v-model="filter.user_id" />
                </div>
                <div class="form-group col-md-3">
                    <label for="box">Caja</label>
                    <v-select :options="boxList" label="name" :reduce="(box) => box.id" v-model="filter.box_id" />
                </div>
                <div class="col my-4 col-4">
                    <button class="btn btn-success btn-block" @click="getOrders(1)">
                        Buscar <i class="bi bi-search"></i>
                    </button>
                </div>
            </div>
        </div>
        <div class="page-content">
            <section class="p-3">
                <table class="table table-sm table-bordered table-hover">
                    <thead class=" thead-dark">
                        <tr>
                            <th>Fecha venta</th>
                            <th>Número total de facturas</th>
                            <th>Nro. facturas credito</th>
                            <th>Valor créditos</th>
                            <th>Nro. cotizaciones</th>
                            <th>Nro. facturas de contado</th>
                            <th>Valor total de venta</th>

                            <th>Costo total de venta</th>
                            <th>Ganancia bruta</th>
                            <!-- <th>Valor dinero ingresado</th> -->
                            <!--  <th>Total Pago</th>                           
                            <th>Total Ganancia</th> -->
                            <th>Pagos recibidos</th>
                        </tr>
                    </thead>
                    <tbody v-if="List.length > 0">
                        <tr v-for="(l, index) in List" :key="index">
                            <td>
                                {{ l.date_paid }}
                            </td>
                            <td>
                                {{ l.number_of_orders }}
                            </td>

                            <td>
                                {{ l.credit }}
                            </td>
                            <td>
                                {{ l.paid_credit | currency }}
                            </td>
                            <td>
                                {{ l.quoted }}
                            </td>
                            <td>
                                {{ l.registered }}
                            </td>
                            <td>
                                {{ l.total_sale | currency }}
                            </td>
                            <td>
                                {{ l.total_sale_iva_exc | currency }}
                            </td>
                            <td>
                                {{ l.total_sale - l.total_sale_iva_exc | currency }}
                            </td>
                            <!-- <td>
                                {{ l.PAID_CREDIT | currency }}
                            </td> -->
                            <td>
								<span v-if="l.cash != null">Efectivo: {{  l.cash  }} </span> <br />
								<span v-if="l.nequi != null">Nequi: {{  l.nequi  }} </span> <br />
								<span v-if="l.card != null">Tarjeta : {{  l.card  }}</span> <br />
								<span v-if="l.others != null"> Otros medios de pago: {{  l.others  }} </span>
							</td>
                        </tr>
                    </tbody>
                    <tbody v-else>
                        <tr>
                            <td colspan="3">
                                No hay resultados
                            </td>
                        </tr>
                    </tbody>
                </table>
            </section>
        </div>
    </div>
</template>

<script>
export default {
    data() {
        return {
            List: {},
            userList: [],
            boxList: [],
            filter: {
                client: "",
                no_invoice: "",
                from: "",
                to: "",
                status: "",
                user_id: "",
                box_id: ""
            },
            statusOrders: [
                { id: 2, status: "Facturado" },
                { id: 3, status: "Cotizado" },
                { id: 5, status: "Credito" }
            ],
        };
    },
    methods: {
        getOrders(page = 1) {
            let me = this;

            let data = {
                page: page,
                from: me.filter.from,
                to: me.filter.to,
                no_invoice: me.filter.no_invoice,
                client: me.filter.client,
                status: me.filter.status
            };

            axios
                .get(
                    `api/reports/closing`,
                    {
                        params: data,
                        headers: this.$root.config.headers,
                    })
                .then(function (response) {
                    me.List = response.data;
                });
        },
        listBoxes() {
            let me = this;
            axios
                .get(`api/boxes/box-list`, this.$root.config)
                .then(function (response) {
                    me.boxList = response.data.boxes;
                });
        },
        listUsers() {
            let me = this;
            axios
                .get(`api/users/user-list`, this.$root.config)
                .then(function (response) {
                    me.userList = response.data.users;
                });
        },
    },
    mounted() {
        this.getOrders(1);
        this.listBoxes();
        this.listUsers();
    }
};
</script>
