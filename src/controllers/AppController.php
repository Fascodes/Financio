<?php


class AppController { 


    protected function isGet(): bool
    {
        return $_SERVER['REQUEST_METHOD'] === 'GET';
    }

    protected function isPost(): bool
    {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }

    /**
     * Pobierz aktywną grupę z sesji lub z parametru
     */
    protected function getActiveGroupId($paramGroupId = null) {
        if (!empty($paramGroupId)) {
            return (int)$paramGroupId;
        }
        return isset($_SESSION['active_group_id']) ? (int)$_SESSION['active_group_id'] : null;
    }
    
    protected function render(string $template = null, array $variables = [])
    {
        $templatePath = 'public/views/'. $template.'.html';
        $templatePath404 = 'public/views/404.html';
        $output = "";
                 
        if(file_exists($templatePath)){
            extract($variables);
            
            ob_start();
            include $templatePath;
            $output = ob_get_clean();
        } else {
            ob_start();
            include $templatePath404;
            $output = ob_get_clean();
        }
        echo $output;
    }
    
}