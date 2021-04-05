<?php

namespace App\Services;

use App\Models\Session;
use Illuminate\Support\Facades\Cache;

class StudentActiveSession
{
    public static $cacheKey = 'Models\Session\Active';

    /**
     * @return \App\Models\Session $session
     */
    public function getInstance()
    {
        return self::instance();
    }

    /**
     * @return int
     */
    public function id()
    {
        return $this->getInstance()->id ?? null;
    }

    /**
     * @return bool
     */
    public function clear()
    {
        return Cache::forget(static::$cacheKey);
    }

    public static function setActive(Session $session)
    {
        Cache::put(static::$cacheKey, $session, config('cache.stored-expiry'));
    }

    private static function instance()
    {
        if (\config('app.env') != 'testing') {
            static $session;

            if ($session != null) {
                return $session;
            }
        }

        $session = Cache::get(static::$cacheKey);

        if ($session != null) {
            return $session;
        }

        $session = (new Session())->getActive();
        Cache::add(static::$cacheKey, $session, config('cache.stored-expiry'));

        return $session;
    }
}
