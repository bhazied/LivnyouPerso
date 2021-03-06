<?php

namespace ContinuousNet\LivnYouBundle\Controller;

use FOS\RestBundle\Controller\Annotations\QueryParam;
use FOS\RestBundle\Controller\Annotations\RouteResource;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Post;
use FOS\RestBundle\Controller\Annotations\Put;
use FOS\RestBundle\Controller\Annotations\View;
use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\View\View as FOSView;
use FOS\UserBundle\Model\UserManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\Controller\FOSRestController;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;

/**
 * Public Api V1 REST Controller
 *
 * Manage Api V1
 *
 * PHP version 5.4.4
 *
 * @category   Symfony 2 REST Controller
 * @package  ContinuousNet\livnyouBundle\Controller
 * @author    Sahbi KHALFALLAH <sahbi.khalfallah@continuousnet.com>
 * @copyright  2016 CONTINUOUS NET
 * @license  CONTINUOUS NET REGULAR LICENSE
 * @version  Release: 1.0
 * @link    http://livnyou.continuousnet.com/ContinuousNet/livnyouBundle/Controller
 * @see      ApiV1RESTController
 * @since      Class available since Release 1.0
 * @access    public
 */
class ApiV1RESTController extends FOSRestController
{
    const SESSION_EMAIL = 'fos_user_send_resetting_email/email';

    private $locales = array(
        'en' => 'en_US',
        'fr' => 'fr_FR',
        'es' => 'es_ES',
        'de' => 'de_DE',
        'it' => 'it_IT',
    );

    private function setTranslator($code)
    {
        $translator = new Translator($this->locales[$code]);
        $yamlLoader = new YamlFileLoader();
        $translator->addLoader('yaml', $yamlLoader);
        $translator->addResource('yaml', $this->container->getParameter('kernel.root_dir').'/Resources/translations/messages.'.$code.'.yaml', $this->locales[$code]);
        $this->container->register('translator', $translator);
        $this->get('session')->setLocale($this->locales[$code]);
    }

