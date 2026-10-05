<?php

declare(strict_types=1);

use Neuedaten\GlobalPassword\EventListener\StaticFileCacheListener;
use SFC\Staticfilecache\Event\CacheRuleEvent;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    // EXT:staticfilecache is optional, only register the listener when it is installed
    if (!class_exists(CacheRuleEvent::class)) {
        return;
    }

    // Run after the "force static" listener so it cannot re-enable caching.
    // It was renamed in EXT:staticfilecache 15, so both identifiers are listed
    $containerConfigurator->services()
        ->set(StaticFileCacheListener::class)
        ->tag('event.listener', [
            'identifier' => 'global-password/staticfilecache',
            'event' => CacheRuleEvent::class,
            'after' => 'SfcCacheRuleForceStaticCacheListener, ForceStaticCacheListener',
        ])
    ;
};
