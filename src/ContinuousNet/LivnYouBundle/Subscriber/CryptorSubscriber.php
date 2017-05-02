<?php

namespace ContinuousNet\LivnYouBundle\Subscriber;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use ContinuousNet\LivnYouBundle\Entity\Measurement;
use ContinuousNet\LivnYouBundle\Tools\Cryptor;

class CryptorSubscriber implements EventSubscriber
{
    private $key;

    const BASE64_URI = 'data:text/plain;base64,';

    public function __construct($key)
    {
        $this->key = $key;
    }

    public function getSubscribedEvents()
    {
        return array(
            'postLoad',
            'prePersist',
            'preUpdate'
        );
    }

    public function prePersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        if ($entity instanceof Measurement) {
            if (is_null($entity->getCreatedAt())) {
                $entity->setCreatedAt(new \DateTime('now'));
            }
            $entity->setFirstName($this->encryt($entity->getFirstName()));
            $entity->setLastName($this->encryt($entity->getLastName()));
            $entity->setGroupName($this->encryt($entity->getGroupName()));
            $entity->setAddress($this->encryt($entity->getAddress()));
            $entity->setCity($this->encryt($entity->getCity()));
            $entity->setZipCode($this->encryt($entity->getZipCode()));
            $entity->setState($this->encryt($entity->getState()));
            $entity->setMobileNumber($this->encryt($entity->getMobileNumber()));
            $entity->setEmail($this->encryt($entity->getEmail()));
            $entity->setPhone($this->encryt($entity->getPhone()));
        }
    }

    public function preUpdate(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        if ($entity instanceof Measurement) {
            $entity->setModifiedAt(new \DateTime('now'));
            $entity->setFirstName($this->encryt($entity->getFirstName()));
            $entity->setLastName($this->encryt($entity->getLastName()));
            $entity->setGroupName($this->encryt($entity->getGroupName()));
            $entity->setAddress($this->encryt($entity->getAddress()));
            $entity->setCity($this->encryt($entity->getCity()));
            $entity->setZipCode($this->encryt($entity->getZipCode()));
            $entity->setState($this->encryt($entity->getState()));
            $entity->setMobileNumber($this->encryt($entity->getMobileNumber()));
            $entity->setEmail($this->encryt($entity->getEmail()));
            $entity->setPhone($this->encryt($entity->getPhone()));
        }
    }

    public function postLoad(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        if ($entity instanceof Measurement) {
            $entity->setFirstName($this->decryt($entity->getFirstName()));
            $entity->setLastName($this->decryt($entity->getLastName()));
            $entity->setGroupName($this->decryt($entity->getGroupName()));
            $entity->setAddress($this->decryt($entity->getAddress()));
            $entity->setCity($this->decryt($entity->getCity()));
            $entity->setZipCode($this->decryt($entity->getZipCode()));
            $entity->setState($this->decryt($entity->getState()));
            $entity->setMobileNumber($this->decryt($entity->getMobileNumber()));
            $entity->setEmail($this->decryt($entity->getEmail()));
            $entity->setPhone($this->decryt($entity->getPhone()));
        }
    }

    public function setKey($key)
    {
        $this->key = $key;
        return $this;
    }

    public function getKey()
    {
        return $this->key;
    }

    public function encryt($data)
    {
        if (substr($data, 0, strlen(self::BASE64_URI)) !== self::BASE64_URI) {
            //$encrypted = Cryptor::Encrypt($data, $this->getKey());
            //return self::BASE64_URI . $encrypted;
            return $data;
        } else {
            return $data;
        }
    }

    public function decryt($data)
    {
        if (substr($data, 0, strlen(self::BASE64_URI)) === self::BASE64_URI) {
            $data = substr($data, strlen(self::BASE64_URI));
            $decrypted = Cryptor::Decrypt($data, $this->getKey());
            return $decrypted;
        } else {
            return $data;
        }
    }
}
