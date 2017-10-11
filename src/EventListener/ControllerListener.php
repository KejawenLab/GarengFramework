<?php

namespace KejawenLab\Framework\GarengFramework\EventListener;

use KejawenLab\Framework\GarengFramework\Api\ApiClientAwareInterface;
use KejawenLab\Framework\GarengFramework\DependencyInjection\ContainerAwareInterface;
use KejawenLab\Framework\GarengFramework\DependencyInjection\ContainerAwareTrait;
use KejawenLab\Framework\GarengFramework\Event\FilterController;
use KejawenLab\Framework\GarengFramework\Template\TemplateAwareInterface;

/**
 * @author Muhamad Surya Iksanudin <surya.kejawen@gmail.com>
 */
class ControllerListener implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * @param FilterController $event
     */
    public function filterController(FilterController $event)
    {
        $controller = $event->getController();

        if ($controller instanceof TemplateAwareInterface) {
            $controller->setTemplate($this->container['internal.template']);
        }

        if ($controller instanceof ApiClientAwareInterface) {
            $controller->setClient($this->container['internal.http_client']);
        }

        if ($controller instanceof ContainerAwareInterface) {
            $controller->setContainer($this->container);
        }
    }
}
