<?php

namespace LeafyTech\Core;

use Illuminate\Events\Dispatcher;
use LeafyTech\Core\Exception\NotFoundException;
use LeafyTech\Core\Helpers\Ldap;
use LeafyTech\Core\Helpers\RouteHelper;
use LeafyTech\Core\Support\Language\Translator;
use RuntimeException;

class Application
{
    public Config $config;

    public Router $router;

    public Request $request;

    public Response $response;

    public Translator $translator;

    public static Application $app;

    public string $layout          = 'layout';

    public ?Controller $controller = null;

    public ?UserModel $user;

    public ?Ldap $ldap;

    public Dispatcher $events;

    public Session $session;

    public View $view;

    public Database $capsule;

    protected string $basePath;

    public function __construct($rootDir)
    {
        self::$app        = $this;
        $this->basePath   = $rootDir;

        $this->config     = new Config($_ENV);
        $this->user       = null;
        $this->request    = new Request();
        $this->response   = new Response();
        $this->router     = new Router($this->request, $this->response);
        $this->events     = new Dispatcher();
        $this->session    = new Session();
        $this->view       = new View();
        $this->capsule    = new Database($this->config);
        $this->ldap       = $this->ldapConnect();
        $this->translator = new Translator();
    }

    public function boot(): Application
    {
        RouteHelper::includeRouteFiles(app()->basePath('/routes'));

        $this->translator->setLocale($this->config->app['locale']);

        return $this;
    }

    public function addNewDatabaseConnection(string $name, array $values, $reloadConnections = false): Application
    {
        if (!empty($name) && !empty($values)) {
            $this->config->set('connections', [$name => $values]);
        }

        if ($reloadConnections) {
            $this->capsule = new Database($this->config);
        }

        return $this;
    }

    /**
     * Events and Listeners
     * @param array $events
     * @return void
     */
    public function eventRegister(array $events): void
    {
        foreach ($events as $event => $listeners) {
            foreach (array_unique($listeners) as $listener) {
                $this->events->listen($event, $listener);
            }
        }
    }

    public function eventSubscribe(array $subscribes): void
    {
        foreach ($subscribes as $subscriber) {
            $this->events->subscribe($subscriber);
        }
    }

    public function ldapConnect()
    {
        if ($this->config->app['auth'] == Ldap::TYPE) {
            return (new Ldap($this->config->ldap))->connect();
        }
    }

    public function login(UserModel $user, ?Ldap $ldap, bool $automaticallyRedirectPreviousUrl = true)
    {
        $user->userid     = $ldap->userId();
        $user->name       = $ldap->displayName();
        $user->department = $ldap->getDepartmentName();
        $user->title      = $ldap->getPersonalTitle();
        $user->email      = $ldap->getEmail();
        $user->avatar     = $user->getAvatar();
        $user->initials   = $user->getInitials();
        $user->groups     = $ldap->memberOf(null, $this->config->app['devPrefix'], $this->config->app['prefix']);

        $this->user       = $user;

        self::$app->session->set('user', $user);

        if (self::$app->session->get('previews_url') !== null && $automaticallyRedirectPreviousUrl) {
            $goTo = self::$app->session->get('previews_url');
            self::$app->session->remove('previews_url');
            self::$app->response->redirect($goTo);
        } else {
            return true;
        }
    }

    public function logout(): void
    {
        $this->user = null;
        self::$app->session->remove('user');
        session_destroy();
    }

    public function getHomeUri(): string
    {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $host     = filter_input(INPUT_SERVER, 'HTTP_HOST');

        $folder   = '';

        if (!empty(self::$app->config->app['folder'])) {
            $folder = '/' . self::$app->config->app['folder'];
        }

        return "{$protocol}://{$host}{$folder}";
    }

    public static function isGuest(): bool
    {
        self::$app->session->set('previews_url', self::$app->request->getUrl());

        return !self::$app->session->get('user');
    }

    public function storagePath(): string
    {
        return app()->basePath('storage');
    }

    public function isDownForMaintenance(): bool
    {
        return file_exists($this->storagePath() . '/framework/down');
    }

    public function run(): void
    {
        if ($this->isDownForMaintenance()) {
            echo $this->view->renderWithoutTemplate('/framework/maintenance', $this->storagePath());

            exit;
        }

        try {
            echo $this->router->resolve();
        } catch (\Exception $e) {
            echo $this->router->renderView('errors/_error', [
                'exception' => $e,
            ]);
        }
    }

    public function getNamespace(): int|string
    {
        if (!file_exists($this->basePath('composer.json'))) {
            throw new NotFoundException('Composer.json file not found');
        }

        $composer = json_decode(file_get_contents(app()->basePath('composer.json')), true);

        foreach ((array) data_get($composer, 'autoload.psr-4') as $namespace => $path) {
            foreach ((array) $path as $pathChoice) {
                if (realpath(app()->basePath('app')) === realpath(app()->basePath($pathChoice))) {
                    return $namespace;
                }
            }
        }

        throw new RuntimeException('Unable to detect application namespace.');
    }

    public function basePath($path = ''): string
    {
        return $this->basePath . ($path !== '' ? DIRECTORY_SEPARATOR . $path : '');
    }
}
