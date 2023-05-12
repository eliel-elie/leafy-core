<?php

namespace LeafyTech\Core;

class View
{
    public string $title = '';

    public function renderView($view, array $params, $css = [], $script = [])
    {
        $layoutName = Application::$app->layout;
        if (Application::$app->controller) {
            $layoutName = Application::$app->controller->layout;
        }

        foreach ($css as $key => $value) $$key = $value;

        foreach ($script as $key => $value) $$key = $value;

        $viewContent = $this->renderViewOnly($view, $params);
        ob_start();
        include_once Application::$ROOT_DIR."/templates/$layoutName.php";
        $layoutContent = ob_get_clean();
        return str_replace('{{content}}', $viewContent, $layoutContent);
    }

    public function renderViewOnly($view, array $params)
    {
        foreach ($params as $key => $value) {
            $$key = $value;
        }
        ob_start();
        include_once Application::$ROOT_DIR."/views/$view.php";
        return ob_get_clean();
    }

    public function renderWithoutTemplate($view, string $path = 'views', array $params = [])
    {
        foreach ($params as $key => $value) {
            $$key = $value;
        }
        ob_start();
        include_once "$path/$view.php";
        return ob_get_clean();
    }

}