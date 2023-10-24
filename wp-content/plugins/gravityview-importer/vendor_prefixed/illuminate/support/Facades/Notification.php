<?php
/**
 * @license MIT
 *
 * Modified by The GravityKit Team on 07-September-2023 using Strauss.
 * @see https://github.com/BrianHenryIE/strauss
 */

namespace GravityKit\GravityImport\Foundation\ThirdParty\Illuminate\Support\Facades;

use Illuminate\Notifications\ChannelManager;
use GravityKit\GravityImport\Foundation\ThirdParty\Illuminate\Support\Testing\Fakes\NotificationFake;

/**
 * @see \Illuminate\Notifications\ChannelManager
 */
class Notification extends Facade
{
    /**
     * Replace the bound instance with a fake.
     *
     * @return void
     */
    public static function fake()
    {
        static::swap(new NotificationFake);
    }

    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return ChannelManager::class;
    }
}
