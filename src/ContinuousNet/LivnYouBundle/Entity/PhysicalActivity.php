<?php

namespace ContinuousNet\LivnYouBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Events;
use Doctrine\Common\Collections\ArrayCollection as DoctrineCollection;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

;
use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Exclude;
use JMS\Serializer\Annotation\Expose;
use JMS\Serializer\Annotation\MaxDepth;
use JMS\Serializer\Annotation\Groups;

/**
 * Physical Activity Entity
 *
 * Storing PhysicalActivities data to the database using Doctrine
 *
 * PHP version 5.4.4
 *
 * @category   Doctrine 2 Entity
 * @package    ContinuousNet\LivnYouBundle\Entity
 * @author     Sahbi KHALFALLAH <sahbi.khalfallah@continuousnet.com>
 * @copyright  2017 CONTINUOUS NET
 * @license	CONTINUOUS NET REGULAR LICENSE
 * @version    Release: 1.0
 * @link       http://livnyou.continuousnet.com/ContinuousNet/LivnYouBundle/Entity
 * @see        PhysicalActivity
 * @since      Class available since Release 1.0
 * @access     public
 *
 * @ORM\Table(name="`physical_activity`", indexes={@ORM\Index(name="creator_user_id", columns={"creator_user_id"}), @ORM\Index(name="modifier_user_id", columns={"modifier_user_id"})})
 * @ORM\Entity(repositoryClass="ContinuousNet\LivnYouBundle\Repository\PhysicalActivityRepository")
 * @ORM\HasLifecycleCallbacks()
 *
 * @ExclusionPolicy("none")
 *
 */
class PhysicalActivity
{
    /**
     * @var integer
     * @access private
     *
     * @ORM\Column(name="id", type="integer", nullable=false, unique=true)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     *
     * @Expose
     *
     */
    private $id;

    /**
     * @var string
     * @access private
     *
     * @ORM\Column(name="`name`", type="string", length=50, nullable=false, unique=false)
     *
     * @Expose
     *
     */
    private $name;

    /**
     * @var string
     * @access private
     *
     * @ORM\Column(name="`athletic_name`", type="string", length=50, nullable=true, unique=false)
     *
     * @Expose
     *
     */
    private $athleticName;

    /**
     * @var float
     * @access private
     *
     * @ORM\Column(name="`value`", type="float", precision=10, scale=0, nullable=false, unique=false)
     *
     * @Expose
     *
     */
    private $value;

    /**
     * @var float
     * @access private
     *
     * @ORM\Column(name="`energy_needs_rate`", type="float", precision=10, scale=0, nullable=false, unique=false)
     *
     * @Expose
     *
     */
    private $energyNeedsRate;

    /**
     * @var \DateTime
     * @access private
     *
     * @ORM\Column(name="`created_at`", type="datetime", nullable=false, unique=false)
     *
     * @Expose
     *
     */
    private $createdAt;

    /**
     * @var \DateTime
     * @access private
     *
     * @ORM\Column(name="`modified_at`", type="datetime", nullable=true, unique=false)
     *
     * @Expose
     *
     */
    private $modifiedAt;

    /**
     * @var \ContinuousNet\LivnYouBundle\Entity\User
     * @access private
     *
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumns({
     *        @ORM\JoinColumn(name="creator_user_id", referencedColumnName="id")
     * })
     *
     * @Expose
     * @MaxDepth(1)
     *
     */
    private $creatorUser;

    /**
     * @var \ContinuousNet\LivnYouBundle\Entity\User
     * @access private
     *
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumns({
     *        @ORM\JoinColumn(name="modifier_user_id", referencedColumnName="id")
     * })
     *
     * @Expose
     * @MaxDepth(1)
     *
     */
    private $modifierUser;

    /**
     * Constructor
     *
     * @access public
     */
    public function __construct()
    {
    }

    /**
     * Get id
     *
     * @access public
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @access public
     * @param string $name
     * @return PhysicalActivity
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Get name
     *
     * @access public
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set athleticName
     *
     * @access public
     * @param string $athleticName
     * @return PhysicalActivity
     */
    public function setAthleticName($athleticName = null)
    {
        $this->athleticName = $athleticName;
        return $this;
    }

    /**
     * Get athleticName
     *
     * @access public
     * @return string
     */
    public function getAthleticName()
    {
        return $this->athleticName;
    }

    /**
     * Set value
     *
     * @access public
     * @param float $value
     * @return PhysicalActivity
     */
    public function setValue($value)
    {
        $this->value = $value;
        return $this;
    }

    /**
     * Get value
     *
     * @access public
     * @return float
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Set energyNeedsRate
     *
     * @access public
     * @param float $energyNeedsRate
     * @return PhysicalActivity
     */
    public function setEnergyNeedsRate($energyNeedsRate)
    {
        $this->energyNeedsRate = $energyNeedsRate;
        return $this;
    }

    /**
     * Get energyNeedsRate
     *
     * @access public
     * @return float
     */
    public function getEnergyNeedsRate()
    {
        return $this->energyNeedsRate;
    }

    /**
     * Set createdAt
     *
     * @access public
     * @param \DateTime $createdAt
     * @return PhysicalActivity
     */
    public function setCreatedAt(\DateTime $createdAt)
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    /**
     * Get createdAt
     *
     * @access public
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set modifiedAt
     *
     * @access public
     * @param \DateTime $modifiedAt
     * @return PhysicalActivity
     */
    public function setModifiedAt(\DateTime $modifiedAt = null)
    {
        $this->modifiedAt = $modifiedAt;
        return $this;
    }

    /**
     * Get modifiedAt
     *
     * @access public
     * @return \DateTime
     */
    public function getModifiedAt()
    {
        return $this->modifiedAt;
    }

    /**
     * Set creatorUser
     *
     * @access public
     * @param \ContinuousNet\LivnYouBundle\Entity\User $creatorUser
     * @return PhysicalActivity
     */
    public function setCreatorUser(User $creatorUser = null)
    {
        $this->creatorUser = $creatorUser;
        return $this;
    }

    /**
     * Get creatorUser
     *
     * @access public
     * @return \ContinuousNet\LivnYouBundle\Entity\User
     */
    public function getCreatorUser()
    {
        return $this->creatorUser;
    }

    /**
     * Set modifierUser
     *
     * @access public
     * @param \ContinuousNet\LivnYouBundle\Entity\User $modifierUser
     * @return PhysicalActivity
     */
    public function setModifierUser(User $modifierUser = null)
    {
        $this->modifierUser = $modifierUser;
        return $this;
    }

    /**
     * Get modifierUser
     *
     * @access public
     * @return \ContinuousNet\LivnYouBundle\Entity\User
     */
    public function getModifierUser()
    {
        return $this->modifierUser;
    }

    /**
     * @ORM\PreUpdate
     */
    public function preUpdate()
    {
        $this->setModifiedAt(new \DateTime('now'));
    }

    /**
     * @ORM\PrePersist
     */
    public function prePersist()
    {
        if (is_null($this->getCreatedAt())) {
            $this->setCreatedAt(new \DateTime('now'));
        }
    }
}
