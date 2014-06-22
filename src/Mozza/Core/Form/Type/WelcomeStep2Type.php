<?php

namespace Mozza\Core\Form\Type;

use Symfony\Component\Form\AbstractType,
    Symfony\Component\Form\FormBuilderInterface;

use Symfony\Component\Validator\Constraints\NotBlank,
    Symfony\Component\Validator\Constraints\Email;

class WelcomeStep2Type extends AbstractType {

    protected $parameters;

    public function __construct($parameters = array()) {
        $this->parameters = $parameters;
    }

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
            ->add('email', 'text', array(
                'label' => 'Email',
                'attr' => array('placeholder' => 'Email'),
                'constraints' => array(
                    new NotBlank(array(
                        'message' => 'Please, give your email address.'
                    )),
                    new Email(array(
                        'message' => 'This email is not valid.'
                    ))
                )
            ))
            ->add('password', 'repeated', array(
                'type' => 'password',
                'invalid_message' => 'Passwords do not match.',
                'options' => array('required' => TRUE),
                'constraints' => array(
                    new NotBlank(array(
                        'message' => 'Password is required.'
                    ))
                ),
                'first_options'  => array(
                    'label' => 'Password',
                    'attr' => array('placeholder' => 'Password'),
                ),
                'second_options'  => array(
                    'label' => 'Password, again',
                    'attr' => array('placeholder' => 'Password, again'),
                ),
            ));
    }

    public function getName() {
        return 'welcomestep2';
    }
}