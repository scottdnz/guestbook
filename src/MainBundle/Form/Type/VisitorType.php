<?php

namespace MainBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

use MainBundle\Entity\Visitor;

class VisitorType extends AbstractType {

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
        ->add("name", TextType::class, 
            array("attr" => 
                array("class" => "form-control")
            )
        )
        ->add("address", TextareaType::class, 
           array("attr" => 
                array("class" => "form-control")
            )
        )
        ->add("email", EmailType::class,
           array("attr" => 
                array("class" => "form-control")
            )
        )
        ->add("message", TextareaType::class, 
            array("attr" => 
                array("class" => "form-control")
            )
        );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Visitor::class,
        ));
    }
    
}
