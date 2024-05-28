<?php

namespace LeafyTech\Core;

use Illuminate\Events\Dispatcher;
use LeafyTech\Core\Helpers\Ldap;
use LeafyTech\Core\Helpers\RouteHelper;
use RuntimeException;

class Application
{
    public Config $config;
    public Router $router;
    public Request $request;
    public Response $response;

    public static Application $app;
    public static string $ROOT_DIR;
    public string $layout = 'layout';

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
        self::$app      = $this;
        self::$ROOT_DIR = $rootDir;
        $this->basePath = $rootDir;

        $this->config   = new Config($_ENV);

        $this->user     = null;
        $this->request  = new Request();
        $this->response = new Response();
        $this->router   = new Router($this->request, $this->response);
        $this->events   = new Dispatcher();
        $this->session  = new Session();
        $this->view     = new View();
        $this->capsule  = new Database($this->config);
        $this->ldap     = $this->ldapConnect();

    }

    public function boot(): Application
    {
        RouteHelper::includeRouteFiles(self::$ROOT_DIR . '/routes');
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

    public function eventSubscribe(array $subscribes)
    {
        foreach ($subscribes as $subscriber) {
            $this->events->subscribe($subscriber);
        }
    }

    public function ldapConnect()
    {
        if($this->config->app['auth'] == Ldap::TYPE) {
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

        $this->user = $user;

        self::$app->session->set('user', $user);

        if(self::$app->session->get('previews_url') !== null && $automaticallyRedirectPreviousUrl) {

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
        $protocol = filter_input(INPUT_SERVER, 'REQUEST_SCHEME') ;
        $host     = filter_input(INPUT_SERVER, 'HTTP_HOST') ;

        return "{$protocol}://{$host}";
    }

    public static function isGuest(): bool
    {
        self::$app->session->set('previews_url', self::$app->request->getUrl());

        return !self::$app->session->get('user');
    }

    public function storagePath(): string
    {
        return self::$ROOT_DIR.'/storage';
    }

    public function isDownForMaintenance(): bool
    {
        return file_exists($this->storagePath() .'/framework/down');
    }

    public function run(): void
    {
        if($this->isDownForMaintenance()) {
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

    public function getNamespace()
    {
        $composer = json_decode(file_get_contents(self::$ROOT_DIR.DIRECTORY_SEPARATOR.'composer.json'), true);

        foreach ((array) data_get($composer, 'autoload.psr-4') as $namespace => $path) {
            foreach ((array) $path as $pathChoice) {
                if (realpath(self::$ROOT_DIR.DIRECTORY_SEPARATOR.'app') === realpath(self::$ROOT_DIR.DIRECTORY_SEPARATOR.$pathChoice)) {
                    return $namespace;
                }
            }
        }

        throw new RuntimeException('Unable to detect application namespace.');
    }

    public function basePath($path = ''): string
    {
        return $this->basePath.($path != '' ? DIRECTORY_SEPARATOR.$path : '');
    }
}