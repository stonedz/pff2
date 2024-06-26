<?php

namespace pff\modules;

use pff\Abs\AModule;
use pff\Iface\IBeforeSystemHook;

/**
 *
 * @author paolo.fagni<at>gmail.com
 */
class MultiLanguage extends AModule implements IBeforeSystemHook
{
    /**
     * Contains the user selected language if specified in URL or the default specified in the configuration
     *
     * @var string
     */
    private $_selectedLanguage;

    /**
     * If true saves the preferred language on cookies!
     *
     * @var bool
     */
    private $_saveOnCookies;

    /**
     * If true saves the preferred language on session!
     *
     * @var bool
     */
    private $_saveOnSession;

    /**
     * The name of the cookie to save
     *
     * @var string
     */
    private $_cookieName;

    /**
     * The key of the session array to save
     *
     * @var string
     */
    private $_sessionKeyName;

    /**
     * Contains the specified default language, this could be overrider by $pffConfig['default_language']
     *
     * @var string
     */
    private $_defaultLang;

    private $_accepted_languages;

    public function __construct($confFile = 'multilanguage/module.conf.yaml')
    {
        $this->_accepted_languages = [];
        $moduleconfig = $this->readConfig($confFile);
        $this->_loadConfig($moduleconfig);
    }

    /**
     * Loads the configuration file
     */
    private function _loadConfig($parsedConfig)
    {
        $this->_defaultLang     = $parsedConfig['moduleConf']['default_language'];
        $this->_saveOnCookies   = $parsedConfig['moduleConf']['save_on_cookies'];
        $this->_cookieName      = $parsedConfig['moduleConf']['cookie_name'];
        $this->_saveOnSession   = $parsedConfig['moduleConf']['save_on_session'];
        $this->_sessionKeyName  = $parsedConfig['moduleConf']['session_key_name'];
        if (isset($parsedConfig['moduleConf']['accepted_languages'])) {
            foreach ($parsedConfig['moduleConf']['accepted_languages'] as $l) {
                $this->_accepted_languages[] = $l;
            }
        } else {
            $this->_accepted_languages = null;
        }
    }

    /**
     * Executed before the system startup
     *
     * @return mixed
     */
    public function doBeforeSystem()
    {
        $url = $this->_app->getUrl();
        $url = $this->processUrl($url);
        if (is_null($this->_selectedLanguage)) { // No language code has been found in URL request
            $this->chooseLanguage();
        }
        $this->_app->setUrl($url);
    }

