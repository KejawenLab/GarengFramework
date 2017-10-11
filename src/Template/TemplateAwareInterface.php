<?php

namespace KejawenLab\Framework\GarengFramework\Template;

/**
 * @author Muhamad Surya Iksanudin <surya.kejawen@gmail.com>
 */
interface TemplateAwareInterface
{
    public function setTemplate(TemplateEngineInterface $templateEngine);
}
