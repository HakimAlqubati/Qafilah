<?php

namespace App\Http\Middleware;

use Filament\Http\Middleware\Authenticate as BaseAuthenticate;
use Filament\Facades\Filament;
use Filament\Models\Contracts\FilamentUser;
use Illuminate\Database\Eloquent\Model;

class CustomAdminFilamentAuthenticate extends BaseAuthenticate
{
    protected function authenticate($request, array $guards): void
    {
        $guard = Filament::auth();

        if (! $guard->check()) {
            $this->unauthenticated($request, $guards);
            return;
        }

        $this->auth->shouldUse(Filament::getAuthGuard());

        /** @var Model $user */
        $user = $guard->user();

        $panel = Filament::getCurrentOrDefaultPanel();

        // التحقق من أن المستخدم هو Admin (ليس لديه vendor_id)
        if ($user->vendor_id) {
            abort(403, 'Access denied. Admins only.');
        }

        // يمكنك إضافة تحققات إضافية هنا
        // مثال: التحقق من دور المستخدم
        // if (!$user->hasRole('admin')) {
        //     abort(403, 'You do not have admin privileges.');
        // }
    }

    protected function redirectTo($request): ?string
    {
        return Filament::getLoginUrl();
    }
}
