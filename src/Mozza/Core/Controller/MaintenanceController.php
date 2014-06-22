<?php

namespace Mozza\Core\Controller;

use Silex\Application,
    Symfony\Component\HttpFoundation\Request,
    Symfony\Component\HttpFoundation\Response,
    Twig_Environment;

use Mozza\Core\Exception as MozzaException,
    Mozza\Core\Services as MozzaServices;

class MaintenanceController {

    protected $twig;

    public function __construct(Twig_Environment $twig) {
        $this->twig = $twig;
    }

    public function reactToExceptionAction(Request $request, Application $app, MozzaException\MaintenanceNeeded\MaintenanceNeededExceptionInterface $e) {

        /*
            Maintenance actions are not yet implemented in Mozza;
            TODO: Implement maintenance actions and map them here
        */

        /*switch(TRUE) {
            case $e instanceOf MozzaException\MaintenanceNeeded\DatabaseInvalidCredentialsMaintenanceNeededException: {
                $action = 'databaseInvalidCredentialsAction';
                break;
            }
            case $e instanceOf MozzaException\MaintenanceNeeded\DatabaseUpdateMaintenanceNeededException: {
                $action = 'databaseUpdateAction';
                break;
            }
            case $e instanceOf MozzaException\MaintenanceNeeded\AdministrativeAccountMissingMaintenanceNeededException: {
                $action = 'administrativeAccountMissingAction';
                break;
            }
            case $e instanceOf MozzaException\MaintenanceNeeded\SystemStatusMissingMaintenanceNeededException: {
                $action = 'systemStatusMissingAction';
                break;
            }
            case $e instanceOf MozzaException\MaintenanceNeeded\SiteConfigFileMissingMaintenanceNeededException: {
                $action = 'siteConfigFileMissingAction';
                break;
            }
            default: {
                $action = 'unknownMaintenanceTaskAction';
                break;
            }
        }

        return $this->$action(
            $request,
            $app,
            $e
        );*/
    }

    public function proceedWithRequestAction(Request $request, Application $app, MozzaException\MaintenanceNeeded\MaintenanceNeededExceptionInterface $e) {
        /*
            Maintenance routes are not yet defined in Mozza;
            TODO: Implement maintenance routes and map them here
        */
    }

    public function databaseInvalidCredentialsAction(Request $request, Application $app, MozzaException\MaintenanceNeeded\DatabaseInvalidCredentialsMaintenanceNeededException $e) {
        return $this->twig->render('@MozzaCore/Maintenance/databaseinvalidcredentials.html.twig');
    }

    public function databaseUpdateAction(Request $request, Application $app, MozzaException\MaintenanceNeeded\DatabaseUpdateMaintenanceNeededException $e) {
        return $this->twig->render('@MozzaCore/Maintenance/databaseupdate.html.twig');
    }

    public function administrativeAccountMissingAction(Request $request, Application $app, MozzaException\MaintenanceNeeded\AdministrativeAccountMissingMaintenanceNeededException $e) {
        return $this->twig->render('@MozzaCore/Maintenance/administrativeaccountmissing.html.twig');
    }

    public function systemStatusMissingAction(Request $request, Application $app, MozzaException\MaintenanceNeeded\SystemStatusMissingMaintenanceNeededException $e) {
        return $this->twig->render('@MozzaCore/Maintenance/systemstatusmissing.html.twig');
    }

    public function siteConfigFileMissingAction(Request $request, Application $app, MozzaException\MaintenanceNeeded\SiteConfigFileMissingMaintenanceNeededException $e) {
        return $this->twig->render('@MozzaCore/Maintenance/siteconfigfilemissing.html.twig');
    }

    public function unknownMaintenanceTaskAction(Request $request, Application $app, MozzaException\MaintenanceNeeded\MaintenanceNeededExceptionInterface $e) {
        return $this->twig->render('@MozzaCore/Maintenance/unknownmaintenancetask.html.twig');
    }
}