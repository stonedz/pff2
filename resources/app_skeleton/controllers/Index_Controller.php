<?php

namespace pff\controllers;

use pff\Abs\AController;
use pff\Core\ModuleManager;
use pff\Core\ServiceContainer;

/**
 * Index controller
 */
class Index_Controller extends AController
{

    /**
     * Index action.
     *
     * @return mixed
     */
    public function index()
    {
        echo 'Welcome to pff!';
    }

    public function test()
    {

        echo 'Welcome to test pff!';
    }

    public function createDbEntry()
    {
        $db = ServiceContainer::get('dm');
        $test = new \pff\models\Test();
        $test->setName('test');
        $db->persist($test);
        $db->flush();
    }

    public function getDbEntry()
    {
        $db = ServiceContainer::get('dm');
        $test = $db->getRepository(\pff\models\Test::class)->find(1);
        echo $test->getName();
    }

    public function getAllDbEntries()
    {
        $db = ServiceContainer::get('dm');
        $tests = $db->getRepository(\pff\models\Test::class)->findAll();
        foreach ($tests as $test) {
            echo $test->getName() . '<br>';
        }
    }

    public function sendTestEmail()
    {
        /** @var \pff\modules\Mail\Mail $mailer */
        $mailer = ModuleManager::loadModule('mail');
        $mailer->sendMail('to@pff2.org', 'test@pff2.org', 'Test', 'Test', 'Test');

        echo 'Email sent';
    }
}
