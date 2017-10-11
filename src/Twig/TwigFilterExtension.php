<?php

namespace KejawenLab\Framework\GarengFramework\Twig;

/**
 * @author Muhamad Surya Iksanudin <surya.kejawen@gmail.com>
 */
class TwigFilterExtension extends \Twig_Extension
{
    /**
     * @return array
     */
    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter('ucwords', function ($value) { return ucwords(strtolower($value)); }),
            new \Twig_SimpleFilter('ucfirst', function ($value) { return ucfirst($value); }),
        ];
    }
}