    /**
     * Processes the url to find a language
     *
     * @param $url
     * @return string
     */
    public function processUrl($url)
    {
        $splittedUrl = explode('/', $url);
        $langCodes = $this->getCodes();

        if (isset($langCodes[$splittedUrl[0]])) {
            if ($this->_accepted_languages !== null && is_array($this->_accepted_languages)) {
                if (in_array($splittedUrl[0], $this->_accepted_languages)) {
                    $this->_selectedLanguage = array_shift($splittedUrl);
                } else {
                    $this->_selectedLanguage = $this->_defaultLang;
                }
            } else {
                $this->_selectedLanguage = array_shift($splittedUrl);
            }
            $this->saveLanguage();

            $processedUrl = implode('/', $splittedUrl);
        } elseif (isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) && isset($langCodes[substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2)])) {
            $this->_selectedLanguage = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
            if ($this->_accepted_languages === null) {
                $this->_selectedLanguage = $this->_defaultLang;
            } elseif (is_array($this->_accepted_languages)) {
                if (!in_array($this->_selectedLanguage, $this->_accepted_languages)) {
                    $this->_selectedLanguage = $this->_defaultLang;
                }
            }
            $this->saveLanguage();

            $processedUrl = $url;
        } else {
            $this->_selectedLanguage = $this->_defaultLang;
            $this->saveLanguage();

            $processedUrl = $url;
        }
        return $processedUrl;
    }

    /**
     * Saves the choosen language preference
     */
    private function saveLanguage()
    {
        if ($this->_saveOnCookies) {
            $this->getRequiredModules('cookies')->setCookie($this->_cookieName, $this->_selectedLanguage, 30 * 24);
        }
        if ($this->_saveOnSession) {
            $_SESSION[$this->_sessionKeyName] = $this->_selectedLanguage;
            $_SESSION['default_language'] = $this->_defaultLang;
        }
    }

    /**
     * Chooses a default language. First check for cookies, then user configuration, then module configuration
     */
    public function chooseLanguage()
    {
        if ($this->_saveOnCookies &&
            $tmpLng = $this->getRequiredModules('cookies')->getCookie($this->_cookieName)
        ) {
            $this->_selectedLanguage = $tmpLng;
        } elseif (isset($this->_app)) {
            try {
                $this->_selectedLanguage = $this->_app->getConfig()->getConfigData('default_language');
            } catch (\pff\ConfigException $e) {
                $this->_selectedLanguage = $this->_defaultLang;
            }
        } else {
            $this->_selectedLanguage = $this->_defaultLang;
        }
    }

    /**
     * Returns an array with the language codes "lang_code" => "english_name"
     *
     * @return array
     */
    private function getCodes()
    {
        $languageCodes = [
            "aa" => "Afar",
            "ab" => "Abkhazian",
            "ae" => "Avestan",
            "af" => "Afrikaans",
            "ak" => "Akan",
            "am" => "Amharic",
            "an" => "Aragonese",
            "ar" => "Arabic",
            "as" => "Assamese",
            "av" => "Avaric",
            "ay" => "Aymara",
            "az" => "Azerbaijani",
            "ba" => "Bashkir",
            "be" => "Belarusian",
            "bg" => "Bulgarian",
            "bh" => "Bihari",
            "bi" => "Bislama",
            "bm" => "Bambara",
            "bn" => "Bengali",
            "bo" => "Tibetan",
            "br" => "Breton",
            "bs" => "Bosnian",
            "ca" => "Catalan",
            "ce" => "Chechen",
            "ch" => "Chamorro",
            "co" => "Corsican",
            "cr" => "Cree",
            "cs" => "Czech",
            "cu" => "Church Slavic",
            "cv" => "Chuvash",
            "cy" => "Welsh",
            "da" => "Danish",
            "de" => "German",
            "dv" => "Divehi",
            "dz" => "Dzongkha",
            "ee" => "Ewe",
            "el" => "Greek",
            "en" => "English",
            "eo" => "Esperanto",
            "es" => "Spanish",
            "et" => "Estonian",
            "eu" => "Basque",
            "fa" => "Persian",
            "ff" => "Fulah",
            "fi" => "Finnish",
            "fj" => "Fijian",
            "fo" => "Faroese",
            "fr" => "French",
            "fy" => "Western Frisian",
            "ga" => "Irish",
            "gd" => "Scottish Gaelic",
            "gl" => "Galician",
            "gn" => "Guarani",
            "gu" => "Gujarati",
            "gv" => "Manx",
            "ha" => "Hausa",
            "he" => "Hebrew",
            "hi" => "Hindi",
            "ho" => "Hiri Motu",
            "hr" => "Croatian",
            "ht" => "Haitian",
            "hu" => "Hungarian",
            "hy" => "Armenian",
            "hz" => "Herero",
            "ia" => "Interlingua (International Auxiliary Language Association)",
            "id" => "Indonesian",
            "ie" => "Interlingue",
            "ig" => "Igbo",
            "ii" => "Sichuan Yi",
            "ik" => "Inupiaq",
            "io" => "Ido",
            "is" => "Icelandic",
            "it" => "Italian",
            "iu" => "Inuktitut",
            "ja" => "Japanese",
            "jv" => "Javanese",
            "ka" => "Georgian",
            "kg" => "Kongo",
            "ki" => "Kikuyu",
            "kj" => "Kwanyama",
            "kk" => "Kazakh",
            "kl" => "Kalaallisut",
            "km" => "Khmer",
            "kn" => "Kannada",
            "ko" => "Korean",
            "kr" => "Kanuri",
            "ks" => "Kashmiri",
            "ku" => "Kurdish",
            "kv" => "Komi",
            "kw" => "Cornish",
            "ky" => "Kirghiz",
            "la" => "Latin",
            "lb" => "Luxembourgish",
            "lg" => "Ganda",
            "li" => "Limburgish",
            "ln" => "Lingala",
            "lo" => "Lao",
            "lt" => "Lithuanian",
            "lu" => "Luba-Katanga",
            "lv" => "Latvian",
            "mg" => "Malagasy",
            "mh" => "Marshallese",
            "mi" => "Maori",
            "mk" => "Macedonian",
            "ml" => "Malayalam",
            "mn" => "Mongolian",
            "mr" => "Marathi",
            "ms" => "Malay",
            "mt" => "Maltese",
            "my" => "Burmese",
            "na" => "Nauru",
            "nb" => "Norwegian Bokmal",
            "nd" => "North Ndebele",
            "ne" => "Nepali",
            "ng" => "Ndonga",
            "nl" => "Dutch",
            "nn" => "Norwegian Nynorsk",
            "no" => "Norwegian",
            "nr" => "South Ndebele",
            "nv" => "Navajo",
            "ny" => "Chichewa",
            "oc" => "Occitan",
            "oj" => "Ojibwa",
            "om" => "Oromo",
            "or" => "Oriya",
            "os" => "Ossetian",
            "pa" => "Panjabi",
            "pi" => "Pali",
            "pl" => "Polish",
            "ps" => "Pashto",
            "pt" => "Portuguese",
            "qu" => "Quechua",
            "rm" => "Raeto-Romance",
            "rn" => "Kirundi",
            "ro" => "Romanian",
            "ru" => "Russian",
            "rw" => "Kinyarwanda",
            "sa" => "Sanskrit",
            "sc" => "Sardinian",
            "sd" => "Sindhi",
            "se" => "Northern Sami",
            "sg" => "Sango",
            "si" => "Sinhala",
            "sk" => "Slovak",
            "sl" => "Slovenian",
            "sm" => "Samoan",
            "sn" => "Shona",
            "so" => "Somali",
            "sq" => "Albanian",
            "sr" => "Serbian",
            "ss" => "Swati",
            "st" => "Southern Sotho",
            "su" => "Sundanese",
            "sv" => "Swedish",
            "sw" => "Swahili",
            "ta" => "Tamil",
            "te" => "Telugu",
            "tg" => "Tajik",
            "th" => "Thai",
            "ti" => "Tigrinya",
            "tk" => "Turkmen",
            "tl" => "Tagalog",
            "tn" => "Tswana",
            "to" => "Tonga",
            "tr" => "Turkish",
            "ts" => "Tsonga",
            "tt" => "Tatar",
            "tw" => "Twi",
            "ty" => "Tahitian",
            "ug" => "Uighur",
            "uk" => "Ukrainian",
            "ur" => "Urdu",
            "uz" => "Uzbek",
            "ve" => "Venda",
            "vi" => "Vietnamese",
            "vo" => "Volapuk",
            "wa" => "Walloon",
            "wo" => "Wolof",
            "xh" => "Xhosa",
            "yi" => "Yiddish",
            "yo" => "Yoruba",
            "za" => "Zhuang",
            "zh" => "Chinese",
            "zu" => "Zulu",
        ];
        return $languageCodes;
    }

    /**
     * @return string
     */
    public function getSelectedLanguage()
    {
        return $this->_selectedLanguage;
    }
}
