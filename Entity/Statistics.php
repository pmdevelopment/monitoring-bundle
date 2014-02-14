<?php

namespace PM\Bundle\MonitoringBundle\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * Statistics
 *
 * @ORM\Table(name="pm_monitoring_statistics")
 * @ORM\Entity
 */
class Statistics
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="created", type="datetime")
     */
    private $created;

    /**
     * @var array
     *
     * @ORM\Column(name="data", type="json_array")
     */
    private $data;


    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set created
     *
     * @param DateTime $created
     * @return Statistics
     */
    public function setCreated($created)
    {
        $this->created = $created;
    
        return $this;
    }

    /**
     * Get created
     *
     * @return DateTime 
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set data
     *
     * @param array $data
     * @return Statistics
     */
    public function setData($data)
    {
        $this->data = $data;
    
        return $this;
    }

    /**
     * Get data
     *
     * @return array 
     */
    public function getData()
    {
        return $this->data;
    }
}
