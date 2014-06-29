<?php

namespace Pulpy\Core\Command;

use Symfony\Component\Console\Helper\DialogHelper,
    Symfony\Component\Console\Input\InputInterface,
    Symfony\Component\Console\Output\OutputInterface,
    Symfony\Component\Console\Input\InputArgument;

class AsseticDumpCommand extends \Knp\Command\Command {
    /**
     * {inheritDoc}
     */
    protected function configure()
    {
        $this
            ->setName('assetic:dump')
            ->setDescription('Dumps all assets to the filesystem.');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output) {

        $app = $this->getSilexApplication();
        $app['assetic.dumper']->addTwigAssets();
        $app['assetic.dumper']->dumpAssets();
        
        $output->writeln('<info>Dump finished</info>');
    }
}