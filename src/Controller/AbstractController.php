<?php

namespace KejawenLab\Framework\GarengFramework\Controller;

use KejawenLab\Framework\GarengFramework\Api\ApiClientAwareInterface;
use KejawenLab\Framework\GarengFramework\Api\ApiClientAwareTrait;
use KejawenLab\Framework\GarengFramework\Template\TemplateAwareInterface;
use KejawenLab\Framework\GarengFramework\Template\TemplateAwareTrait;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author Muhamad Surya Iksanudin <surya.kejawen@gmail.com>
 */
abstract class AbstractController implements ControllerInterface, TemplateAwareInterface, ApiClientAwareInterface
{
    use TemplateAwareTrait;
    use ApiClientAwareTrait;

    /**
     * @param string $url
     * @param string $method
     * @param array  $data
     *
     * @return Response
     */
    protected function request($url, $method = 'GET', array $data = [])
    {
        try {
            return call_user_func_array([$this, strtolower($method)], [$url, $data]);
        } catch (\Exception $exception) {
            throw new \RuntimeException('Http method is not valid.');
        }
    }

    /**
     * @param string $token
     */
    protected function auth($token)
    {
        $this->client->bearer($token);
    }
}
