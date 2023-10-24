<?php
/**
 * @license MIT
 *
 * Modified by The GravityKit Team on 07-September-2023 using Strauss.
 * @see https://github.com/BrianHenryIE/strauss
 */

namespace GravityKit\GravityImport\Foundation\ThirdParty\Illuminate\Support\Facades;

/**
 * @method static mixed guard(string|null $name = null)
 * @method static void shouldUse(string $name);
 * @method static bool check()
 * @method static bool guest()
 * @method static \GravityKit\GravityImport\Foundation\ThirdParty\Illuminate\Contracts\Auth\Authenticatable|null user()
 * @method static int|null id()
 * @method static bool validate(array $credentials = [])
 * @method static void setUser(\GravityKit\GravityImport\Foundation\ThirdParty\Illuminate\Contracts\Auth\Authenticatable $user)
 * @method static bool attempt(array $credentials = [], bool $remember = false)
 * @method static bool once(array $credentials = [])
 * @method static void login(\GravityKit\GravityImport\Foundation\ThirdParty\Illuminate\Contracts\Auth\Authenticatable $user, bool $remember = false)
 * @method static \GravityKit\GravityImport\Foundation\ThirdParty\Illuminate\Contracts\Auth\Authenticatable loginUsingId(mixed $id, bool $remember = false)
 * @method static bool onceUsingId(mixed $id)
 * @method static bool viaRemember()
 * @method static void logout()
 *
 * @see \Illuminate\Auth\AuthManager
 * @see \GravityKit\GravityImport\Foundation\ThirdParty\Illuminate\Contracts\Auth\Factory
 * @see \GravityKit\GravityImport\Foundation\ThirdParty\Illuminate\Contracts\Auth\Guard
 * @see \GravityKit\GravityImport\Foundation\ThirdParty\Illuminate\Contracts\Auth\StatefulGuard
 */
class Auth extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'auth';
    }

    /**
     * Register the typical authentication routes for an application.
     *
     * @return void
     */
    public static function routes()
    {
        static::$app->make('router')->auth();
    }
}
