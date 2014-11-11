<?php

namespace pff\Factory;
use pff\Abs\AView;
use pff\App;
use pff\Core\ModuleManager;
use pff\Core\ServiceContainer;
use pff\Core\ViewPHP;
use pff\Core\ViewSmarty;

/**
 * Views Factory
 *
 * @author paolo.fagni<at>gmail.com
 */
class FView {

    /**
     * Gets an AView object
     *
     * @static
     * @param string $templateName The name of the template
     * @param App $app
     * @param string $templateType Te type of the template
     * @return AView
     */
    static public function create($templateName, App $app = null, $templateType = null) {
        $standardTemplate = $templateName;


        if ($templateType === null) {
            $tmp          = explode('.', $templateName);
            $templateType = $tmp[count($tmp) - 1];
        } else {
            $templateType = strtolower($templateType);
        }

        return self::loadTemplate($templateName, $templateType);
    }

    static private function loadTemplate($templateName, $templateType) {

        switch ($templateType) {
            case 'php':
                $templateName = self::checkMobile($templateName, 'php');
                return new ViewPHP($templateName);
                break;
            case 'tpl':
            case 'smarty':
                $templateName = self::checkMobile($templateName, 'smarty');
                return new ViewSmarty($templateName);
                break;
            default:
                $templateName = self::checkMobile($templateName, 'php');
                return new ViewPHP($templateName);
                break;

        }
    }

    /**
     * @param $templateName
     * @param $type
     * @throws \pff\Exception\ModuleException
     * @internal param ModuleManager $mm
     * @return array
     */
    private static function checkMobile($templateName, $type) {
        $mm = ServiceContainer::get('modulemanager');
        if ($mm->isLoaded('mobile_views')) {

            /** @var \pff\modules\MobileViews $mobileViews */
            $mobileViews = $mm->getModule('mobile_views');
            if ($mobileViews->isMobile() || $mobileViews->getMobileViewOnly()) {
                $tmp = explode('.', $templateName);
                $tmp[0] .= '_mobile';
                $tempTemplateName = implode('.', $tmp);

                if($type == 'php') {
                    $templatePath = ROOT . DS . 'app' . DS . 'views' . DS .  $tempTemplateName;
                }
                else{ // smarty
                    $templatePath = ROOT . DS . 'app' . DS . 'views' . DS . 'smarty' . DS . 'templates' . DS . $tempTemplateName;
                }

                if (file_exists($templatePath)) {
                    return $tempTemplateName;
                }
                else {
                    return $templateName;
                }
            }
            else {
                return $templateName;
            }
        }
        else {
            return $templateName;
        }
    }
}
