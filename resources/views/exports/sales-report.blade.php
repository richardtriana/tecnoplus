<table>
  <thead>
    <tr>
      <th>ID</th><th>Fecha</th><th>Factura</th><!-- etc... --><th>Estado Dian</th>
    </tr>
  </thead>
  <tbody>
  @foreach($orders as $o)
    <tr>
      <td>{{ $o->id }}</td>
      <td>{{ $o->created_at }}</td>
      <td>{{ $o->bill_number }}</td>
      <!-- ... resto de columnas ... -->
      <td>{{ $o->status_dian ? 'Recibida' : 'No recibida' }}</td>
    </tr>
  @endforeach
  </tbody>
</table>
