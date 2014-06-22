<?php

namespace Mozza\Core\Security;

use Symfony\Component\Security\Core\User\UserProviderInterface,
    Symfony\Component\Security\Core\User\UserInterface,
    Symfony\Component\Security\Core\User\User,
    Symfony\Component\Security\Core\Exception\UnsupportedUserException,
    Symfony\Component\Security\Core\Exception\UsernameNotFoundException;

use Doctrine\ORM\EntityManager;

class UserProvider implements UserProviderInterface {

    protected $em;

    public function __construct(EntityManager $em) {
        $this->em = $em;
    }

    public function loadUserByUsername($email) {

        $user = $this->em->getRepository('Mozza\Core\Entity\User')->findOneByEmail($email);
        if($user) {
            return $user;
        }

        throw new UsernameNotFoundException(sprintf('Username "%s" does not exist.', $email));
    }

    public function refreshUser(UserInterface $user) {

        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', get_class($user)));
        }

        return $this->loadUserByUsername($user->getUsername());
    }

    public function supportsClass($class) {
        return $class === 'Mozza\Core\Entity\User';
    }
}