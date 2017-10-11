<?php

namespace KejawenLab\Framework\GarengFramework\Twig;

use KejawenLab\Framework\GarengFramework\DependencyInjection\ContainerAwareInterface;
use KejawenLab\Framework\GarengFramework\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * @author Muhamad Surya Iksanudin <surya.kejawen@gmail.com>
 */
class TwigExtensionMiddleware implements HttpKernelInterface, ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * @var HttpKernelInterface
     */
    private $app;

    /**
     * @param HttpKernelInterface $app
     */
    public function __construct(HttpKernelInterface $app)
    {
        $this->app = $app;
    }

    /**
     * @param Request $request
     * @param int     $type
     * @param bool    $catch
     *
     * @return Response
     */
    public function handle(Request $request, $type = self::MASTER_REQUEST, $catch = true)
    {
        $templateEngine = $this->container['internal.template'];
        if ($templateEngine instanceof \Twig_Environment) {
            $templateEngine->addExtension(new TwigFilterExtension());
        }

        return $this->app->handle($request, $type, $catch);
    }
}
