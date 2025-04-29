<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:client.index')->only('index', 'show');
        $this->middleware('can:client.store')->only('store');
        $this->middleware('can:client.update')->only('update');
        $this->middleware('can:client.delete')->only('destroy');
        $this->middleware('can:client.active')->only('activate');
    }

    /**
     * Muestra la información de un cliente en detalle.
     */
    public function show($id)
    {
        $client = Client::with([
            'municipality',
            'clientTribute',
            'identityDocumentType',
            'organizationType'
        ])->findOrFail($id);

        return response()->json([
            'status' => 'success',
            'client' => $client
        ]);
    }

    /**
     * Muestra una lista de clientes, con posibilidad de filtrar
     * por nombre, razón social o documento.
     */
    public function index(Request $request)
    {
        // Tomamos el parámetro de búsqueda (opcional)
        $search = $request->input('search');

        // Eager load de las relaciones y filtrado por first_name, razon_social o document
        $clientsQuery = Client::with([
            'municipality',
            'clientTribute',
            'identityDocumentType',
            'organizationType'
        ])->when($search, function ($query) use ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'LIKE', "%{$search}%")
                  ->orWhere('razon_social', 'LIKE', "%{$search}%")
                  ->orWhere('document', 'LIKE', "%{$search}%");
            });
        });

        // Paginación de 15 registros
        $clients = $clientsQuery->paginate(15);

        return response()->json([
            'status' => 'success',
            'code'   => 200,
            'clients'=> $clients
        ]);
    }

    /**
     * Guarda un nuevo cliente en la base de datos.
     */
    public function store(Request $request)
    {
        $clientData = $request->all();
        Client::create($clientData);

        return response()->json([
            'status'  => 'success',
            'message' => 'Cliente creado exitosamente'
        ]);
    }

    /**
     * Actualiza los datos de un cliente.
     */
    public function update(Request $request, $id)
    {
        $client = Client::find($id);

        if ($client) {
            $client->first_name               = $request->first_name;
            $client->second_name              = $request->second_name;
            $client->first_lastname           = $request->first_lastname;
            $client->second_lastname          = $request->second_lastname;
            $client->razon_social             = $request->razon_social;
            $client->address                  = $request->address;
            $client->phone                    = $request->phone;
            $client->email                    = $request->email;
            $client->document                 = $request->document;
            $client->div_verification         = $request->div_verification;
            $client->municipality_id          = $request->municipality_id;
            $client->client_tribute_id        = $request->client_tribute_id;
            $client->identity_document_type_id= $request->identity_document_type_id;
            $client->organization_type_id     = $request->organization_type_id;
            $client->active                   = $request->active;
            $client->save();

            return response()->json([
                'status'  => 'success',
                'message' => 'Cliente actualizado exitosamente'
            ]);
        }

        return response()->json([
            'status'  => 'error',
            'message' => 'Cliente no encontrado'
        ], 404);
    }

    /**
     * Activa/Desactiva un cliente (cambia la columna 'active').
     */
    public function activate($id)
    {
        $client = Client::find($id);
        if ($client) {
            $client->active = !$client->active;
            $client->save();

            return response()->json([
                'status'  => 'success',
                'message' => 'Estado del cliente actualizado'
            ]);
        }

        return response()->json([
            'status'  => 'error',
            'message' => 'Cliente no encontrado'
        ], 404);
    }

    /**
     * Busca un cliente por documento o nombre (solo los activos).
     */
    public function searchClient(Request $request)
    {
        $client = Client::select()
            ->where('active', 1)
            ->where(function ($q) use ($request) {
                $q->where('document', 'LIKE', "%{$request->client}%")
                  ->orWhere('first_name', 'LIKE', "%{$request->client}%");
            })
            ->first();

        return response()->json($client);
    }

    /**
     * Filtra la lista de clientes (solo los activos),
     * limitado a 20 resultados.
     * Se utiliza el método GET para cumplir con los principios REST.
     */
    public function filterClientList(Request $request)
    {
        if (!$request->client || $request->client == '') {
            $clients = Client::select()->where('active', 1)->get();
        } else {
            $clients = Client::select()
                ->where('active', 1)
                ->where(function ($q) use ($request) {
                    $q->where('document', 'LIKE', "%{$request->client}%")
                      ->orWhere('first_name', 'LIKE', "%{$request->client}%");
                })
                ->limit(20)
                ->get();
        }
        return response()->json($clients);
    }
}
