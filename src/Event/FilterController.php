<?php

namespace KejawenLab\Framework\GarengFramework\Event;

use KejawenLab\Framework\GarengFramework\Controller\ControllerInterface;
use Symfony\Component\EventDispatcher\Event;

/**
 * @author Muhamad Surya Iksanudin <surya.kejawen@gmail.com>
 */
class FilterController extends Event
{
    /**
     * @var ControllerInterface
     */
    private $controller;

    /**
     * @param ControllerInterface $controller
     */
    public function __construct(ControllerInterface $controller)
    {
        $this->controller = $controller;
    }

    /**
     * @return ControllerInterface
     */
    public function getController()
    {
        return $this->controller;
    }
}
