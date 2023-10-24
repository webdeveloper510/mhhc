<?php
/**
 * @license MIT
 *
 * Modified by GravityKit on 25-September-2023 using Strauss.
 * @see https://github.com/BrianHenryIE/strauss
 */ declare(strict_types=1);

namespace GFExcel\Vendor\League\Container\ServiceProvider;

interface BootableServiceProviderInterface extends ServiceProviderInterface
{
    /**
     * Method will be invoked on registration of a service provider implementing
     * this interface. Provides ability for eager loading of Service Providers.
     *
     * @return void
     */
    public function boot();
}
