<?php

namespace Mozza\Core\Controller;

use Silex\Application,
    Symfony\Component\HttpFoundation\Request,
    Symfony\Component\HttpFoundation\Response,
    Symfony\Component\HttpFoundation\RedirectResponse,
    Symfony\Component\Routing\Generator\UrlGenerator,
    Symfony\Component\Form\FormFactory,
    Symfony\Component\Yaml\Yaml,
    Twig_Environment;

use \Doctrine\ORM\EntityManager;

use Mozza\Core\Exception as MozzaException,
    Mozza\Core\Services as MozzaServices,
    Mozza\Core\Form\Type as FormType,
    Mozza\Core\Entity\SystemStatus,
    Mozza\Core\Entity\HierarchicalConfig;

class InitializationController {

    protected $twig;
    protected $environment;

    public function __construct(
        Twig_Environment $twig,
        MozzaServices\Context\EnvironmentService $environment,
        UrlGenerator $urlgenerator,
        FormFactory $formfactory,
        EntityManager $em
    ) {
        $this->twig = $twig;
        $this->environment = $environment;
        $this->urlgenerator = $urlgenerator;
        $this->formfactory = $formfactory;
        $this->em = $em;
    }

    public function reactToExceptionAction(Request $request, Application $app, MozzaException\ApplicationNeedsMaintenanceExceptionInterface $e, $code) {

        if($this->environment->getInitializationMode() !== TRUE) {
            return new Response('Initialization mode off. Access denied.', 401);
        }

        switch(TRUE) {
            case $e instanceOf MozzaException\DatabaseInvalidCredentialsException: {
                $action = 'databaseInvalidCredentialsExceptionAction';
                break;
            }
            case $e instanceOf MozzaException\DatabaseMissingException: {
                $action = 'databaseMissingExceptionAction';
                break;
            }
            case $e instanceOf MozzaException\DatabaseEmptyException: {
                $action = 'databaseEmptyExceptionAction';
                break;
            }
            case $e instanceOf MozzaException\DatabaseNeedsUpdateException: {
                $action = 'databaseNeedsUpdateExceptionAction';
                break;
            }
            case $e instanceOf MozzaException\AdministrativeAccountMissingException: {
                $action = 'administrativeAccountMissingExceptionAction';
                break;
            }
            case $e instanceOf MozzaException\SystemStatusMissingException: {
                $action = 'systemStatusMissingExceptionAction';
                break;
            }
            case $e instanceOf MozzaException\SiteConfigFileMissingException: {
                $action = 'siteConfigFileMissingExceptionAction';
                break;
            }
            default: {
                $action = 'unknownMaintenanceTaskExceptionAction';
                break;
            }
        }

        return $this->$action(
            $request,
            $app,
            $e
        );
    }

    public function proceedWithInitializationRequestAction(Request $request, Application $app, MozzaException\ApplicationNeedsMaintenanceExceptionInterface $e) {

        if($this->environment->getInitializationMode() !== TRUE) {
            return new Response('Initialization mode off. Access denied.', 401);
        }

        if($request->attributes->get('_route') === '_init_welcome') {

            $createdb = ($e instanceOf MozzaException\DatabaseMissingException);
            $createschema = $createdb || ($e instanceOf MozzaException\DatabaseEmptyException);
            $updateschema = ($e instanceOf MozzaException\DatabaseNeedsUpdateException);

            return $this->welcomeAction($request, $app, array(
                'createdb' => $createdb,
                'createschema' => $createschema,
                'updateschema' => $updateschema,
            ));
        }

        if($request->attributes->get('_route') === '_init_step1_createdb') {
            return $this->step1CreateDbAction($request, $app);
        }

        if($request->attributes->get('_route') === '_init_step1_createschema') {
            return $this->step1CreateSchemaAction($request, $app);
        }

        if($request->attributes->get('_route') === '_init_step1_updateschema') {
            return $this->step1UpdateSchemaAction($request, $app);
        }

        if($request->attributes->get('_route') === '_init_step2') {
            return $this->step2Action($request, $app);
        }

        if($request->attributes->get('_route') === '_init_finish') {
            return $this->finishAction($request, $app);
        }
    }

    public function welcomeAction(Request $request, Application $app, $tasks = array()) {

        if($this->environment->getInitializationMode() !== TRUE) {
            return new Response('Initialization mode off. Access denied.', 401);
        }

        if($tasks['createdb']) {
            $nextroute = '_init_step1_createdb';
        } elseif($tasks['createschema']) {
            $nextroute = '_init_step1_createschema';
        } elseif($tasks['updateschema']) {
            $nextroute = '_init_step1_updateschema';
        } else {
            # Database is OK; proceed to next step
            # Should never be the case here
            $nextroute = '_init_step2';
        }

        return $this->twig->render('@MozzaCore/Maintenance/welcome.html.twig', array(
            'nextroute' => $nextroute,
        ));
    }

