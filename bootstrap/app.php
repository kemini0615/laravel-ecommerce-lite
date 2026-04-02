<?php

use App\Http\Middleware\AuthenticateAdmin;
use App\Http\Middleware\AuthenticateUser;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php', // 기본 웹 라우트 파일 등록 (web 미들웨어 그룹 자동 적용)
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',

        // web 라우트 로드 후 추가로 등록할 라우트
        then: function () {
            Route::middleware('web')
                ->prefix('admin')
                ->name('admin.')
                ->group(base_path('routes/admin.php'));
        }
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // 미들웨어 별칭 등록
        $middleware->alias([
            'user' => AuthenticateUser::class,
            'admin' => AuthenticateAdmin::class,
        ]);

        // guest 미들웨어(RedirectIfAuthenticated)의 동작을 커스터마이징
        // 이미 로그인한 유저가 guest 전용 페이지(login, register 등)에 접근할 때 어디로 리다이렉트할지 정의
        $middleware->redirectUsersTo(function () {
            if (Auth::guard('admin')->check()) {
                return redirect()->route('admin.dashboard');
            }

            return redirect()->route('user.dashboard');
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
