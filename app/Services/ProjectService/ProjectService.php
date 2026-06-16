<?php
declare(strict_types=1);

namespace App\Services\ProjectService;

use App\Models\Shop;
use App\Services\CoreService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class ProjectService extends CoreService
{
    private string $url = 'https://demo.githubit.com/api/v2/server/notification';

    /**
     * @return string
     */
    protected function getModelClass(): string
    {
        return Shop::class;
    }

    public function activationKeyCheck(string|null $code = null, string|null $id = null): bool|string
    {
        if (!$this->checkLocal()) {

            $params = [
                'code'  => !empty($code) ? $code : config('credential.purchase_code'),
                'id'    => !empty($id) ? $id : config('credential.purchase_id'),
                'ip'    => request()->server('SERVER_ADDR'),
                'host'  => request()->getSchemeAndHttpHost()
            ];

            $response = Http::post($this->url, $params);

            return $response->body();
        }

        return json_encode([
            'local'     => true,
            'active'    => true,
            'key'       => config('credential.purchase_code'),
        ]);
    }

    public function checkLocal(): bool
    {
        if (app()->environment('local')) {
            return true;
        }

        $remoteAddr = $_SERVER['REMOTE_ADDR'] ?? '';
        $httpHost   = $_SERVER['HTTP_HOST'] ?? '';

        if ($remoteAddr === '127.0.0.1'
            || $httpHost === 'localhost'
            || str_starts_with($httpHost, '127.0.0.1')
            || str_starts_with($httpHost, '10.')
            || str_starts_with($httpHost, '192.168.')) {
            return true;
        }

        return false;
    }

    public function ensureLicenceCache(): void
    {
        $cached = Cache::get('rjkcvd.ewoidfh');

        if ($cached && data_get($cached, 'active')) {
            return;
        }

        Cache::remember('rjkcvd.ewoidfh', 302400, function () {
            $response = json_decode($this->activationKeyCheck());

            if (
                data_get($response, 'active') &&
                data_get($response, 'key') == config('credential.purchase_code')
            ) {
                return json_decode(json_encode($response), true);
            }

            return null;
        });
    }
}
