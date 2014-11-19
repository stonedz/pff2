<?php
/**
 *
 * @author paolo.fagni<at>gmail.com
 */
class HookManagerTest extends PHPUnit_Framework_TestCase {

    /**
     * @var \pff\Core\HookManager
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     *
     */
    protected function setUp() {
        $conf         = new \pff\Config('config.user.php', 'tests/assets');
        $this->object = new \pff\Core\HookManager($conf);
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     *
     * @return void
     */
    protected function tearDown() {
    }

    public function testInitialStateIsValid() {
        $this->assertEmpty($this->object->getBeforeController());
        $this->assertEmpty($this->object->getAfterController());
    }

    public function testRegisterABeforeProvider() {
        $stub = $this->getMock('\\pff\\Iface\\IBeforeHook');
        $stub->expects($this->any())
             ->method('doBefore')
             ->will($this->returnValue('done'));

        $this->object->registerHook($stub, 'name');

        $listOfHooks = $this->object->getBeforeController();
        $this->assertNotEmpty($listOfHooks);
        $this->assertEmpty($this->object->getAfterController());
        $this->assertEmpty($this->object->getBeforeSystem());
        $this->assertEquals('done', $listOfHooks['name']->doBefore());
    }

    public function testRegisterAnAfterProvider() {
        $stub = $this->getMock('\\pff\\Iface\\IAfterHook');
        $stub->expects($this->any())
            ->method('doAfter')
            ->will($this->returnValue('done'));

        $this->object->registerHook($stub, 'name');

        $listOfHooks = $this->object->getAfterController();
        $this->assertNotEmpty($listOfHooks);
        $this->assertEquals('done', $listOfHooks['name']->doAfter());
    }

    public function testRegisterABeforeSystemProvider() {
        $stub = $this->getMock('\\pff\\Iface\\IBeforeSystemHook');
        $stub->expects($this->any())
            ->method('doBeforeSystem')
            ->will($this->returnValue('done'));

        $this->object->registerHook($stub, 'name');

        $listOfHooks = $this->object->getBeforeSystem();
        $this->assertNotEmpty($listOfHooks);
        $this->assertEquals('done', $listOfHooks['name']->doBeforeSystem());
    }

    public function testFailsToRegisterAnEmptyProvider() {
        $this->setExpectedException('\\pff\\Exception\\HookException');
        $stub = $this->getMock('\\pff\\Iface\\IHookProvider');
        $this->object->registerHook($stub, 'name');
    }

}
