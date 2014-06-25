<?php

namespace Pulpy\Core\Controller;

use Silex\Application,
    Symfony\Component\HttpFoundation\Request,
    Symfony\Component\HttpFoundation\Response,
    Twig_Environment;

use Pulpy\Core\Exception as PulpyException,
    Pulpy\Core\Services as PulpyServices;

class MaintenanceController {

    protected $twig;

    public function __construct(Twig_Environment $twig) {
        $this->twig = $twig;
    }

    public function reactToExceptionAction(Request $request, Application $app, PulpyException\MaintenanceNeeded\MaintenanceNeededExceptionInterface $e) {

        /*
            Maintenance actions are not yet implemented in Pulpy;
            TODO: Implement maintenance actions and map them here
        */

        /*switch(TRUE) {
            case $e instanceOf PulpyException\MaintenanceNeeded\DatabaseInvalidCredentialsMaintenanceNeededException: {
                $action = 'databaseInvalidCredentialsAction';
                break;
            }
            case $e instanceOf PulpyException\MaintenanceNeeded\DatabaseUpdateMaintenanceNeededException: {
                $action = 'databaseUpdateAction';
                break;
            }
            case $e instanceOf PulpyException\MaintenanceNeeded\AdministrativeAccountMissingMaintenanceNeededException: {
                $action = 'administrativeAccountMissingAction';
                break;
            }
            case $e instanceOf PulpyException\MaintenanceNeeded\SystemStatusMissingMaintenanceNeededException: {
                $action = 'systemStatusMissingAction';
                break;
            }
            case $e instanceOf PulpyException\MaintenanceNeeded\SiteConfigFileMissingMaintenanceNeededException: {
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

    public function proceedWithRequestAction(Request $request, Application $app, PulpyException\MaintenanceNeeded\MaintenanceNeededExceptionInterface $e) {
        /*
            Maintenance routes are not yet defined in Pulpy;
            TODO: Implement maintenance routes and map them here
        */
    }

    public function databaseInvalidCredentialsAction(Request $request, Application $app, PulpyException\MaintenanceNeeded\DatabaseInvalidCredentialsMaintenanceNeededException $e) {
        return $this->twig->render('@PulpyCore/Maintenance/databaseinvalidcredentials.html.twig');
    }

    public function databaseUpdateAction(Request $request, Application $app, PulpyException\MaintenanceNeeded\DatabaseUpdateMaintenanceNeededException $e) {
        return $this->twig->render('@PulpyCore/Maintenance/databaseupdate.html.twig');
    }

    public function administrativeAccountMissingAction(Request $request, Application $app, PulpyException\MaintenanceNeeded\AdministrativeAccountMissingMaintenanceNeededException $e) {
        return $this->twig->render('@PulpyCore/Maintenance/administrativeaccountmissing.html.twig');
    }

    public function systemStatusMissingAction(Request $request, Application $app, PulpyException\MaintenanceNeeded\SystemStatusMissingMaintenanceNeededException $e) {
        return $this->twig->render('@PulpyCore/Maintenance/systemstatusmissing.html.twig');
    }

    public function siteConfigFileMissingAction(Request $request, Application $app, PulpyException\MaintenanceNeeded\SiteConfigFileMissingMaintenanceNeededException $e) {
        return $this->twig->render('@PulpyCore/Maintenance/siteconfigfilemissing.html.twig');
    }

    public function unknownMaintenanceTaskAction(Request $request, Application $app, PulpyException\MaintenanceNeeded\MaintenanceNeededExceptionInterface $e) {
        return $this->twig->render('@PulpyCore/Maintenance/unknownmaintenancetask.html.twig');
    }
}