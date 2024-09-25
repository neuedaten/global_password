<?php
declare(strict_types=1);

namespace Neuedaten\GlobalPassword\Middleware;

use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Http\HtmlResponse;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Utility\PathUtility;
use TYPO3Fluid\Fluid\View\TemplateView;
use Psr\Http\Server\MiddlewareInterface;
use TYPO3\CMS\Core\Http\RedirectResponse;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TYPO3\CMS\Frontend\Controller\ErrorController;
use TYPO3\CMS\Frontend\Page\PageAccessFailureReasons;
use TYPO3\CMS\Core\Configuration\Loader\YamlFileLoader;
use Neuedaten\GlobalPassword\Entity\GlobalPasswordConfiguration;

class CheckPassword implements MiddlewareInterface
{
    const COOKIE_NAME = 'TYPO3_GLOBAL_PASSWORD';
    const ENV_PASSWORD_FIELD = 'TYPO3__GLOBAL_PASSWORD';
    const ENV_CONFIG_FIELD = 'TYPO3__GLOBAL_PASSWORD_CONFIG_FILE';

    protected $config
        = [
            'templatePathAndFilename' => 'EXT:global_password/Resources/Private/Templates/Password/Login.html',
            'templateRootPaths' => [0 => 'EXT:global_password/Resources/Private/Templates/'],
            'partialRootPaths' => [0 => 'EXT:global_password/Resources/Private/Partials/'],
            'layoutRootPaths' => [0 => 'EXT:global_password/Resources/Private/Layouts/'],
            'cssPathAndFilename' => 'EXT:global_password/Resources/Public/CSS/main.css',
            'texts' => [
                'title' => 'Login',
                'htmlAbove' => '',
                'htmlBelow' => '',
                'passwordPlaceholder' => 'Password',
                'rememberMe' => 'remember me',
                'login' => 'Login'
            ]
        ];

    protected $objectManager = null;

    private function responseToMiddleware(
        ServerRequestInterface &$request,
        RequestHandlerInterface &$handler
    ) {
        /** @var \Psr\Http\Message\ResponseInterface $response */
        $response = $handler->handle($request);
        return $response;
    }

    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {

        $site = $request->getAttribute('site');

        if (!$site instanceof Site) {
            return GeneralUtility::makeInstance(ErrorController::class)->pageNotFoundAction(
                $request,
                'No site configuration found.',
                ['code' => PageAccessFailureReasons::PAGE_NOT_FOUND]
            );
        }

        $globalPasswordConfiguration = GeneralUtility::makeInstance(GlobalPasswordConfiguration::class, $site);

        if(
            (!array_key_exists(self::ENV_PASSWORD_FIELD, $_ENV)) ||
            !$globalPasswordConfiguration->isPasswordProtected()
        ) {
            return $this->responseToMiddleware($request, $handler);
        }

        /** Logout: */
        if (key_exists('global-password-logout', $request->getQueryParams())
            && $request->getQueryParams()['global-password-logout'] == '1'
        ) {
            $this->removePasswordCookie();
            return new RedirectResponse('/');
        }

        /** @var \TYPO3\CMS\Extbase\Object\ObjectManager; objectManager */
        $this->objectManager
            = GeneralUtility::makeInstance(ObjectManager::class);

        $this->readConfigFile();

        $configPassword = $_ENV[self::ENV_PASSWORD_FIELD];

        $templateVariables = [];

        if (is_array($request->getParsedBody()) && isset($request->getParsedBody()['global-password-submit'])) {
            $formPassword = $request->getParsedBody()['password'] ?? '';
            if ($formPassword == $configPassword) {
                $stay = $request->getParsedBody()['password'] ? true : false;
                $this->setPasswordToCookie(hash('sha256', $configPassword),
                    $stay);
                return $this->responseToMiddleware($request, $handler);
            } else {
                $templateVariables['wrongPassword'] = true;
            }
        }

        $cookiePassword = $this->getPasswordFromCookie();

        if (isset($cookiePassword)
            && $cookiePassword == hash('sha256', $configPassword)
        ) {
            return $this->responseToMiddleware($request, $handler);
        }

        $templateVariables['texts'] = $this->config['texts'];
        $templateVariables['cssPathAndFilename'] = PathUtility::getAbsoluteWebPath(
            GeneralUtility::getFileAbsFileName($this->config['cssPathAndFilename'])
        );

        /** @var \TYPO3\CMS\Fluid\View\TemplateView $view */
        $view = $this->initializeStandaloneView($templateVariables);
        return new HtmlResponse($view->render());
    }

