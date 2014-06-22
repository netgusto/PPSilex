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

    public function reactToExceptionAction(
        Request $request,
        Application $app,
        MozzaException\InitializationNeeded\InitializationNeededExceptionInterface $e
    ) {

        if($this->environment->getInitializationMode() !== TRUE) {
            return new Response('Initialization mode off. Access denied.', 401);
        }

        switch(TRUE) {
            case $e instanceOf MozzaException\InitializationNeeded\DatabaseMissingInitializationNeededException: {
                $action = 'databaseMissingAction';
                break;
            }
            case $e instanceOf MozzaException\InitializationNeeded\DatabaseEmptyInitializationNeededException: {
                $action = 'databaseEmptyAction';
                break;
            }
            default: {
                $action = 'unknownInitializationTaskAction';
                break;
            }
        }

        return $this->$action(
            $request,
            $app,
            $e
        );
    }

    public function proceedWithInitializationRequestAction(
        Request $request,
        Application $app,
        MozzaException\InitializationNeeded\InitializationNeededExceptionInterface $e
    ) {

        if($this->environment->getInitializationMode() !== TRUE) {
            return new Response('Initialization mode off. Access denied.', 401);
        }

        if($request->attributes->get('_route') === '_init_welcome') {

            $createdb = ($e instanceOf MozzaException\InitializationNeeded\DatabaseMissingInitializationNeededException);
            $createschema = $createdb || ($e instanceOf MozzaException\InitializationNeeded\DatabaseEmptyInitializationNeededException);

            return $this->welcomeAction($request, $app, array(
                'createdb' => $createdb,
                'createschema' => $createschema,
            ));
        }

        if($request->attributes->get('_route') === '_init_step1_createdb') {
            return $this->step1CreateDbAction($request, $app);
        }

        if($request->attributes->get('_route') === '_init_step1_createschema') {
            return $this->step1CreateSchemaAction($request, $app);
        }

        if($request->attributes->get('_route') === '_init_step2') {
            return $this->step2Action($request, $app);
        }

        if($request->attributes->get('_route') === '_init_finish') {
            return $this->finishAction($request, $app);
        }
    }


    public function databaseMissingAction(Request $request, Application $app, MozzaException\InitializationNeeded\DatabaseMissingInitializationNeededException $e) {
        return new RedirectResponse($this->urlgenerator->generate('_init_welcome'));
    }

    public function databaseEmptyAction(Request $request, Application $app, MozzaException\InitializationNeeded\DatabaseEmptyInitializationNeededException $e) {
        return new RedirectResponse($this->urlgenerator->generate('_init_welcome'));
    }

    public function welcomeAction(Request $request, Application $app, $tasks = array()) {

        if($this->environment->getInitializationMode() !== TRUE) {
            return new Response('Initialization mode off. Access denied.', 401);
        }

        if($tasks['createdb']) {
            $nextroute = '_init_step1_createdb';
        } elseif($tasks['createschema']) {
            $nextroute = '_init_step1_createschema';
        } else {
            # Database is OK; proceed to next step
            # Should never be the case here
            $nextroute = '_init_step2';
        }

        return $this->twig->render('@MozzaCore/Initialization/welcome.html.twig', array(
            'nextroute' => $nextroute,
        ));
    }

    public function step1CreateDbAction(Request $request, Application $app) {
        
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

        return $this->twig->render('@MozzaCore/Initialization/init_step1_createdb.html.twig', array(
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

        return $this->twig->render('@MozzaCore/Initialization/init_step1_createschema.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    public function step2Action(Request $request, Application $app) {
        
        $form = $this->formfactory->create(new FormType\WelcomeStep2Type());
        $form->handleRequest($request);
        if($form->isValid()) {
            var_dump($form->getData());
           die('VALID !');
        }

        return $this->twig->render('@MozzaCore/Initialization/init_step2.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    public function finishAction(Request $request, Application $app) {
        return $this->twig->render('@MozzaCore/Initialization/init_finish.html.twig');
    }

    /* Utilitary functions */

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