<?php

declare(strict_types=1);

namespace Haffner\JhCaptcha\Validation\ErrorCheck;

use Haffner\JhCaptcha\Validation\Validator\ReCaptchaValidator;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class ReCaptcha extends \Typoheads\Formhandler\Validator\ErrorCheck\AbstractErrorCheck
{
    public function check(): string
    {
        $checkFailed = '';

        if (isset($this->gp[$this->formFieldName])) {
            $reCaptchaValidator = GeneralUtility::makeInstance(ReCaptchaValidator::class);

            // validate the captcha
            $result = $reCaptchaValidator->validate($this->gp[$this->formFieldName]);

            // check errors
            if ($result->getErrors()) {
                $checkFailed = $this->getCheckFailed();
            }
        }

        return $checkFailed;
    }
}