    /**
     * @param array|null $variables
     *
     * @return \TYPO3Fluid\Fluid\View\TemplateView
     */
    protected function initializeStandaloneView(array $variables = null
    ): TemplateView {
        $view = new TemplateView();
        $paths = $view->getTemplatePaths();

        if (isset($this->config['templatePathAndFilename'])
        ) {
            $templatePathAndFilename = GeneralUtility::getFileAbsFileName($this->config['templatePathAndFilename']);
            $paths->setTemplatePathAndFilename($templatePathAndFilename);
        }

        if (isset($this->config['layoutRootPaths'])
            && is_array($this->config['layoutRootPaths'])
        ) {
            array_walk(
                $this->config['layoutRootPaths'],
                function(&$path) {
                    $path = GeneralUtility::getFileAbsFileName($path);
                }
            );
            $paths->setLayoutRootPaths($this->config['layoutRootPaths']);
        }

        if (isset($this->config['templateRootPaths'])
            && is_array($this->config['templateRootPaths'])
        ) {
            array_walk(
                $this->config['templateRootPaths'],
                function (&$path) {
                    $path = GeneralUtility::getFileAbsFileName($path);
                }
            );
            $paths->setTemplateRootPaths($this->config['templateRootPaths']);
        }

        if (isset($this->config['partialRootPaths'])
            && is_array($this->config['partialRootPaths'])
        ) {
            array_walk(
                $this->config['partialRootPaths'],
                function (&$path) {
                    $path = GeneralUtility::getFileAbsFileName($path);
                }
            );
            $paths->setPartialRootPaths($this->config['partialRootPaths']);
        }

        $renderingContext = $view->getRenderingContext();
        $renderingContext->setControllerName('Password');
        $renderingContext->setControllerAction('Login');
        $renderingContext->setTemplatePaths($paths);
        $view->setRenderingContext($renderingContext);

        if (isset($variables)) {
            $view->assignMultiple($variables);
        }

        return $view;
    }

    /**
     * @return mixed
     */
    protected function getPasswordFromCookie()
    {
        if (array_key_exists(self::COOKIE_NAME, $_COOKIE)) {
            return $_COOKIE[self::COOKIE_NAME];
        }
        return null;
    }

    protected function setPasswordToCookie($password, $stay = false)
    {
        if ($stay == true) {
            setcookie(self::COOKIE_NAME, $password, time() + 60 * 60 * 24 * 30,
                '/');
        } else {
            setcookie(self::COOKIE_NAME, $password, null, '/');
        }
    }

    protected function removePasswordCookie()
    {
        if (isset($_COOKIE[self::COOKIE_NAME])) {
            unset($_COOKIE[self::COOKIE_NAME]);
            setcookie(self::COOKIE_NAME, '', time() - 3600, '/');
        }
    }

    protected function readConfigFile()
    {
        if (!array_key_exists(self::ENV_CONFIG_FIELD, $_ENV)) {
            return;
        }
        $filename = $_ENV[self::ENV_CONFIG_FIELD];
        /** @var YamlFileLoader $yamlFileLoader */
        $yamlFileLoader = $this->objectManager->get(YamlFileLoader::class);
        /** @var array $yamlConfig */
        $yamlConfig
            = $yamlFileLoader->load(Environment::getConfigPath()
            . '/' . $filename);
        $this->config = array_replace_recursive($this->config, $yamlConfig);
    }
}
