<?php

declare(strict_types=1);

class HomeController extends Controller
{
    public function index(): void
    {
        // echo $this->language->translate('nav.home');
        // exit;
        $carModel = $this->model('Car');
        $cars = $carModel->getAllWithOwner(HOMEPAGE_CARS_LIMIT);

        $this->view('home/index', [
//            'title' => 'Home',
            'title' => t('nav.home'),
            'cars' => $cars
        ]);
    }
}
