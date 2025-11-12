<?php
require_once 'BaseController.php';

class HomeController extends BaseController {

    /**
     * Página inicial - redireciona para login ou dashboard
     */
    public function index() {
        if (isLoggedIn()) {
            redirect('/dashboard');
        } else {
            redirect('/login');
        }
    }
}
?>