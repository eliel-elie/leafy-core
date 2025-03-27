<?php

namespace LeafyTech\Core\Traits;

trait AuthorizesRequests
{
    public function authorize(string $ability): void
    {
        $permissions = session()->get('user')->permissions ?? [];
        $allowed     = in_array($ability, $permissions) ?? false;

        if (! $allowed) {

            $viewPath = app()->basePath('views/errors/403.php');

            if (file_exists($viewPath)) {
                echo $this->render('errors/403');
            } else {

                http_response_code(403);

                ob_start();
                include_once resource_path('views/errors/default-403.php');
                $view = ob_get_clean();

                echo $view;

            }

            exit();
        }
    }
}