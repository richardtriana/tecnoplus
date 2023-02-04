<!DOCTYPE html>
<html lang="en">

<head>
    <meta http-equiv="Content-Type" content="text/css" charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orden {{$creditInformation->id}}</title>
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
        }
    </style>
</head>

<body>
    @if ($configuration)
    <header>

        <table>

            <tr>
                <td rowspan="3">
                    <img class="logo" src="{{ $url.'/'.$configuration->logo}}" alt="logo">
                </td>

                <th>Entidad</th>
                <td>{{ $configuration->name}}</td>
            </tr>
            <tr>

                <th>Representante</th>
                <td>{{$configuration->legal_representative}}</td>
            </tr>
            <tr>

                <th>Nit</th>
                <td>{{$configuration->nit}}</td>
            </tr>
        </table>

    </header>
    @endif
    <section>
        <div>
            <h4 class="text-center">Detalles de Orden</h4>
            <table class="table">
                <tbody>
                    <tr>
                        <td>No. Factura</td>
                        <th>
                            {{ $creditInformation->no_invoice }}
                        </th>
                    </tr>
                    <tr>
                        <td>Fecha</td>
                        <th>
                            {{ $creditInformation->updated_at }}
                        </th>
                    </tr>
                    <tr>
                        <th colspan="2" class="text-center">Proveedor</th>
                    </tr>
                    <tr>
                        <td>Nombres:</td>
                        <th>
                            {{ $creditInformation->client->name }}
                        </th>
                    </tr>
                    <tr>
                        <td>Documento / Nit:</td>
                        <th>
                            {{ $creditInformation->client->document }}
                        </th>
                    </tr>
                    <tr>
                        <td>Direccion</td>
                        <td>{{ $creditInformation->client->address }}</td>
                    </tr>
                    <tr>
                        <td>Email</td>
                        <td>{{ $creditInformation->client->email }}</td>
                    </tr>
                    <tr>
                        <td>Celular / Télefono</td>
                        <td>{{ $creditInformation->client->mobile }}</td>
                    </tr>
                    <tr>
                        <td colspan="6">Subtotal</td>
                        <td class="text-right">
                            $ {{ $creditInformation->total_iva_exc }}
                        </td>
                    </tr>
                    <tr>
                        <td colspan="6">Descuento</td>
                        <td class="text-right">
                            $ {{ $creditInformation->total_discount }}
                        </td>
                    </tr>
                    <tr>
                        <td colspan="6">Total</td>
                        <th class="text-right">
                            $ {{ $creditInformation->total_iva_inc }}
                        </th>
                    </tr>
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
                </tbody>
            </table>
        </div>
        <br>
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
                    @foreach ( $creditInformation->paymentCredits as $key => $i)
                    <tr>
                        @if (!$payment_id)
                        <td>{{ $key + 1 }}</td>
                        <td>$ {{ $i->pay }}</td>
                        <td>{{ $i->created_at }}</td>
                        @endif
                        @if ($payment_id && $payment_id == $i->id)
                        <td>{{ $key + 1 }}</td>
                        <td>$ {{ $i->pay }}</td>
                        <td>{{ $i->created_at }}</td>
                        @endif

                    </tr>
                    @endforeach
                </tbody>
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