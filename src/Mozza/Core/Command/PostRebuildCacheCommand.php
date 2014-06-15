<?php

namespace Mozza\Core\Command;

use Symfony\Component\Console\Helper\DialogHelper,
    Symfony\Component\Console\Input\InputInterface,
    Symfony\Component\Console\Output\OutputInterface,
    Symfony\Component\Console\Input\InputArgument;

use Mozza\Core\Entity\Post;

class PostRebuildCacheCommand extends \Knp\Command\Command {
    /**
     * {inheritDoc}
     */
    protected function configure()
    {
        $this
            ->setName('mozza:post:rebuildcache')
            ->setDescription('Rebuilds completely the post cache');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output) {

        $dialog = $this->getHelperSet()->get('dialog');
        $app = $this->getSilexApplication();

        $app['post.repository']->deleteAll();

        $postfiles = $app['postfile.repository']->findAll();
        
        foreach($postfiles as $postfile) {
            $app['orm.em']->persist(
                $app['postfile.topostconverter']->convertToPost($postfile)
            );
        }

        $app['orm.em']->flush();

        $output->writeln('<info>Post cache has been rebuilt.</info>');
    }
}