    public function initStep1CreateDbAction(Request $request, Application $app) {
        
        $form = $this->formfactory->create(new FormType\WelcomeStep1Type());
        $form->handleRequest($request);

        if($form->isValid()) {
            # The database is created and initialized
            $this->createDatabase($this->em->getConnection());
            $this->createSchema($this->em);
            $this->createSystemStatus($this->em, $app['version']);
            $this->createSiteConfig($this->em, $app['environment']->getRootDir());

            return new RedirectResponse($this->urlgenerator->generate('_init_step2'));
        }

        return $this->twig->render('@MozzaCore/Maintenance/init_step1_createdb.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    public function step1CreateSchemaAction(Request $request, Application $app) {
        
        $form = $this->formfactory->create(new FormType\WelcomeStep1Type());
        $form->handleRequest($request);

        if($form->isValid()) {
            # The schemas are created
            $this->createSchema($this->em);
            $this->createSystemStatus($this->em, $app['version']);
            $this->createSiteConfig($this->em, $app['environment']->getRootDir());

            return new RedirectResponse($this->urlgenerator->generate('_init_step2'));
        }

        return $this->twig->render('@MozzaCore/Maintenance/init_step1_createschema.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    public function step1UpdateSchemaAction(Request $request, Application $app) {
        return 'initStep1UpdateSchemaAction';
    }

    public function step2Action(Request $request, Application $app) {
        
        $form = $this->formfactory->create(new FormType\WelcomeStep2Type());
        $form->handleRequest($request);
        if($form->isValid()) {
            var_dump($form->getData());
           die('VALID !');
        }

        return $this->twig->render('@MozzaCore/Maintenance/init_step2.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    public function finishAction(Request $request, Application $app) {
        return $this->twig->render('@MozzaCore/Maintenance/init_finish.html.twig');
    }

    public function databaseInvalidCredentialsExceptionAction(Request $request, Application $app, MozzaException\DatabaseInvalidCredentialsException $e) {
        return $this->twig->render('@MozzaCore/Maintenance/databaseinvalidcredentials.html.twig');
    }

    public function databaseMissingExceptionAction(Request $request, Application $app, MozzaException\DatabaseMissingException $e) {
        return new RedirectResponse($this->urlgenerator->generate('_init_welcome'));
    }

    public function databaseEmptyExceptionAction(Request $request, Application $app, MozzaException\DatabaseEmptyException $e) {
        return new RedirectResponse($this->urlgenerator->generate('_init_welcome'));
    }

    public function databaseNeedsUpdateExceptionAction(Request $request, Application $app, MozzaException\DatabaseNeedsUpdateException $e) {
        return $this->twig->render('@MozzaCore/Maintenance/databaseneedsupdate.html.twig');
    }

    public function administrativeAccountMissingExceptionAction(Request $request, Application $app, MozzaException\AdministrativeAccountMissingException $e) {
        return $this->twig->render('@MozzaCore/Maintenance/administrativeaccountmissing.html.twig');
    }

    public function systemStatusMissingExceptionAction(Request $request, Application $app, MozzaException\SystemStatusMissingException $e) {
        return $this->twig->render('@MozzaCore/Maintenance/systemstatusmissing.html.twig');
    }

    public function siteConfigFileMissingExceptionAction(Request $request, Application $app, MozzaException\SiteConfigFileMissingException $e) {
        return $this->twig->render('@MozzaCore/Maintenance/siteconfigfilemissing.html.twig');
    }

    public function unknownMaintenanceTaskExceptionAction(Request $request, Application $app, MozzaException\ApplicationNeedsMaintenanceExceptionInterface $e) {
        return $this->twig->render('@MozzaCore/Maintenance/unknownmaintenancetask.html.twig');
    }



    protected function createDatabase(\Doctrine\DBAL\Connection $connection) {
        $databasecreator = new MozzaServices\Maintenance\DatabaseCreatorService();
        return $databasecreator->createDatabase($connection);
    }

    protected function createSchema(\Doctrine\ORM\EntityManager $em) {
        $ormschemacreator = new MozzaServices\Maintenance\ORMSchemaCreatorService();
        return $ormschemacreator->createSchema($em);
    }

    protected function createSystemStatus(\Doctrine\ORM\EntityManager $em, $appversion) {
        $systemStatus = new SystemStatus();
        $systemStatus->setConfiguredversion($appversion);

        $em->persist($systemStatus);
        $em->flush();
    }

    protected function createSiteConfig(\Doctrine\ORM\EntityManager $em, $rootdir) {

        $configfile = $rootdir . '/data/config/config.yml';

        $siteconfig = new HierarchicalConfig();
        $siteconfig->setName('config.site');
        $siteconfig->setConfig(
            Yaml::parse($configfile)
        );

        $em->persist($siteconfig);
        $em->flush();
    }
}