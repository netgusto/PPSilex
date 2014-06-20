<?php

namespace Mozza\Core\Services\CacheHandler;

use Symfony\Component\Console\Output\OutputInterface;

use Doctrine\ORM\EntityManager;

use Mozza\Core\Entity\AbstractPost,
    Mozza\Core\Repository\PostRepository,
    Mozza\Core\Services\Context\CultureService,
    Mozza\Core\Services\Context\SystemStatusService,
    Mozza\Core\Services\PostFile\PostFileRepositoryService,
    Mozza\Core\Services\PostFile\PostFileToPostConverterService,
    Mozza\Core\Services\PersistentStorage\PersistentStorageServiceInterface;

abstract class AbstractPostCacheHandlerService {

    protected $systemstatus;
    protected $postfilerepository;
    protected $postrepository;
    protected $postfiletopostconverter;
    protected $em;
    protected $postspath;
    protected $culture;

    public function __construct(PersistentStorageServiceInterface $fs, SystemStatusService $systemstatus, PostFileRepositoryService $postfilerepository, PostRepository $postrepository, PostFileToPostConverterService $postfiletopostconverter, EntityManager $em, /* string */ $postspath, CultureService $culture) {
        $this->fs = $fs;
        $this->systemstatus = $systemstatus;
        $this->postfilerepository = $postfilerepository;
        $this->postrepository = $postrepository;
        $this->postfiletopostconverter = $postfiletopostconverter;
        $this->em = $em;
        $this->postspath = $postspath;
        $this->culture = $culture;
    }

    abstract public function cacheNeedsUpdate();

    public function updateCacheIfNeeded() {
        if($this->cacheNeedsUpdate()) {
            $this->updateCache();
            $this->systemstatus->setPostCacheLastUpdate($lastmodified);
        }
    }

    public function updateCache(OutputInterface $output = null) {

        $postfiles = $this->postfilerepository->findAll();
        $posts = $this->postrepository->findAll();

        $postsBySlug = array();
        $postsfilesBySlug = array();

        $postfilesslugs = array();
        $postslugs = array();

        foreach($postfiles as $post) {
            $postfilesslugs[] = $post->getSlug();
            $postsfilesBySlug[$post->getSlug()] = $post;
        }

        foreach($posts as $post) {
            $postslugs[] = $post->getSlug();
            $postsBySlug[$post->getSlug()] = $post;
        }

        $newslugs = array_diff($postfilesslugs, $postslugs);
        $delslugs = array_diff($postslugs, $postfilesslugs);
        $updateslugs = array_diff($postfilesslugs, array_merge($newslugs, $delslugs));

        # create new posts
        foreach($newslugs as $newslug) {
            $this->em->persist(
                $this->postfiletopostconverter->convertToPost(
                    $postsfilesBySlug[$newslug]
                )
            );

            if(!is_null($output)) {
                $output->writeln('<comment>Cached post ' . $newslug . ' has been created.</comment>');
            }
        }

        # delete old posts
        foreach($delslugs as $delslug) {
            $this->postrepository->deleteOneBySlug($delslug);

            if(!is_null($output)) {
                $output->writeln('<comment>Cached post ' . $delslug . ' has been deleted.</comment>');
            }
        }

        # update existing posts
        foreach($updateslugs as $updateslug) {
            $postfile = $postsfilesBySlug[$updateslug];
            $post = $postsBySlug[$updateslug];

            if($postfile->getLastmodified() <= $post->getLastmodified()) {
                continue;
            }

            $post = $this->postfiletopostconverter->merge($post, $postfile);
            $this->em->merge($post);

            if(!is_null($output)) {
                $output->writeln('<comment>Cached post ' . $updateslug . ' has been updated.</comment>');
            }
        }

        $this->em->flush();

        if(!is_null($output)) {
            $output->writeln('<info>Post cache has been updated.</info>');
        }
    }

    public function rebuildCache(OutputInterface $output = null) {

        $this->postrepository->deleteAll();
        $postfiles = $this->postfilerepository->findAll();
        
        foreach($postfiles as $postfile) {
            $this->em->persist(
                $this->postfiletopostconverter->convertToPost($postfile)
            );
        }

        $this->em->flush();

        if(!is_null($output)) {
            $output->writeln('<info>Post cache has been rebuilt.</info>');
        }
    }
}