<?php
declare(strict_types=1);

namespace App\Http\Middleware;

use App\Helpers\ResponseError;
use App\Models\User;
use App\Services\ProjectService\ProjectService;
use App\Traits\ApiResponse;
use Closure;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class CheckSellerShop
{
    use ApiResponse;

    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure(Request): (Response|RedirectResponse) $next
     * @return JsonResponse
     * @throws Exception
     */
    public function handle(Request $request, Closure $next): JsonResponse
    {
        (new ProjectService)->ensureLicenceCache();

        if (!Cache::get('rjkcvd.ewoidfh') || !data_get(Cache::get('rjkcvd.ewoidfh'), 'active')) {
            return $this->onErrorResponse([
                'code' => ResponseError::ERROR_403,
                'http' => HttpResponse::HTTP_FORBIDDEN,
            ]);
        }

        if (!auth('sanctum')->check()) {
            return $this->onErrorResponse(['code' => ResponseError::ERROR_100]);
        }

        /** @var User $user */
        $user = auth('sanctum')->user();

        if ($user?->shop && $user?->role == 'seller') {
            return $next($request);
        }

        if ($user?->moderatorShop && $user?->role == 'moderator' || $user?->role == 'deliveryman') {
            return $next($request);
        }

        if ($user?->shop && $user?->role == 'admin') {
            return $next($request);
        }

        return $this->onErrorResponse(['code' => ResponseError::ERROR_204]);
    }
}
