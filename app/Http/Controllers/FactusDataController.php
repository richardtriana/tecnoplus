<?php

namespace App\Http\Controllers;

use App\Services\FactusApiService;
use Illuminate\Http\Request;
use Exception;

class FactusDataController extends Controller
{
    protected $factus;

    public function __construct(FactusApiService $factusApiService)
    {
        $this->factus = $factusApiService;
    }

    /**
     * Obtiene los tributos de productos.
     * Ejemplo de URL: /factus/tributes/products?name=IVA
     */
    public function getProductTributes(Request $request)
    {
        $name = $request->query('name', ''); // valor opcional para filtrar por nombre

        try {
            $tokenData = $this->factus->getToken();
            $accessToken = $tokenData['access_token'];
            $tributes = $this->factus->getProductTributes($accessToken, $name);
            return response()->json($tributes);
        } catch (Exception $e) {
            return response()->json([
                'error'   => true,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Obtiene las unidades de medida.
     * Ejemplo de URL: /factus/measurement-units?name=kilogramo
     */
    public function getMeasurementUnits(Request $request)
    {
        $name = $request->query('name', '');

        try {
            $tokenData = $this->factus->getToken();
            $accessToken = $tokenData['access_token'];
            $units = $this->factus->getMeasurementUnits($accessToken, $name);
            return response()->json($units);
        } catch (Exception $e) {
            return response()->json([
                'error'   => true,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Obtiene los municipios.
     * Ejemplo de URL: /factus/municipalities?name=Medellin
     */
    public function getMunicipalities(Request $request)
    {
        // Si necesitas filtrar por nombre, lo obtienes de la query
        $name = $request->query('name', '');

        try {
            $tokenData = $this->factus->getToken();
            $accessToken = $tokenData['access_token'];
            $municipalities = $this->factus->getMunicipalities($accessToken, $name);
            return response()->json($municipalities);
        } catch (Exception $e) {
            return response()->json([
                'error'   => true,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Obtiene los rangos de numeración.
     * Puedes enviar filtros en la URL, por ejemplo:
     * /factus/numbering-ranges?filter[document]=Factura de Venta&filter[is_active]=1
     */
    public function getNumberingRanges(Request $request)
    {
        // Se capturan todos los parámetros de consulta enviados
        $filters = $request->query();

        try {
            $tokenData = $this->factus->getToken();
            $accessToken = $tokenData['access_token'];
            $ranges = $this->factus->getNumberingRanges($accessToken, $filters);
            return response()->json($ranges);
        } catch (Exception $e) {
            return response()->json([
                'error'   => true,
                'message' => $e->getMessage()
            ], 400);
        }
    }
}
