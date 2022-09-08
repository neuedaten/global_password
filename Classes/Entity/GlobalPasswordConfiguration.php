<?php
namespace Neuedaten\GlobalPassword\Entity;

use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\ExpressionLanguage\Resolver;
use Symfony\Component\ExpressionLanguage\SyntaxError;

/**
 * GlobalPasswordConfiguration
 *
 * Parse the GlobalPassword Configuration in a site file
 */
class GlobalPasswordConfiguration implements SingletonInterface
{
    protected $config = [];

    /**
     * __construct
     *
     * @param Site $site
     */
    public function __construct(Site $site)
    {
        // Do we have config set?
        if (isset($site->getConfiguration()['globalPassword'])) {
            $config = $site->getAttribute('globalPassword');

            /**
             * If there are conditional variants separate them our and remove from config
             */
            if(isset($config['variants'])) {
                $variants = $config['variants'];
                unset($config['variants']);
            }

            // Merge the conditional config and global config
            $this->config = $this->applyEnvironmentConfiguration($config, $variants);
        }
    }

    /**
     * isPasswordProtected
     *
     * Is the site (with environmental overrides) password protected?
     *
     * @return bool
     */
    public function isPasswordProtected(): bool
    {
        return $this->config['enabled'] ?? false;
    }


    /**
     * applyEnvironmentConfiguration
     *
     * Apply environmental overrides to allow environment & site specific config
     *
     * This uses the same code found in \TYPO3\CMS\Core\Site\Entity\Site
     *
     * Here's an example of how to disable the password on dev
     * <code>
     * globalPassword:
     *  enabled: true
     *  variants:
     *    -
     *      enabled: false
     *      condition: 'applicationContext == "Development/Local"'
     * </code>
     *
     * @param array $config
     * @param array $variants
     */
    protected function applyEnvironmentConfiguration(array $config, array $variants = [])
    {
        if(count($variants)) {
            $expressionLanguageResolver = GeneralUtility::makeInstance(
                Resolver::class,
                'site',
                []
            );

            foreach ($variants as $variant) {
                try {
                    if ($expressionLanguageResolver->evaluate($variant['condition'])) {
                        unset($variant['condition']);
                        $config = array_merge($config, $variant);
                        break;
                    }
                } catch (SyntaxError $e) {
                    // silently fail and do not evaluate
                    // no logger here, as Site is currently cached and serialized
                }
            }
        }

        return $config;
    }
}
