<?php

namespace Pulpy\Core\Command;

use Symfony\Component\Console\Helper\DialogHelper,
    Symfony\Component\Console\Input\InputInterface,
    Symfony\Component\Console\Output\OutputInterface,
    Symfony\Component\Console\Input\InputArgument;

use Pulpy\Core\Entity\Post;

class PostUpdateCacheCommand extends \Knp\Command\Command {
    /**
     * {inheritDoc}
     */
    protected function configure()
    {
        $this
            ->setName('pulpy:cache:update')
            ->setDescription('Updates the post cache');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output) {

        $dialog = $this->getHelperSet()->get('dialog');
        $app = $this->getSilexApplication();

        $app['post.cachehandler']->updateCache($output);
    }
}