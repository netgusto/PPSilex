<?php

namespace Mozza\Core\Composer;

use Composer\Script\CommandEvent,
    Symfony\Component\Filesystem\Filesystem,
    Symfony\Component\Filesystem\Exception\IOException,
    Symfony\Component\Finder\Finder;

class ScriptHandler {

    public static function createParametersYml(CommandEvent $event) {

        $source = realpath('.') . '/src/Mozza/Core/Resources/config/parameters.yml.dist';
        $destination = realpath('.') . '/app/parameters.yml';

        $filesystem = new Filesystem();

        if($filesystem->exists($destination)) {
            $event->getIO()->write('<error>Could not create "app/parameters.yml", as the file already exists.</error>');
            return;
        }

        if(!$filesystem->exists($source)) {
            $event->getIO()->write('<error>Could not create "app/parameters.yml", as the source file is not found ("' . $source . '").</error>');
            return;
        }

        try {
            $filesystem->copy($source, $destination);
        } catch(IOException $e) {
            $event->getIO()->write('<error>Could not create "app/parameters.yml"; ' . $e->getMessage() . '.</error>');
        }

        $event->getIO()->write('<info>The config file <comment>"app/parameters.yml"</comment> has been created using default values.</info>');
    }

    public static function forwardAssetsToWebDir(CommandEvent $event) {
        $extra = $event->getComposer()->getPackage()->getExtra();

        if(!array_key_exists('assetsforwarding', $extra)) {
            return;
        }

        if(!array_key_exists('packages', $extra['assetsforwarding'])) {
            return;
        }

        $sourcedir = realpath('.') . '/vendor/';
        $destinationdir = realpath('.') . '/' . trim($extra['assetsforwarding']['destdir'], '/') . '/';

        $filesystem = new Filesystem();

        if(!is_dir($destinationdir)) {
            #$event->getIO()->write('<error>The assetsforwarding.destdir specified in composer.json was not found in '.getcwd() . '.' . PHP_EOL . '</error>');
            #return;
            $filesystem->mkdir($destinationdir, 0777);
        }

        $filesystem = new Filesystem();

        foreach($extra['assetsforwarding']['packages'] as $packagename) {
            
            $originDir = $sourcedir . '/' . $packagename;
            if(!is_dir($originDir)) {
                $event->getIO()->write('<error>Package "' . $packagename . '" was not found ("' . $originDir . '")</error>');
                return;
            }

            $targetDir = $destinationdir . $packagename;

            $filesystem->remove($targetDir);
            $filesystem->mkdir($targetDir, 0777);
            
            // We use a custom iterator to ignore VCS files
            $filesystem->mirror($originDir, $targetDir, Finder::create()->ignoreDotFiles(false)->in($originDir));

            $event->getIO()->write('<info>Package "' . $packagename . '" assets have been forwarded to web dir.</info>');
        }
    }
}