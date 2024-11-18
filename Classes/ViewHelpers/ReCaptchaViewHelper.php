<?php

declare(strict_types=1);

namespace Haffner\JhCaptcha\ViewHelpers;

use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

class ReCaptchaViewHelper extends AbstractViewHelper
{
    /**
     * @var bool
     */
    protected $escapeOutput = false;

    public function __construct(
        protected ConfigurationManagerInterface $configurationManager
    ) {
    }

    public function initializeArguments(): void
    {
        $this->registerArgument('uid', 'String', 'reCaptcha uid', false);
        $this->registerArgument('type', 'String', 'form type', false);
    }

    public function render(): string
    {
        $settings = $this->configurationManager->getConfiguration(
            ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS,
            'JhCaptcha'
        );

        $captchaResponseId = 'captchaResponse';
        if ($this->arguments['uid']) {
            $captchaResponseId = $captchaResponseId . '-' . $this->arguments['uid'];
        }

        if ($settings['reCaptcha']['version'] == 2) {
            // render v2
            if ($settings['reCaptcha']['v2']['siteKey']) {
                return $this->renderV2($captchaResponseId, $settings);
            } else {
                return LocalizationUtility::translate('setApiKey', 'jh_captcha');
            }
        } else {
            // render v3
            if ($settings['reCaptcha']['v3']['siteKey']) {
                return $this->renderV3($captchaResponseId, $settings);
            } else {
                return LocalizationUtility::translate('setApiKey', 'jh_captcha');
            }
        }
    }

    private function renderV2($captchaResponseId, $settings): string
    {
        $siteKey = htmlspecialchars($settings['reCaptcha']['v2']['siteKey']);
        $theme = htmlspecialchars($settings['reCaptcha']['v2']['theme']);
        $lang = htmlspecialchars($settings['reCaptcha']['v2']['lang']);
        $size = htmlspecialchars($settings['reCaptcha']['v2']['size']);
        $callBack = '';

        $uid = (string)$this->arguments['uid'];

        $reCaptcha = '<div id="recaptcha' . $uid . '"></div>';
        $renderReCaptcha = '<script type="text/javascript">var apiCallback' . str_replace("-", "", $uid) . ' = function() { reCaptchaWidget' . str_replace("-", "", $uid) . ' = grecaptcha.render("recaptcha' . $uid . '", { "sitekey" : "' . $siteKey .'", "callback" : "captchaCallback' . str_replace("-", "", $uid) .'", "theme" : "' . $theme . '", "size" : "' . $size . '" }); }</script>';
        $reCaptchaApi = '<script src="https://www.google.com/recaptcha/api.js?onload=apiCallback' . str_replace("-", "", $uid) . '&hl=' . $lang . '&render=explicit" async defer></script>';
        if (!$this->isPowermail()) {
            $callBack = '<script type="text/javascript">var captchaCallback' . str_replace("-", "", $uid) . ' = function() { document.getElementById("' . $captchaResponseId . '").value = grecaptcha.getResponse(reCaptchaWidget' . str_replace("-", "", $uid) . ') }</script>';
        }

        return $reCaptcha . $callBack . $renderReCaptcha . $reCaptchaApi;
    }

    private function renderV3($captchaResponseId, $settings): string
    {
        $callBackFunctionName = 'onLoad' .
            $this->arguments['type'] . str_replace("-", "", $this->arguments['uid']);

        $captchaResponseField = '';
        if ($this->isPowermail()) {
            $captchaResponseField = '<input type="hidden" id="' . $captchaResponseId . '" name="g-recaptcha-response">';
        }

        $callBack =
            '<script type="text/javascript">'.
                'var ' . $callBackFunctionName . ' = function() {'.
                    'grecaptcha.execute('.
                        '"' . htmlspecialchars($settings['reCaptcha']['v3']['siteKey']) . '",'.
                        '{action: "' . htmlspecialchars($settings['reCaptcha']['v3']['action']) . '"})'.
                        '.then(function(token) {'.
                            'document.getElementById("' . $captchaResponseId . '").value = token;'.
                        '}'.
                    ');'.
                '};'.
                'setInterval(' . $callBackFunctionName . ', 100000);'.
            '</script>';
        $api =
            '<script src="https://www.google.com/recaptcha/api.js?'.
                'render=' . htmlspecialchars($settings['reCaptcha']['v3']['siteKey']) . '&'.
                'onload=' . $callBackFunctionName . '"></script>';

        return $captchaResponseField . $callBack . $api;
    }

    private function isPowermail(): bool
    {
        return $this->arguments['type'] === "powermail";
    }
}
