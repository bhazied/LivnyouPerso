<?php

namespace ContinuousNet\LivnYouBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\BirthdayType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

/**
 * Template Type
 *
 * Render Template Type
 *
 * PHP version 5.4.4
 *
 * @category   Symfony 2 Type
 * @package    ContinuousNet\LivnYouBundle\Form
 * @author     Sahbi KHALFALLAH <sahbi.khalfallah@continuousnet.com>
 * @copyright  2017 CONTINUOUS NET
 * @license   AMINOGRAM REGULAR LICENSE
 * @version    Release: 1.0
 * @link       http://livnyou.continuousnet.com/ContinuousNet\LivnYouBundle/Form
 * @see        TemplateType
 * @since      Class available since Release 1.0
 * @access     public
 */
class TemplateType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class)
            ->add('activated', CheckboxType::class)
            ->add('priority', IntegerType::class)
            ->add('isDefault', CheckboxType::class)
            ->add('shareLevel', ChoiceType::class, array('choices' => array('None' => 'None', 'Everyone' => 'Everyone', ), 'expanded' => false, 'multiple' => false))
        ;
    }
    
    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'ContinuousNet\LivnYouBundle\Entity\Template'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'LivnYouBundle_Template';
    }
}
