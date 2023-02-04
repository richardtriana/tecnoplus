<!DOCTYPE html>
<html lang="en">

<head>
    <meta http-equiv="Content-Type" content="text/css" charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orden {{$orderInformation->id}}</title>
    <link rel="stylesheet" href="myProjects/webProject/icofont/css/icofont.min.css">
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 12px;
        }

        header {
            width: 100%;

        }

        .logo {
            width: auto;
            height: 80px;
        }

        .table {
            border-collapse: collapse;
            width: 100%;
        }

        .table th,
        .table td {
            border: 1px solid #E4E4E4;
            padding: 5px;
        }

        .table th,
        table th {
            text-align: left;
        }

        .detail {
            margin-top: 10px;
        }

        .text-center {
            text-align: center !important;
        }

        .text-right {
            text-align: right !important;
        }

        tfoot tr {
            background-color: #E4E4E4;
        }

        .icon {
            width: 17px;
        }

        footer {
            color: white;
            background-color: #19AC0D;
            padding: 5px auto;
        }
    </style>
</head>

<body>
    @if ($configuration)
    <header>

        <table width="100%">
            <tr align="center">
                <td colspan="5" align="center">
                    <img class="logo" src="{{ $url.'/'.$configuration->logo}}" alt="logo" width="150">
                    <p style="padding: 0; margin:0">{{ $configuration->name}}</p>
                    <p style="padding: 0; margin:0">Representante: {{$configuration->legal_representative}}</p>
                    <p style="padding: 0; margin:0">Nit: {{$configuration->nit}}</p>
                </td>
            </tr>
        </table>

    </header>
    @endif
    <section>
        <div>
            <h4 class="text-center">Factura {{ $orderInformation->bill_number }}</h4>
            <table class="table">
                <tbody>
                    <tr>
                        <td>Fecha</td>
                        <th>
                            {{ $orderInformation->payment_date }}
                        </th>
                    </tr>
                    <tr>
                        <td>Cliente:</td>
                        <th>
                            {{ $orderInformation->client->name }}
                        </th>
                    </tr>
                    <tr>
                        <td>Documento / Nit:</td>
                        <th>
                            {{ $orderInformation->client->document }}
                        </th>
                    </tr>
                    <tr>
                        <td>Dirección</td>
                        <td>{{ $orderInformation->client->address }}</td>
                    </tr>
                    <tr>
                        <td>Email</td>
                        <td>{{ $orderInformation->client->email }}</td>
                    </tr>
                    <tr>
                        <td>Celular / Teléfono</td>
                        <td>{{ $orderInformation->client->mobile }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        @if ( count($orderInformation->paymentCredits) )

        <div class="detail">
            <h4 class="text-center">Abonos</h4>
            <table class="table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Abono</th>
                        <th>Fecha</th>
                    </tr>
                </thead>
                <tbody class="">
                    @foreach ( $orderInformation->paymentCredits as $key => $i)
                    <tr>
                        <td>{{ $key + 1 }}</td>
                        <td>$ {{ $i->pay }}</td>
                        <td>{{ $i->created_at }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
        <div class="detail">
            <h4 class="text-center">Detalles</h4>
            <table class="table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Código de barras</th>
                        <th>Producto</th>
                        <th>Cantidad</th>
                        <th>Precio con IVA</th>
                        <th>Descuento $</th>
                        <th>Precio Total</th>
                    </tr>
                </thead>
                <tbody class="">
                    @foreach ( $orderDetails as $key => $i)
                    <tr>
                        <td>{{ $key + 1 }}</td>
                        <td>{{ $i->barcode }}</td>
                        <td>{{ $i->product }}</td>
                        <td>{{ $i->quantity }}</td>
                        <td>$ {{ $i->price_tax_inc }}</td>
                        <td>$ {{ $i->discount_price }}</td>
                        <td class="text-right">$ {{ $i->price_tax_inc_total }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot class="table-secondary">
                    <tr>
                        <td colspan="6">Subtotal</td>
                        <td class="text-right">
                            $ {{ $orderInformation->total_iva_exc }}
                        </td>
                    </tr>
                    <tr>
                        <td colspan="6">Descuento</td>
                        <td class="text-right">
                            $ {{ $orderInformation->total_discount }}
                        </td>
                    </tr>
                    <tr>
                        <td colspan="6">Total</td>
                        <th class="text-right">
                            $ {{ $orderInformation->total_iva_inc }}
                        </th>
                    </tr>
                    @if ( count($orderInformation->paymentCredits) )
                    <tr>
                        <td colspan="6">Abono</td>
                        <th class="text-right">
                            $ {{ $creditInformation->paid_payment }}
                        </th>
                    </tr>
                    <tr>
                        <td colspan="6">Saldo</td>
                        <th class="text-right">
                            $ {{ $creditInformation->total_iva_inc - $creditInformation->paid_payment}}
                        </th>
                    </tr>
                    @endif
                </tfoot>
            </table>
        </div>
    </section>
    @if ($configuration)
    <br>
    <br>
    <div>
        <h4>Condiciones:</h4>
        <p>
            {!! $configuration->condition_quotation !!}
        </p>
    </div>
    <div class="text-center">
        @if ($consecutiveBox)

        <p>Prefijo: {{ $orderInformation->box->prefix}}</p>
        <p>
            {{
            "De No. ".$consecutiveBox->from_nro." AL ".$consecutiveBox->until_nro." Autoriza"
            }}
        </p>
        <p>{{ $consecutive_expires }}</p>
        @endif
    </div>
    <footer class="text-center">
        <h3>Informacion de contacto</h3>

        <p>
            <img class="icon" src="{{ $url.'/images/'.'email.png'}}">
            {{$configuration->email}}
        </p>
        <p>
            <img class="icon" src="{{ $url.'/images/'.'phone.png'}}">
            {{$configuration->telephone.' - '.$configuration->mobile}}
        </p>
        <p>
            <img class="icon" src="{{ $url.'/images/'.'location-pin.png'}}">
            {{$configuration->address}}
        </p>

    </footer>

    @endif
</body>

</html>