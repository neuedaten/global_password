<?php
declare(strict_types = 1);
namespace Neuedaten\GlobalPassword\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TYPO3\CMS\Core\Configuration\Loader\YamlFileLoader;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Fluid\View\StandaloneView;

class CheckPassword implements MiddlewareInterface
{
    const COOKIE_NAME = 'TYPO3_GLOBAL_PASSWORD';
    const ENV_PASSWORD_FIELD = 'TYPO3__GLOBAL_PASSWORD';
    const ENV_CONFIG_FIELD = 'TYPO3__GLOBAL_PASSWORD_CONFIG_FILE';

    protected $config = [
        'templatePathAndFilename' => 'EXT:global_password/Resources/Private/Templates/Password/Login.html',
        'templateRootPaths' => [0 => 'EXT:global_password/Resources/Private/Templates/'],
        'partialRootPaths' => [0 => 'EXT:global_password/Resources/Private/Partials/'],
        'layoutRootPaths' => [0 => 'EXT:global_password/Resources/Private/Layouts/'],
        'texts' => [
            'title' => 'Login',
            'htmlAbove' => '',
            'htmlBelow' => '',
            'passwordPlaceholder' => 'Passwort',
            'rememberMe' => 'angemeldet bleiben',
            'login' => 'Login'
        ]
    ];

    protected $objectManager = null;

    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {

        /** @var \TYPO3\CMS\Core\Http\Response  $response */
        $response = $handler->handle($request);

        if (!array_key_exists(self::ENV_PASSWORD_FIELD, $_ENV)) {
            return $response;
        }

        /** @var \TYPO3\CMS\Extbase\Object\ObjectManager; objectManager */
        $this->objectManager = GeneralUtility::makeInstance(ObjectManager::class);

        $this->readConfigFile();
        
        $configPassword = $_ENV[self::ENV_PASSWORD_FIELD];

        $templateVariables = [];

        if (GeneralUtility::_POST('global-password-submit')) {
            $formPassword = GeneralUtility::_GP('password');
            if ($formPassword == $configPassword) {
                $stay = GeneralUtility::_GP('stay') ? true : false;
                $this->setPasswordToCookie(hash('sha256', $configPassword), $stay);
                return $response;
            } else {
                $templateVariables['wrongPassword'] = true;
            }
        }
        
        $cookiePassword = $this->getPasswordFromCookie();

        if (isset($cookiePassword) && $cookiePassword == hash('sha256', $configPassword)) {
            return $response;
        }

        $templateVariables['texts'] = $this->config['texts'];

        /** @var \TYPO3\CMS\Fluid\View\StandaloneView $view */
        $view = $this->initializeStandaloneView($templateVariables);
        die($view->render());
    }

    /**
     * @param array|null $variables
     *
     * @return StandaloneView
     */
    protected function initializeStandaloneView(Array $variables = null): StandaloneView
    {
        /** @var \TYPO3\CMS\Fluid\View\StandaloneView $standaloneView */
        $standaloneView = $this->objectManager->get(StandaloneView::class);
        $standaloneView->setFormat('html');

        $renderingContext = $standaloneView->getRenderingContext();
        $renderingContext->setControllerName('Password');
        $renderingContext->setControllerAction('Login');
        $standaloneView->setRenderingContext($renderingContext);

        if (isset($this->config['layoutRootPaths']) && is_array($this->config['layoutRootPaths'])) {
            $standaloneView->setLayoutRootPaths($this->config['layoutRootPaths']);
        }

        if (isset($this->config['templatePathAndFilename'])) {
            $standaloneView->setTemplatePathAndFilename($this->config['templatePathAndFilename']);
        }

        if (isset($this->config['templateRootPaths']) && is_array($this->config['templateRootPaths'])) {
            $standaloneView->setTemplateRootPaths($this->config['templateRootPaths']);
        }

        if (isset($this->config['partialRootPaths']) && is_array($this->config['partialRootPaths'])) {
            $standaloneView->setPartialRootPaths($this->config['partialRootPaths']);
        }

        if (isset($variables)) {
            $standaloneView->assignMultiple($variables);
        }

        return $standaloneView;
    }

    /**
     * @return mixed
     */
    protected function getPasswordFromCookie() {
        if (array_key_exists(self::COOKIE_NAME, $_COOKIE)) {
            return $_COOKIE[self::COOKIE_NAME];
        }
        return null;
    }

    protected function setPasswordToCookie($password, $stay = false) {
        if ($stay == true) {
            setcookie(self::COOKIE_NAME, $password, time() + 60 * 60 * 24 * 30);
        } else {
            setcookie(self::COOKIE_NAME, $password);
        }
    }

    protected function readConfigFile() {
        if (!array_key_exists(self::ENV_CONFIG_FIELD, $_ENV)) return;
        $filename = $_ENV[self::ENV_CONFIG_FIELD];
        /** @var YamlFileLoader $yamlFileLoader */
        $yamlFileLoader = $this->objectManager->get(YamlFileLoader::class);
        /** @var array $yamlConfig */
        $yamlConfig = $yamlFileLoader->load(\TYPO3\CMS\Core\Core\Environment::getConfigPath() . '/' . $filename);
        $this->config = array_replace_recursive($this->config, $yamlConfig);
    }


}