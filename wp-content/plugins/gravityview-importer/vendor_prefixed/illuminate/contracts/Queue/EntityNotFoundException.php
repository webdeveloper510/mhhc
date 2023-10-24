<?php
/**
 * @license MIT
 *
 * Modified by The GravityKit Team on 07-September-2023 using Strauss.
 * @see https://github.com/BrianHenryIE/strauss
 */

namespace GravityKit\GravityImport\Foundation\ThirdParty\Illuminate\Contracts\Queue;

use InvalidArgumentException;

class EntityNotFoundException extends InvalidArgumentException
{
    /**
     * Create a new exception instance.
     *
     * @param  string  $type
     * @param  mixed  $id
     * @return void
     */
    public function __construct($type, $id)
    {
        $id = (string) $id;

        parent::__construct("Queueable entity [{$type}] not found for ID [{$id}].");
    }
}
