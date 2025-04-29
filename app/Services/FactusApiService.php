<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Exception;

class FactusApiService
{
    protected $baseUrl;
    protected $clientId;
    protected $clientSecret;
    protected $username;
    protected $password;

    public function __construct()
    {
        $this->baseUrl = 'https://api-sandbox.factus.com.co';
        $this->clientId = config('services.factus.client_id');
        $this->clientSecret = config('services.factus.client_secret');
        $this->username = config('services.factus.username');
        $this->password = config('services.factus.password');
    }

    /**
     * Obtiene un token de acceso mediante las credenciales configuradas.
     */
    public function getToken()
    {
        $response = Http::asForm()->post("{$this->baseUrl}/oauth/token", [
            'grant_type'    => 'password',
            'client_id'     => $this->clientId,
            'client_secret' => $this->clientSecret,
            'username'      => $this->username,
            'password'      => $this->password,
        ]);

        if ($response->successful()) {
            return $response->json();
        }

        throw new Exception('No se pudo obtener el token: ' . $response->body());
    }

    /**
     * Refresca el token de acceso utilizando un refresh token.
     *
     * @param string $refreshToken
     */
    public function refreshToken(string $refreshToken)
    {
        $response = Http::asForm()->post("{$this->baseUrl}/oauth/token", [
            'grant_type'    => 'refresh_token',
            'client_id'     => $this->clientId,
            'client_secret' => $this->clientSecret,
            'refresh_token' => $refreshToken,
        ]);

        if ($response->successful()) {
            return $response->json();
        }

        throw new Exception('No se pudo refrescar el token: ' . $response->body());
    }

    /**
     * Obtiene los tributos de productos.
     *
     * @param string $accessToken
     * @param string|null $name Filtro opcional por nombre
     */
    public function getProductTributes(string $accessToken, ?string $name = null)
    {
        $url = "{$this->baseUrl}/v1/tributes/products";

        if (!empty($name)) {
            $url .= '?name=' . urlencode($name);
        }

        $response = Http::withToken($accessToken)
            ->acceptJson()
            ->get($url);

        if ($response->successful()) {
            return $response->json();
        }

        throw new Exception('No se pudo obtener los tributos de productos: ' . $response->body());
    }

    /**
     * Obtiene las unidades de medida.
     *
     * @param string $accessToken
     * @param string|null $name Filtro opcional por nombre
     */
    public function getMeasurementUnits(string $accessToken, ?string $name = null)
    {
        $url = "{$this->baseUrl}/v1/measurement-units";

        if (!empty($name)) {
            $url .= '?name=' . urlencode($name);
        }

        $response = Http::withToken($accessToken)
            ->acceptJson()
            ->get($url);

        if ($response->successful()) {
            return $response->json();
        }

        throw new Exception('No se pudo obtener las unidades de medida: ' . $response->body());
    }

    /**
     * Obtiene los municipios.
     *
     * @param string $accessToken
     * @param string|null $name Filtro opcional por nombre
     */
    public function getMunicipalities(string $accessToken, ?string $name = '')
    {
        $url = "{$this->baseUrl}/v1/municipalities";

        if (!empty($name)) {
            $url .= '?name=' . urlencode($name);
        }

        $response = Http::withToken($accessToken)
            ->acceptJson()
            ->get($url);

        if ($response->successful()) {
            return $response->json();
        }

        throw new Exception('No se pudo obtener los municipios: ' . $response->body());
    }

    /**
     * Obtiene los rangos de numeración.
     *
     * @param string $accessToken
     * @param array $filters Filtros opcionales (por ejemplo, filter[document], filter[is_active], etc.)
     */
    public function getNumberingRanges(string $accessToken, array $filters = [])
    {
        $url = "{$this->baseUrl}/v1/numbering-ranges";

        if (!empty($filters)) {
            $query = http_build_query($filters);
            $url .= '?' . $query;
        }

        $response = Http::withToken($accessToken)
            ->acceptJson()
            ->get($url);

        if ($response->successful()) {
            return $response->json();
        }

        throw new Exception('No se pudo obtener los rangos de numeración: ' . $response->body());
    }

    public function getBaseUrl()
    {
        return $this->baseUrl;
    }

}
