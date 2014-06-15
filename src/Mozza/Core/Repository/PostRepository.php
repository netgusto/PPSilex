<?php

namespace Mozza\Core\Repository;

use Symfony\Component\Finder\Finder;

use Doctrine\ORM\EntityManager;

use Mozza\Core\Services\PostFileRepositoryService,
    Mozza\Core\Entity\AbstractPost,
    Mozza\Core\Entity\Post;

class PostRepository {

    protected $em;
    
    public function __construct(EntityManager $em) {
        $this->em = $em;
    }

    public function findOneBySlug($slug) {
        return $this->em->getRepository('Mozza\Core\Entity\Post')->findOneBySlug($slug);
    }

    public function findAll() {
        $qb = $this->em->createQueryBuilder()
            ->select('p')
            ->from('Mozza\Core\Entity\Post', 'p')
            ->add('where', 'p.status=:status')->setParameter('status', 'publish')
            ->add('orderBy', 'p.date DESC');
        return $qb->getQuery()->getResult();
    }

    public function deleteAll() {
        $q = $this->em->createQuery('DELETE FROM Mozza\Core\Entity\Post');
        $q->execute();
    }

    public function deleteOneBySlug($slug) {
        $q = $this->em->createQuery('DELETE FROM Mozza\Core\Entity\Post p where p.slug = :slug');
        $q->setParameter('slug', $slug);
        $q->execute();
    }
    
    public function findPrevious(Post $post) {
        
        $qb = $this->em->createQueryBuilder()
            ->select('p')
            ->from('Mozza\Core\Entity\Post', 'p')
            ->add('where', 'p.status = :status AND p.date < :date OR (p.date = :date AND (p.title < :title OR (p.title = :title AND p.slug < :slug)))')
                ->setParameter('status', 'publish')
                ->setParameter('date', $post->getDate())
                ->setParameter('title', $post->getTitle())
                ->setParameter('slug', $post->getSlug())
            ->add('orderBy', 'p.date DESC')
            ->setMaxResults(1);

        $result = $qb->getQuery()->getResult();
        if(!$result) {
            return null;
        }

        return $result[0];
    }

    public function findNext(Post $post) {
        $qb = $this->em->createQueryBuilder()
            ->select('p')
            ->from('Mozza\Core\Entity\Post', 'p')
            ->add('where', 'p.status = :status AND p.date > :date OR (p.date = :date AND (p.title > :title OR (p.title = :title AND p.slug > :slug)))')
                ->setParameter('status', 'publish')
                ->setParameter('title', $post->getTitle())
                ->setParameter('date', $post->getDate())
                ->setParameter('slug', $post->getSlug())
            ->add('orderBy', 'p.date ASC')
            ->setMaxResults(1);

        $result = $qb->getQuery()->getResult();
        if(!$result) {
            return null;
        }

        return $result[0];
    }
}