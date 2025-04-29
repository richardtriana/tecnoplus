<?php

namespace App\Http\Controllers;

use App\Services\FactusApiService;
use App\Models\NumberingRange;
use App\Models\Municipality;
use App\Models\ProductTribute;
use App\Models\MeasurementUnit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class FactusSyncController extends Controller
{
    protected $factus;

    public function __construct(FactusApiService $factusApiService)
    {
        $this->factus = $factusApiService;
    }

    public function syncNumberingRanges()
    {
        try {
            $tokenData = $this->factus->getToken();
            $accessToken = $tokenData['access_token'];

            $apiData = $this->factus->getNumberingRanges($accessToken);
            Log::info('Datos de Rangos de Numeración:', $apiData);
            $count = 0;
            if (isset($apiData['data']) && is_array($apiData['data'])) {
                foreach ($apiData['data'] as $item) {
                    NumberingRange::updateOrCreate(
                        ['id' => $item['id']],
                        [
                            'document'          => $item['document'],
                            'prefix'            => $item['prefix'],
                            'from'              => $item['from'],
                            'to'                => $item['to'],
                            'current'           => $item['current'],
                            'resolution_number' => $item['resolution_number'] ?? null,
                            'start_date'        => isset($item['start_date']) ? $this->parseDate($item['start_date']) : null,
                            'end_date'          => isset($item['end_date']) ? $this->parseDate($item['end_date']) : null,
                            'technical_key'     => $item['technical_key'] ?? null,
                            'is_expired'        => $item['is_expired'] ?? false,
                            'is_active'         => $item['is_active'] ?? true,
                        ]
                    );
                    $count++;
                }
            }
            return response()->json([
                'message' => 'Sincronización de rangos completada',
                'count'   => $count
            ]);
        } catch (\Exception $e) {
            Log::error('Error sincronizando Rangos de Numeración: '.$e->getMessage());
            return response()->json([
                'message' => 'Error sincronizando rangos de numeración',
                'error'   => $e->getMessage()
            ], 400);
        }
    }

    public function syncMunicipalities()
    {
        try {
            $tokenData = $this->factus->getToken();
            $accessToken = $tokenData['access_token'];

            $apiData = $this->factus->getMunicipalities($accessToken);
            Log::info('Datos de Municipios:', $apiData);
            $count = 0;
            if (isset($apiData['data']) && is_array($apiData['data'])) {
                foreach ($apiData['data'] as $item) {
                    Municipality::updateOrCreate(
                        ['id' => $item['id']],
                        [
                            'code'       => $item['code'],
                            'name'       => $item['name'],
                            'department' => $item['department'],
                        ]
                    );
                    $count++;
                }
            }
            return response()->json([
                'message' => 'Sincronización de municipios completada',
                'count'   => $count
            ]);
        } catch (\Exception $e) {
            Log::error('Error sincronizando Municipios: '.$e->getMessage());
            return response()->json([
                'message' => 'Error sincronizando municipios',
                'error'   => $e->getMessage()
            ], 400);
        }
    }

    public function syncTributes()
    {
        try {
            $tokenData = $this->factus->getToken();
            $accessToken = $tokenData['access_token'];

            $apiData = $this->factus->getProductTributes($accessToken);
            Log::info('Datos de Tributos de Productos:', $apiData);
            $count = 0;
            if (isset($apiData['data']) && is_array($apiData['data'])) {
                foreach ($apiData['data'] as $item) {
                    ProductTribute::updateOrCreate(
                        ['id' => $item['id']],
                        [
                            'code'        => $item['code'],
                            'name'        => $item['name'],
                            'description' => $item['description'] ?? null
                        ]
                    );
                    $count++;
                }
            }
            return response()->json([
                'message' => 'Sincronización de tributos completada',
                'count'   => $count
            ]);
        } catch (\Exception $e) {
            Log::error('Error sincronizando Tributos: '.$e->getMessage());
            return response()->json([
                'message' => 'Error sincronizando tributos de productos',
                'error'   => $e->getMessage()
            ], 400);
        }
    }

    public function syncMeasurementUnits()
    {
        try {
            $tokenData = $this->factus->getToken();
            $accessToken = $tokenData['access_token'];

            $apiData = $this->factus->getMeasurementUnits($accessToken);
            Log::info('Datos de Unidades de Medida:', $apiData);
            $count = 0;
            if (isset($apiData['data']) && is_array($apiData['data'])) {
                foreach ($apiData['data'] as $item) {
                    MeasurementUnit::updateOrCreate(
                        ['id' => $item['id']],
                        [
                            'code' => $item['code'],
                            'name' => $item['name'],
                        ]
                    );
                    $count++;
                }
            }
            return response()->json([
                'message' => 'Sincronización de unidades de medida completada',
                'count'   => $count
            ]);
        } catch (\Exception $e) {
            Log::error('Error sincronizando Unidades de Medida: '.$e->getMessage());
            return response()->json([
                'message' => 'Error sincronizando unidades de medida',
                'error'   => $e->getMessage()
            ], 400);
        }
    }

    public function localStats()
    {
        return response()->json([
            'numberingRanges' => NumberingRange::count(),
            'municipalities'  => Municipality::count(),
            'tributes'        => ProductTribute::count(),
            'units'           => MeasurementUnit::count()
        ]);
    }

    private function parseDate($dateStr)
    {
        try {
            return Carbon::createFromFormat('d-m-Y', $dateStr)->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }
}
