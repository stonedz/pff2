<?php
/**
 * ModuleManager test suite
 *
 * @author paolo.fagni<at>gmail.com
 * @covers \pff\ModuleManager
 */
class ModuleManagerTest extends PHPUnit_Framework_TestCase {

    /**
     * @var \pff\Core\ModuleManager
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     *
     */
    protected function setUp() {
        $conf         = new \pff\Config('config.user.php', 'tests/assets');
        $this->object = new \pff\Core\ModuleManager($conf);
        $hookManager  = $this->getMock('\\pff\\Core\\HookManager', array(), array($conf));
        $this->object->setHookManager($hookManager);
        $this->object->initModules();
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     *
     * @return void
     */
    protected function tearDown() {
    }

    /**
     * @covers \pff\ModuleManager::loadModule
     */
    public function testLoadModuleFailsWithNonexistentModule() {
        $this->setExpectedException('\\pff\\Exception\\ModuleException');
        $this->object->loadModule('No_i_do_not_exist');
    }

    /**
     * Test with Logger module
     */
    public function testGetModule() {
        $tmp = $this->object->getModule('Logger');
        $this->assertInstanceOf('\\pff\\Abs\\AModule', $tmp);
    }

    public function testGetModuleFailsWithNonexistentModuleRequest() {
        $this->setExpectedException('\\pff\\Exception\\ModuleException');
        $this->object->getModule('I_AM_NOT_A_MODULE_AND_NEVER_WILL');
    }

    public function testGetHookManager() {
        $this->assertInstanceOf('\\pff\\Core\\HookManager', $this->object->getHookManager());
    }

}
