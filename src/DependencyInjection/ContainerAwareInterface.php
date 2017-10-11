<?php

namespace KejawenLab\Framework\GarengFramework\DependencyInjection;

use Pimple\Container;

/**
 * @author Muhamad Surya Iksanudin <surya.kejawen@gmail.com>
 */
interface ContainerAwareInterface
{
    /**
     * @param Container $container
     */
    public function setContainer(Container $container);
}
