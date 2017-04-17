<?php

namespace Bisnis\Middleware;

use Pimple\Container;

/**
 * @author Muhamad Surya Iksanudin <surya.iksanudin@bisnis.com>
 */
interface ContainerAwareMiddlewareInterface
{
    /**
     * @param Container $container
     */
    public function setContainer(Container $container);
}
