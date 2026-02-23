<?php

namespace App\Domains\Subscription\Middleware;

use App\Domains\Subscription\Services\SubscriptionService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckSubscription
{
    public function __construct(
        protected SubscriptionService $subscriptionService
    ) {}

    /**
     * Allow request to proceed; redirect to subscription expired page if tenant cannot create transactions.
     * View-only routes are always allowed.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $tenant = tenant();
        if (! $tenant) {
            return $next($request);
        }

        if ($this->subscriptionService->isActive($tenant)) {
            return $next($request);
        }

        if ($request->routeIs('subscription.expired') || $request->routeIs('subscription.*')) {
            return $next($request);
        }

        return redirect()->route('subscription.expired')
            ->with('message', 'Your subscription has expired. You can still view data but cannot create new sales or purchases.');
    }
}
