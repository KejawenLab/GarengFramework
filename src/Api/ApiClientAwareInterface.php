<?php

namespace KejawenLab\Framework\GarengFramework\Api;

/**
 * @author Muhamad Surya Iksanudin <surya.kejawen@gmail.com>
 */
interface ApiClientAwareInterface
{
    /**
     * @param ClientInterface $client
     */
    public function setClient(ClientInterface $client);
}
