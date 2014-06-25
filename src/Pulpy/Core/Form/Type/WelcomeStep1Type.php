<?php

namespace Pulpy\Core\Form\Type;

use Symfony\Component\Form\AbstractType,
    Symfony\Component\Form\FormBuilderInterface;

use Symfony\Component\Validator\Constraints\NotBlank,
    Symfony\Component\Validator\Constraints\Email;

class WelcomeStep1Type extends AbstractType {

    protected $parameters;

    public function __construct($parameters = array()) {
        $this->parameters = $parameters;
    }

    public function buildForm(FormBuilderInterface $builder, array $options) {
    }

    public function getName() {
        return 'welcomestep1';
    }
}