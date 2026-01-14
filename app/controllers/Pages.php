<?php
/**
 * Pages Controller
 * 
 * Handles public pages like home, about, etc.
 */

class Pages extends Controller {
    /**
     * Homepage
     * 
     * @return void
     */
    public function index() {
        $data = [
            'title' => 'Welcome to E-Learning Platform',
            'description' => 'Learn new skills with our comprehensive online courses'
        ];
        
        $this->render('index', $data);
    }

    /**
     * About page
     * 
     * @return void
     */
    public function about() {
        $data = [
            'title' => 'About Us',
            'description' => 'Learn more about our e-learning platform'
        ];
        
        $this->render('about', $data);
    }
}

