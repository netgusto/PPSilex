<?php

namespace Mozza\Core\Controller;

use Silex\Application,
    Symfony\Component\HttpFoundation\Request,
    Symfony\Component\HttpFoundation\Response,
    Twig_Environment;

use Mozza\Core\Exception as MozzaException;

class MaintenanceController {

    protected $twig;

    public function __construct(Twig_Environment $twig) {
        $this->twig = $twig;
    }

    public function reactToExceptionAction(Request $request, Application $app, MozzaException\ApplicationNeedsMaintenanceExceptionInterface $e, $code) {

        if($e instanceOf MozzaException\SiteConfigFileMissingException) {
            return $this->siteConfigFileMissingAction(
                $request,
                $app,
                $e
            );
        }

        if($e instanceOf MozzaException\SystemStatusMissingException) {
            return $this->systemStatusMissingAction(
                $request,
                $app,
                $e
            );
        }

        if($e instanceOf MozzaException\DatabaseNeedsUpdateException) {
            return $this->databaseNeedsUpdateAction(
                $request,
                $app,
                $e
            );
        }

        return new Response('Application needs maintenance.');

        #return $this->twig->render('@MozzaTheme/Error/error.notfound.html.twig');
    }

    public function siteConfigFileMissingAction(Request $request, Application $app, MozzaException\SiteConfigFileMissingException $e) {
        return $this->twig->render('@MozzaCore/Maintenance/siteconfigfilemissing.html.twig');
    }

    public function systemStatusMissingAction(Request $request, Application $app, MozzaException\SystemStatusMissingException $e) {
        return $this->twig->render('@MozzaCore/Maintenance/systemstatusmissing.html.twig');
    }

    public function databaseNeedsUpdateAction(Request $request, Application $app, MozzaException\DatabaseNeedsUpdateException $e) {
        return $this->twig->render('@MozzaCore/Maintenance/databaseneedsupdate.html.twig');
    }
}