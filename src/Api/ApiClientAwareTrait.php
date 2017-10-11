<?php

namespace KejawenLab\Framework\GarengFramework\Api;

use Symfony\Component\HttpFoundation\Response;

/**
 * @author Muhamad Surya Iksanudin <surya.kejawen@gmail.com>
 */
trait ApiClientAwareTrait
{
    /**
     * @var ClientInterface
     */
    protected $client;

    /**
     * @param ClientInterface $client
     */
    public function setClient(ClientInterface $client)
    {
        $this->client = $client;
    }

    /**
     * @param string $token
     */
    public function bearer($token)
    {
        $this->client->bearer($token);
    }

    /**
     * @param mixed $key
     * @param mixed $value
     */
    public function store($key, $value)
    {
        $this->client->store($key, $value);
    }

    /**
     * @param mixed $key
     * @param null  $default
     *
     * @return mixed
     */
    public function fetch($key, $default = null)
    {
        return $this->client->fetch($key, $default);
    }

    /**
     * @param mixed $key
     */
    public function remove($key)
    {
        $this->client->remove($key);
    }
    
    public function removeAll()
    {
        $this->client->removeAll();
    }

    /**
     * @param string $url
     * @param array  $options
     *
     * @return Response
     */
    public function get($url, array $options = [])
    {
        return $this->client->get($url, $options);
    }

    /**
     * @param $url
     * @param array $options
     *
     * @return Response
     */
    public function post($url, array $options = [])
    {
        return $this->client->post($url, $options);
    }

    /**
     * @param string $url
     * @param array  $options
     *
     * @return Response
     */
    public function put($url, array $options = [])
    {
        return $this->client->put($url, $options);
    }

    /**
     * @param string $url
     * @param array  $options
     *
     * @return Response
     */
    public function delete($url, array $options = [])
    {
        return $this->client->delete($url, $options);
    }
}