    public function translateEntity($entity, $level = 0)
    {
        if (is_null($entity)) {
            return array();
        }
        $ns = 'ContinuousNet\LivnYouBundle\Entity\\';
        $entityName = str_replace($ns, '', get_class($entity));
        $translationEntityName = 'Translation' . $entityName;
        $translationEntityFullName = $ns . $translationEntityName;
        if (class_exists($translationEntityFullName)) {
            $entityField = lcfirst($entityName);
            $request = $this->get('request');
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
                        if (substr(get_class($fieldValue), 0, strlen($ns)) == $ns) {
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

    private function getConfig($path)
    {
        $config = $this->container->getParameter('livn_you');
        $paths = explode('.', $path);
        foreach ($paths as $index) {
            $config = $config[$index];
        }
        return $config;
    }

    private function getLanguageByCode($code)
    {
        $em = $this->getDoctrine()->getManager();
        $qb = $em->createQueryBuilder();
        $qb->from('LivnYouBundle:Language', 'l_');
        $qb->select('l_');
        $qb->andWhere('l_.code = :code')->setParameter('code', $code);
        return $qb->getQuery()->getOneOrNullResult();
    }

    private function getGroupByName($name)
    {
        $em = $this->getDoctrine()->getManager();
        $qb = $em->createQueryBuilder();
        $qb->from('LivnYouBundle:Group', 'g_');
        $qb->select('g_');
        $qb->where('g_.name= :name')->setParameter('name', $name);
        return $qb->getQuery()->getOneOrNullResult();
    }

    /**
     * @Post("/checkEmail")
     * @View(serializerEnableMaxDepthChecks=true)
     */
    public function checkEmailAction(Request $request)
    {
        try {
            $email = $request->request->get('email');
            if (!is_null($email) && !empty($email)) {
                if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $data = array('status' => false, 'message' => null);
                    $em = $this->getDoctrine()->getManager();
                    $qb = $em->createQueryBuilder();
                    $qb->from('LivnYouBundle:User', 'u_');
                    $qb->andWhere('u_.email = :email')->setParameter('email', $email);
                    $qb->select('count(u_.id)');
                    $count = $qb->getQuery()->getSingleScalarResult();
                    if ($count == 0) {
                        $data['status'] = true;
                        $data['message'] = $this->get('translator')->trans('register.available_email_address');
                    } else {
                        $data['status'] = false;
                        $data['message'] = $this->get('translator')->trans('register.email_already_used');
                    }
                } else {
                    $data['status'] = false;
                    $data['message'] = $this->get('translator')->trans('register.invalid_email');
                }
            } else {
                $data['status'] = false;
                $data['message'] = $this->get('translator')->trans('register.empty_email');
            }
            return $data;
        } catch (\Exception $e) {
            return FOSView::create($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @Post("/checkPhone")
     * @View(serializerEnableMaxDepthChecks=true)
     */
    public function checkPhoneAction(Request $request)
    {
        try {
            $phone = $request->request->get('phone');
            if (!is_null($phone) && !empty($phone)) {
                if (is_numeric($phone)) {
                    $data = array('status' => false, 'message' => null);
                    $em = $this->getDoctrine()->getManager();
                    $qb = $em->createQueryBuilder();
                    $qb->from('LivnYouBundle:User', 'u_');
                    $qb->andWhere('u_.phone = :phone')->setParameter('phone', $phone);
                    $qb->select('count(u_.id)');
                    $count = $qb->getQuery()->getSingleScalarResult();
                    if ($count == 0) {
                        $data['status'] = true;
                        $data['message'] = $this->get('translator')->trans('register.available_phone_number');
                    } else {
                        $data['status'] = false;
                        $data['message'] = $this->get('translator')->trans('register.phone_already_used');
                    }
                } else {
                    $data['status'] = false;
                    $data['message'] = $this->get('translator')->trans('register.invalid_phone');
                }
            } else {
                $data['status'] = false;
                $data['message'] = $this->get('translator')->trans('register.empty_phone');
            }
            return $data;
        } catch (\Exception $e) {
            return FOSView::create($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @Post("/countries")
     * @View(serializerEnableMaxDepthChecks=true)
     */
    public function countriesAction(Request $request)
    {
        try {
            $locale = $request->request->get('locale');
            $select = array('c_.id', 'c_.name', 'c_.nameAr', 'c_.nameFr');
            $em = $this->getDoctrine()->getManager();
            $qb = $em->createQueryBuilder();
            $qb->from('LivnYouBundle:Country', 'c_');
            $qb->select($select);
            if ($locale == 'ar') {
                $qb->addOrderBy('c_.nameAr', 'ASC');
            } elseif ($locale == 'fr') {
                $qb->addOrderBy('c_.nameFr', 'ASC');
            } else {
                $qb->addOrderBy('c_.name', 'ASC');
            }
            $qb->andWhere('c_.published = :published')->setParameter('published', true);
            $results = $qb->getQuery()->getResult();
            return $results;
        } catch (\Exception $e) {
            return FOSView::create($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @Post("/register")
     * @View(serializerEnableMaxDepthChecks=true)
     */
    public function registerAction(Request $request)
    {
        try {
            $data = array('status' => false, 'message' => null);

            $jsonData = json_decode($request->getContent(), true);
            $language = $this->getLanguageByCode($jsonData['locale']);
            $jsonData['language'] = $language->getId();
            unset($jsonData['locale']);
            unset($jsonData['countryChoise']);
            unset($jsonData['gc']);
            $group = $this->getGroupByName('Subscriber');
            $jsonData['groups'] = array($group->getId());

            $jsonData['username'] = $jsonData['email'];
            $chars = '!#$%&\'*+-/=?^`{|}~.@';
            for ($i=0;$i<count($chars);$i++) {
                $jsonData['username'] = str_replace($chars[$i], '_', $jsonData['username']);
            }

            $jsonData['roles'] = array('ROLE_API', 'ROLE_SUBSCRIBER');

            //$jsonData['credentials_expired']  = false;

            //$jsonData['enabled'] = true;

            //$jsonData['picture'] = '/assets/images/'.strtolower($jsonData['gender']).'.png';
           // return $jsonData;

            if (isset($jsonData['provider'])) {
                unset($jsonData['provider']);
            }

            if (isset($jsonData['providerId'])) {
                unset($jsonData['providerId']);
            }
            $request->request->set('app_user_registration', $jsonData);

            $form = $this->container->get('fos_user.registration.form');
            $formHandler = $this->container->get('fos_user.registration.form.handler');
            $confirmationEnabled = $this->container->getParameter('fos_user.registration.confirmation.enabled');

            try {
                $process = $formHandler->process($confirmationEnabled);
            } catch (\Exception $e) {
                if (json_decode($e->getMessage())) {
                    return json_decode($e->getMessage());
                } else {
                    return $e->getMessage();
                }
            }

            if ($process) {
                $user = $form->getData();

                if ($confirmationEnabled) {
                    $this->container->get('session')->set('fos_user_send_confirmation_email/email', $user->getEmail());
                    $data['status'] = true;
                    $data['message'] = $this->get('translator')->trans('register.confirmation_email_sent_check_email');
                } else {
                    $data['status'] = true;
                    $data['message'] = $this->get('translator')->trans('register.registration_completed');
                    $data = array_merge($data, $this->get('ubid_electricity.event.user_session_data')->sessionData($user));

                    $em = $this->getDoctrine()->getManager();

                    $em->flush();
                }
                return $data;
            } else {
                $data['message'] = $this->get('translator')->trans('register.failure_inscription');
                return $data;
            }
        } catch (\Exception $e) {
            return FOSView::create($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @Post("/emailConfirm")
     * @View(serializerEnableMaxDepthChecks=true)
     */
    public function emailConfirmAction(Request $request)
    {
        $data = array('status' => false, 'message' => null);

        try {
            $token = $request->request->get('token');

            if (!is_null($token) && !empty($token)) {
                $user = $this->container->get('fos_user.user_manager')->findUserByConfirmationToken($token);

                if (null === $user) {
                    $data['status'] = false;
                    $data['message'] = sprintf($this->get('translator')->trans('register.user_with_confirmation_token_does_not_exist'), $token);
                } else {
                    $user->setConfirmationToken(null);
                    $user->setEnabled(true);
                    $user->setLastLogin(new \DateTime());

                    $this->container->get('fos_user.user_manager')->updateUser($user);

                    $data['status'] = true;
                    $data['message'] = $this->get('translator')->trans('register.email_confirmed');

                    $data = array_merge($data, $this->get('ubid_electricity.event.user_session_data')->sessionData($user));
                }
            } else {
                $data['status'] = false;
                $data['message'] = $this->get('translator')->trans('register.empty_token');
            }
            return $data;
        } catch (\Exception $e) {
            return FOSView::create($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get the truncated email displayed when requesting the resetting.
     *
     * The default implementation only keeps the part following @ in the address.
     *
     * @param \FOS\UserBundle\Model\UserInterface $user
     *
     * @return string
     */
    protected function getObfuscatedEmail($user)
    {
        $email = $user->getEmail();
        if (false !== $pos = strpos($email, '@')) {
            $email = '...' . substr($email, $pos);
        }

        return $email;
    }

    /**
     * @Post("/requestResetPassword")
     * @View(serializerEnableMaxDepthChecks=true)
     */
    public function requestResetPasswordAction(Request $request)
    {
        $data = array('status' => false, 'message' => null);

        try {
            $email = $request->request->get('email');
            if (!is_null($email) && !empty($email)) {
                if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $data = array('status' => false, 'message' => null);
                    /** @var $user UserInterface */
                    $user = $this->container->get('fos_user.user_manager')->findUserByUsernameOrEmail($email);
                    if (!is_null($user)) {
                        if (!$user->isPasswordRequestNonExpired($this->container->getParameter('fos_user.resetting.token_ttl'))) {
                            if (null === $user->getConfirmationToken()) {
                                /** @var $tokenGenerator \FOS\UserBundle\Util\TokenGeneratorInterface */
                                $tokenGenerator = $this->container->get('fos_user.util.token_generator');
                                $user->setConfirmationToken($tokenGenerator->generateToken());
                            }

                            $this->container->get('session')->set(static::SESSION_EMAIL, $this->getObfuscatedEmail($user));
                            $this->container->get('fos_user.mailer')->sendResettingEmailMessage($user);
                            $user->setPasswordRequestedAt(new \DateTime());
                            $this->container->get('fos_user.user_manager')->updateUser($user);

                            $data['status'] = true;
                            $data['message'] = $this->get('translator')->trans('reset.reset_password_requested');
                        } else {
                            $data['status'] = false;
                            $data['message'] = $this->get('translator')->trans('reset.password_already_requested');
                        }
                    } else {
                        $data['status'] = false;
                        $data['message'] = $this->get('translator')->trans('reset.no_user_with_this_email');
                    }
                } else {
                    $data['status'] = false;
                    $data['message'] = $this->get('translator')->trans('reset.invalid_email');
                }
            } else {
                $data['status'] = false;
                $data['message'] = $this->get('translator')->trans('reset.empty_email');
            }
            return $data;
        } catch (\Exception $e) {
            return FOSView::create($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @Post("/reset")
     * @View(serializerEnableMaxDepthChecks=true)
     */
    public function resetAction(Request $request)
    {
        $data = array('status' => false, 'message' => null);

        try {
            $token = $request->request->get('token');

            if (!is_null($token) && !empty($token)) {
                $user = $this->container->get('fos_user.user_manager')->findUserByConfirmationToken($token);

                if (null === $user) {
                    $data['status'] = false;
                    $data['message'] = sprintf($this->get('translator')->trans('reset.user_with_confirmation_token_does_not_exist'), $token);
                } elseif (!$user->isPasswordRequestNonExpired($this->container->getParameter('fos_user.resetting.token_ttl'))) {
                    $data['status'] = false;
                    $data['message'] = sprintf($this->get('translator')->trans('reset.confirmation_token_is_expired'), $token);
                } else {
                    $jsonData = json_decode($request->getContent(), true);
                    unset($jsonData['locale']);
                    unset($jsonData['token']);

                    $request->request->set('fos_user_resetting_form', $jsonData);

                    $form = $this->container->get('fos_user.resetting.form');
                    $formHandler = $this->container->get('fos_user.resetting.form.handler');
                    $process = $formHandler->process($user);

                    if ($process) {
                        $data['token'] = $this->get("lexik_jwt_authentication.jwt_manager")->create($user);
                        $data['user'] = array(
                            'email' => $user->getEmail(),
                            'username' => $user->getUsername(),
                            'name' => $user->getFirstName() . ' ' . $user->getLastName(),
                            'firstName' => $user->getFirstName(),
                            'lastName' => $user->getLastName(),
                            'job' => $user->getJob(),
                            'picture' => $user->getPicture(),
                            'roles' => $user->getRoles()
                        );
                        $data['status'] = true;
                        $data['message'] = $this->get('translator')->trans('reset.password_changed');
                    } else {
                        $data['status'] = false;
                        $data['message'] = $this->get('translator')->trans('reset.password_not_changed');
                    }
                }
            } else {
                $data['status'] = false;
                $data['message'] = $this->get('translator')->trans('Empty token.');
            }
            return $data;
        } catch (\Exception $e) {
            return FOSView::create($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @Post("/checkConfirmationToken")
     * @View(serializerEnableMaxDepthChecks=true)
     */
    public function checkConfirmationTokenAction(Request $request)
    {
        $data = array('status' => false, 'message' => null);

        try {
            $token = $request->request->get('token');

            if (!is_null($token) && !empty($token)) {
                $user = $this->container->get('fos_user.user_manager')->findUserByConfirmationToken($token);

                if (null === $user) {
                    $data['status'] = false;
                    $data['message'] = sprintf($this->get('translator')->trans('register.user_with_confirmation_token_does_not_exist'), $token);
                } elseif (!$user->isPasswordRequestNonExpired($this->container->getParameter('fos_user.resetting.token_ttl'))) {
                    $data['status'] = false;
                    $data['message'] = sprintf($this->get('translator')->trans('register.confirmation_token_is_expired'), $token);
                } else {
                    $data['status'] = true;
                    $data['message'] = $this->get('translator')->trans('register.correct_token');
                }
            } else {
                $data['status'] = false;
                $data['message'] = $this->get('translator')->trans('register.empty_token');
            }
            return $data;
        } catch (\Exception $e) {
            return FOSView::create($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @Put("/updateProfile")
     * @View(serializerEnableMaxDepthChecks=true)
     */
    public function updateProfileAction(Request $request)
    {
        try {
            $data = array();
            $em = $this->getDoctrine()->getManager();
            $user = $em->getRepository('LivnYouBundle:User')->find($this->getUser()->getId());
            $fields = array(
                'firstName', 'lastName', 'gender', 'country', 'language', 'phone', 'picture'
            );
            foreach ($fields as $field) {
                $value =  !is_null($request->request->get($field))  ? $request->request->get($field) : null;
                $method = 'set'.ucfirst($field);
                if (!is_null($value) && !is_array($value)) {
                    if ($field == 'country') {
                        $value = $em->getRepository('LivnYouBundle:Country')->findOneById($value);
                    } elseif ($field == 'language') {
                        $value = $em->getRepository('LivnYouBundle:Language')->findOneByCode($value);
                    } elseif ($field == 'birthDate') {
                        $value = new \DateTime($value);
                    }
                } else {
                    $value = null;
                }
                if (method_exists($user, $method)) {
                    $user->$method($value);
                }
            }
            try {
                $em->flush();
                $data['status'] = true;
                $data['message'] = $this->get('translator')->trans('profile.updated');
            } catch (\Exception $e) {
                $data['status'] = false;
                $data['message'] = $this->get('translator')->trans('profile.notupdated');
            }
            return $data;
        } catch (\Exception $e) {
            return FOSView::create($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @POST("/contact")
     * @View(serializerEnableMaxDepthChecks=true)
     * @param Request $request
     */
    public function contactAction(Request $request)
    {
        $response = array();
        try {
            $subject = $request->request->get('subject');
            $email = $request->request->get('email');
            $firstName = $request->request->get('firstName');
            $lastName = $request->request->get('lastName');
            $message = $request->request->get('message');
            $message = \Swift_Message::newInstance()
                ->setSubject($subject)
                ->setTo($this->container->getParameter('email_contact'))
                ->setFrom($email)
                ->setBody(
                    $this->renderView('LivnYouBundle:Emails:contact.html.twig', array(
                        'subject' => $subject,
                        'lastName' => $lastName,
                        'firstName' => $firstName,
                        'email' => $email,
                        'message' => $message
                    )),
                    'text/html'
                );
            $sent = $this->get('mailer')->send($message);
            if ($sent) {
                $response =  array(
                    'status' => '0',
                    'message' => 'Message envoyé avec succée'
                );
            } else {
                $response =  array(
                    'status' => '1',
                    'message' => 'Message non envoyé'
                );
            }
            return $response;
        } catch (\Exception $e) {
            return FOSView::create($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @Get("/getProfile")
     * @View(serializerEnableMaxDepthChecks=true)
     */
    public function getProfileAction()
    {
        try {
            $user = $this->getUser();

            $data = array(
                'id' => $user->getId(),
                'name' => $user->getFirstName() . ' ' . $user->getLastName(),
                'email' => $user->getEmail(),
                'username' => $user->getUsername(),
                'firstName' => $user->getFirstName(),
                'lastName' => $user->getLastName(),
                'phone' => $user->getPhone(),
                'job' => $user->getJob(),
                'zipCode' => $user->getZipCode(),
                //'city' => $user->getCity(),
                'type' => $user->getType(),
                'gender' => $user->getGender(),
                'address' => $user->getAddress(),
                'country' => $user->getCountry(),
                'picture' => $user->getPicture(),
                'lastLogin' => $user->getLastLogin(),
                'inscriptionDate' => $user->getCreatedAt()
            );

            return $data;
        } catch (\Exception $e) {
            return FOSView::create($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @ApiDoc(
     *  resource=true,
     *  description="This service check if entred passwword is equal to user current password",
     *  requirements={
     *      {
     *     "name"="newPassword",
     *      "dataType"="string",
     *      "description"="the new password to be set it"
     *      }
     *   },
     *      output="ContinuousNet\LivnYouBundle\Entity\Country"
     * )
     * @POST("/checkPassword")
     * @View(serializerEnableMaxDepthChecks=true)
     */
    public function checkPasswordAction(Request $request)
    {
        $data = [];
        $user = $this->getUser();
        $password = $request->request->get('currentPassword');
        $encoder_service = $this->get('security.encoder_factory');
        $encoder = $encoder_service->getEncoder($user);
        $encoded_pass = $encoder->encodePassword($password, $user->getSalt());
        //return $encoded_pass." ".$password . " ".$user->getSalt();
        if ($encoded_pass == $user->getPassword()) {
            $data = [
                'status' => true,
                'message' => 'OK'
            ];
        } else {
            $data = [
                'status' => false,
                'message' => 'NOT OK'
            ];
        }
        return $data;
    }

    /**
     * @Post("/changePassword")
     * @View(serializerEnableMaxDepthChecks=true)
     */
    public function changePasswordAction(Request $request)
    {
        $data = array('status' => false, 'message' => null);
        try {
            $em = $this->getDoctrine()->getManager();
            $user = $this->getUser();
            $jsonData = json_decode($request->getContent(), true);
            $password =  $jsonData['newPassword'];
            //$user = $user->setPlainPassword($password);
            $encoder_service = $this->get('security.encoder_factory');
            $encoder = $encoder_service->getEncoder($user);
            $encoded_pass = $encoder->encodePassword($password, $user->getSalt());
            $user = $user->setPassword($encoded_pass);
            $user->eraseCredentials();
            $em->flush();
            $data['status'] = true;
            $data['message'] = $this->get('translator')->trans('Password changed');
            return $data;
        } catch (\Exception $e) {
            $data['status'] = false;
            $data['message'] = $this->get('translator')->trans('Password not changed.');
        }
    }
}
