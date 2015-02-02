<?php
/**
 * User: paolo.fagni@gmail.com
 * Date: 11/11/14
 * Time: 11.13
 */

namespace pff\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Parser;

class GeneratePhpStormMeta extends Command{

    protected function configure() {
        $this
            ->setName('phpstorm:generatemeta')
            ->setDescription('generate .phpstorm.meta.php file');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $output->write('Generating .phpstorm.meta.php file...');
        $fileContent =
            '<?php
                namespace PHPSTORM_META {

    $STATIC_METHOD_TYPES = [
        \pff\Core\ServiceContainer::get(\'\') => [
            \'config\' instanceof \pff\Config,
            \'hookmanager\' instanceof \pff\Core\HookManager,
            \'modulemanager\' instanceof \pff\Core\ModuleManager,
            \'helpermanager\' instanceof \pff\Core\HelperManager,
            \'app\' instanceof \pff\App,
            \'yamlparser\' instanceof \Symfony\Component\Yaml\Parser
        ]
        ];

        $STATIC_METHOD_TYPES = [
            \pff\Core\ModuleManager::loadModule(\'\') => [
            ';

        $parser = new Parser();
        $className = array();
        $modulesCorePath = 'vendor/stonedz/pff2/src/modules';
        $modulesUser = 'app/modules';
        $modulesComposer = 'modules';
        $ret = $this->getModules($modulesCorePath, $parser);
        $ret1 = $this->getModules($modulesUser, $parser);
        $ret2 = $this->getModules($modulesComposer, $parser);
        $result = array_merge(array_merge($ret, $ret1), $ret2);

        foreach($result as $moduleDir => $moduleClass) {
            $fileContent .= "\n'".$moduleDir."' instanceof ".$moduleClass.",";
        }

        $fileContent .= '
            ]
        ];
        }';
        $filePath = '.phpstorm.meta.php';

        file_put_contents($filePath, $fileContent);

        $output->writeln('<info>DONE</info>');
    }

    /**
     * @param $modulePath
     * @return array
     */
    private function getModules($modulePath, Parser $parser) {
        $modules = array_diff(scandir($modulePath), array('..','.','.gitignore'));
        $className = array();
        foreach ($modules as $moduleDir) {
            $moduleConf = $parser->parse(file_get_contents($modulePath.'/'.$moduleDir.'/module.yaml'));
            $className[$moduleDir] = '\pff\modules\\'.$moduleConf['class'];
        }
        return $className;
    }

} 