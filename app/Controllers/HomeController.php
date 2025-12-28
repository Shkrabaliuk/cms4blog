<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;

class HomeController extends Controller
{
    public function index(): void
    {
        $this->view->setLayout('main');
        
        echo $this->render('home/index', [
            'title' => 'Головна сторінка',
            'siteName' => getenv('APP_NAME') ?: 'CMS4Blog',
        ]);
    }

    public function about(): void
    {
        $this->view->setLayout('main');
        
        echo $this->render('home/about', [
            'title' => 'Про систему',
            'siteName' => getenv('APP_NAME') ?: 'CMS4Blog',
        ]);
    }
}
