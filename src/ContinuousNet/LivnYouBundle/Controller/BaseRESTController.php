<?php

namespace ContinuousNet\LivnYouBundle\Controller;

use FOS\RestBundle\Controller\Annotations\QueryParam;
use FOS\RestBundle\Controller\Annotations\RouteResource;
use FOS\RestBundle\Controller\Annotations\View;
use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\View\View as FOSView;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormErrorIterator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;

use FOS\RestBundle\Controller\FOSRestController;

class BaseRESTController extends FOSRestController
{

    public $locales = array(
        'en' => 'en_US',
        'fr' => 'fr_FR'
    );

    private $privateEntities = array('User', 'Measurement');

    public function getConfig($path)
    {
        $config = $this->container->getParameter('livn_you');
        $paths = explode('.', $path);
        foreach ($paths as $index) {
            $config = $config[$index];
        }
        return $config;
    }

    public function translateEntity($entity, $level = 0)
    {
        $ns = 'ContinuousNet\LivnYouBundle\Entity\\';
        $prefix = 'Proxies\\__CG__\\';
        $entityName = str_replace($ns, '', get_class($entity));
        $entityName = str_replace($prefix, '', $entityName);
        $translationEntityName = 'Translation' . $entityName;
        $translationEntityFullName = $ns . $translationEntityName;
        if (class_exists($translationEntityFullName)) {
            $entityField = lcfirst($entityName);
            $request = $this->get('request_stack')->getCurrentRequest();
            $em = $this->getDoctrine()->getManager();
            $qb = $em->createQueryBuilder();
            $qb->select('t');
            $qb->from('LivnYouBundle:' . $translationEntityName, 't');
            $qb->andWhere('t.locale = :locale')->setParameter('locale', $request->getLocale());
            $qb->andWhere('t.validated = :validated')->setParameter('validated', true);
            $qb->andWhere('t.' . $entityField . ' = :' . $entityField)->setParameter($entityField, $entity->getId());
            $qb->setMaxResults(1);
            $translation = $qb->getQuery()->getOneOrNullResult();
            if (!is_null($translation)) {
                $notTranslatableFields = array('Id', 'Locale', 'Validated', 'CreatorUser', 'CreatedAt', 'ModifierUser', 'ModifiedAt', $entityName);
                $translatableFields = array();
                $methods = get_class_methods($translation);
                foreach ($methods as $method) {
                    if (substr($method, 0, 3) == 'get') {
                        $field = str_replace('get', '', $method);
                        if (!in_array($field, $notTranslatableFields)) {
                            array_push($translatableFields, $field);
                        }
                    }
                }
                foreach ($translatableFields as $field) {
                    $setMethod = 'set' . $field;
                    $getMethod = 'get' . $field;
                    $entity->$setMethod($translation->$getMethod());
                }
            }
        }
        if ($level < 1) {
            $methods = get_class_methods($entity);
            foreach ($methods as $method) {
                if (substr($method, 0, 3) == 'get') {
                    $field = str_replace('get', '', $method);
                    $setMethod = 'set' . $field;
                    $fieldValue = $entity->$method();
                    if (is_object($fieldValue)) {
                        if (strpos(get_class($fieldValue), $ns) !== false) {
                            $entity->$setMethod($this->translateEntity($fieldValue, $level + 1));
                        }
                    }
                }
            }
        }
        return $entity;

    }

    public function translateEntities($entities)
    {
        foreach ($entities as $i => $entity) {
            $entities[$i] = $this->translateEntity($entity);
        }
        return $entities;
    }

    public function getEntityName($entity)
    {
        $entityName = join('', array_slice(explode('\\', get_class($entity)), -1));
        return $entityName;
    }

    public function getEntityDir($entity, $pluralize = true)
    {
        $dir = strtolower($this->getEntityName($entity));
        if ($pluralize) {
            $lastLetter = substr($dir, -1);
            switch ($lastLetter) {
                case 'y':
                    return substr($dir, 0, -1) . 'ies';
                case 's':
                    return $dir . 'es';
                default:
                    return $dir . 's';
            }
        }
        return $dir;
    }

    public function getSubDirectory($entity, $absolute = true)
    {
        $directory = '';
        if ($absolute) {
            $directory .= $this->get('kernel')->getRootDir() . '/../web/uploads/';

        }
        $entityName = $this->getEntityName($entity);
        if (in_array($entityName, $this->privateEntities)) {
            $directory .= 'data' . sprintf('/user_%06d/', $this->getUser()->getId()) . '/' . $this->getEntityDir($entity) . '/';
        } else {
            $directory .= $this->getEntityDir($entity) . '/';
        }
        return $directory;
    }

    public function createSubDirectory($entity)
    {

        $directory = $this->getSubDirectory($entity);
        if (!is_dir($directory)) {
            mkdir($directory, 0777, true);
        }
    }

    public function createSubDirectories($entities)
    {
        foreach ($entities as $entity) {
            $this->createSubDirectory($entity);
        }
    }

    /**
     * Get rid on any fields that don't appear in the form
     *
     * @param Request $request
     * @param Form $form
     */
    protected function removeExtraFields(Request $request, Form $form)
    {
        $data     = $request->request->all();
        $children = $form->all();
        $data     = array_intersect_key($data, $children);
        $request->request->replace($data);
    }


    protected  function getFormExactError( FormErrorIterator $errors){
        $err_messages = [];
        foreach ($errors as $error) {
            $message = $this->get('translator')->trans($error->getMessage());
            array_push($err_messages, $message);
        }
        return $err_messages;
    }
}