<?php
/**
 * @license MIT
 *
 * Modified by __root__ on 19-October-2023 using Strauss.
 * @see https://github.com/BrianHenryIE/strauss
 */

namespace GravityKit\GravityEdit\Foundation\ThirdParty\Illuminate\Support\Facades;

use GravityKit\GravityEdit\Foundation\ThirdParty\Illuminate\Support\Testing\Fakes\MailFake;

/**
 * @see \Illuminate\Mail\Mailer
 */
class Mail extends Facade
{
    /**
     * Replace the bound instance with a fake.
     *
     * @return void
     */
    public static function fake()
    {
        static::swap(new MailFake);
    }

    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'mailer';
    }
}
