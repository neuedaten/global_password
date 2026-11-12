<?php

declare(strict_types=1);

namespace Neuedaten\GlobalPassword\EventListener;

use Neuedaten\GlobalPassword\Entity\GlobalPasswordConfiguration;
use Neuedaten\GlobalPassword\Middleware\CheckPassword;
use SFC\Staticfilecache\Event\CacheRuleEventInterface;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Stop EXT:staticfilecache caching pages while the password is active
 *
 * Static files are served by the web server before TYPO3 runs, so a page
 * cached by a visitor who entered the password would be shown to everyone
 */
final class StaticFileCacheListener
{
    public function __invoke(CacheRuleEventInterface $event): void
    {
        $site = $event->getRequest()->getAttribute('site');

        // Match the conditions CheckPassword uses to decide whether to prompt
        if (
            !$site instanceof Site
            || !array_key_exists(CheckPassword::ENV_PASSWORD_FIELD, $_ENV)
            || !GeneralUtility::makeInstance(GlobalPasswordConfiguration::class, $site)->isPasswordProtected()
        ) {
            return;
        }

        $event->addExplanation(__CLASS__, 'Site is password protected');
        $event->setSkipProcessing(true);
    }
}
