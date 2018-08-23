<?php

namespace MainBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * UserEnvironment
 *
 * @ORM\Table(name="user_environment")
 * @ORM\Entity(repositoryClass="MainBundle\Repository\UserEnvironmentRepository")
 */
class UserEnvironment
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="Platform", type="string", length=255)
     */
    private $platform;

    /**
     * @var string
     *
     * @ORM\Column(name="IPAddress", type="string", length=25, nullable=true)
     */
    private $iPAddress;
    
    /**
     * @ORM\ManyToOne(targetEntity="Browser", inversedBy="userEnvironments")
     * @ORM\JoinColumn(name="userEnvironment_id", referencedColumnName="id")
     */
    private $browser;
    
     /**
     * @ORM\OneToMany(targetEntity="Visitor", mappedBy="userEnvironment")
     */
    private $visitors;
    
    public function __construct()
    {
        $this->visitors = new ArrayCollection();
    }

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set platform
     *
     * @param string $platform
     *
     * @return UserEnvironment
     */
    public function setPlatform($platform)
    {
        $this->platform = $platform;

        return $this;
    }

    /**
     * Get platform
     *
     * @return string
     */
    public function getPlatform()
    {
        return $this->platform;
    }

    /**
     * Set iPAddress
     *
     * @param string $iPAddress
     *
     * @return UserEnvironment
     */
    public function setIPAddress($iPAddress)
    {
        $this->iPAddress = $iPAddress;

        return $this;
    }

    /**
     * Get iPAddress
     *
     * @return string
     */
    public function getIPAddress()
    {
        return $this->iPAddress;
    }
    
    /**
     * Set browser
     *
     * @param Browser $browser
     *
     * @return UserEnvironment
     */
    public function setBrowser($browser)
    {
        $this->browser = $browser;
        return $this;
    }
    
     /**
     * Get Browser
     *
     * @return Browser
     */
    public function getBrowser()
    {
        return $this->browser;
    }
}

