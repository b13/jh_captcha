<?php

declare(strict_types=1);

namespace Haffner\JhCaptcha\Validation\Validator;

use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator;

abstract class AbstractCaptchaValidator extends AbstractValidator
{
    /** @var bool */
    protected $acceptsEmptyValues = false;
    protected array $settings;

    public function __construct(
        protected ConfigurationManagerInterface $configurationManager
    ) {
        $this->settings = $this->configurationManager->getConfiguration(
            ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS,
            'JhCaptcha'
        );
    }

    protected function addError(string $message, int $code, array $arguments = [], string $title = ''): void
    {
        parent::addError($message, $code);
    }
}
