<?php

namespace Pulpy\Core\Composer;

use Composer\Script\CommandEvent,
    Symfony\Component\Filesystem\Filesystem,
    Symfony\Component\Filesystem\Exception\IOException,
    Symfony\Component\Finder\Finder;

class ScriptHandler {

    public static function forwardAssetsToWebDir(CommandEvent $event) {
        $extra = $event->getComposer()->getPackage()->getExtra();

        if(!array_key_exists('assetsforwarding', $extra)) {
            return;
        }

        if(array_key_exists('packages', $extra['assetsforwarding'])) {
            self::forwardPackages(
                $event,
                $extra['assetsforwarding']['packages'],
                realpath('.') . '/vendor/',     // sourcedir
                realpath('.') . '/web/vendor/'  // destinationdir
            );
        }

        if(array_key_exists('bundles', $extra['assetsforwarding'])) {
            self::forwardBundlesPublicResources(
                $event,
                $extra['assetsforwarding']['bundles'],
                realpath('.') . '/src/',         // sourcedir
                realpath('.') . '/web/bundles/'     // destinationdir
            );
        }
    }

    protected static function forwardPackages($event, $packages, $sourcedir, $destinationdir) {

        $filesystem = new Filesystem();

        if(!is_dir($destinationdir)) {
            $filesystem->mkdir($destinationdir, 0777);
        }

        foreach($packages as $packagename) {
            
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

    protected static function forwardBundlesPublicResources($event, $bundles, $sourcedir, $destinationdir) {

        $filesystem = new Filesystem();

        if(!is_dir($destinationdir)) {
            $filesystem->mkdir($destinationdir, 0777);
        }

        foreach($bundles as $bundlename) {
            
            $originDir = $sourcedir . $bundlename . '/Resources/public/';

            if(!is_dir($originDir)) {
                $event->getIO()->write('<error>Bundle "' . $bundlename . '" was not found ("' . $originDir . '")</error>');
                return;
            }

            $targetDir = $destinationdir . trim(strtolower(str_replace(array('\\', '/'), '', $bundlename)));

            $filesystem->remove($targetDir);
            $filesystem->mkdir($targetDir, 0777);
            
            // We use a custom iterator to ignore VCS files
            $filesystem->mirror($originDir, $targetDir, Finder::create()->ignoreDotFiles(false)->in($originDir));

            $event->getIO()->write('<info>Bundle "' . $bundlename . '" public resources have been forwarded to web dir.</info>');
        }
    }
}