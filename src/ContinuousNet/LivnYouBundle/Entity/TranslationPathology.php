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
 * Translation Pathology Entity
 *
 * Storing TranslationPathologies data to the database using Doctrine
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
 * @see        TranslationPathology
 * @since      Class available since Release 1.0
 * @access     public
 *
 * @ORM\Table(name="`translation_pathology`", indexes={@ORM\Index(name="pathology_id", columns={"pathology_id"}), @ORM\Index(name="creator_user_id", columns={"creator_user_id"}), @ORM\Index(name="modifier_user_id", columns={"modifier_user_id"})})
 * @ORM\Entity(repositoryClass="ContinuousNet\LivnYouBundle\Repository\TranslationPathologyRepository")
 * @ORM\HasLifecycleCallbacks()
 *
 * @ExclusionPolicy("none")
 *
 */
class TranslationPathology
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
     * @ORM\Column(name="`locale`", type="string", length=5, nullable=false, unique=false)
     *
     * @Expose
     *
     */
    private $locale;

    /**
     * @var string
     * @access private
     *
     * @ORM\Column(name="`name`", type="string", length=20, nullable=false, unique=false)
     *
     * @Expose
     *
     */
    private $name;

    /**
     * @var boolean
     * @access private
     *
     * @ORM\Column(name="`validated`", type="boolean", nullable=false, unique=false)
     *
     * @Expose
     *
     */
    private $validated;

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
     * @var \ContinuousNet\LivnYouBundle\Entity\Pathology
     * @access private
     *
     * @ORM\ManyToOne(targetEntity="Pathology")
     * @ORM\JoinColumns({
     *        @ORM\JoinColumn(name="pathology_id", referencedColumnName="id")
     * })
     *
     * @Expose
     * @MaxDepth(1)
     *
     */
    private $pathology;

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
     * Set locale
     *
     * @access public
     * @param string $locale
     * @return TranslationPathology
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;
        return $this;
    }

    /**
     * Get locale
     *
     * @access public
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * Set name
     *
     * @access public
     * @param string $name
     * @return TranslationPathology
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
     * Set validated
     *
     * @access public
     * @param boolean $validated
     * @return TranslationPathology
     */
    public function setValidated($validated)
    {
        $this->validated = $validated;
        return $this;
    }

    /**
     * Get validated
     *
     * @access public
     * @return boolean
     */
    public function getValidated()
    {
        return $this->validated;
    }

    /**
     * Set createdAt
     *
     * @access public
     * @param \DateTime $createdAt
     * @return TranslationPathology
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
     * @return TranslationPathology
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
     * Set pathology
     *
     * @access public
     * @param \ContinuousNet\LivnYouBundle\Entity\Pathology $pathology
     * @return TranslationPathology
     */
    public function setPathology(Pathology $pathology = null)
    {
        $this->pathology = $pathology;
        return $this;
    }

    /**
     * Get pathology
     *
     * @access public
     * @return \ContinuousNet\LivnYouBundle\Entity\Pathology
     */
    public function getPathology()
    {
        return $this->pathology;
    }

    /**
     * Set creatorUser
     *
     * @access public
     * @param \ContinuousNet\LivnYouBundle\Entity\User $creatorUser
     * @return TranslationPathology
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
     * @return TranslationPathology
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
