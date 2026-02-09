<?php

namespace App\Http\Middleware;

use Filament\Http\Middleware\Authenticate as BaseAuthenticate;
use Filament\Facades\Filament;
use Filament\Models\Contracts\FilamentUser;
use Illuminate\Database\Eloquent\Model;
use App\Enums\UserTypes;

// نرث من BaseAuthenticate بدلاً من Middleware العادي
class CustomFilamentAuthenticate extends BaseAuthenticate
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

        // التحقق من أن المستخدم هو تاجر (merchant)
        $userType = $user->getRawOriginal('user_type');
        if ($userType !== UserTypes::MERCHANT->value) {
            abort(403, 'Access denied. Merchants only.');
        }
        // --- هنا يمكنك وضع التعديلات الخاصة بك ---

        // مثال: التحقق مما إذا كان المستخدم محظوراً
        // if ($user->is_banned) {
        //    abort(403, 'Your account is banned.');
        // } 

    }

    // يمكنك أيضاً تعديل التوجيه عند عدم تسجيل الدخول إذا أردت
    protected function redirectTo($request): ?string
    {
        return Filament::getLoginUrl();
    }
}
