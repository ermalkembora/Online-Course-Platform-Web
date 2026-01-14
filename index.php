<?php
/**
 * Root Entry Point Redirect
 * 
 * Redirects requests from project root to the public directory.
 * This allows accessing the app via /e-learning-platform/ instead of /e-learning-platform/public/
 */

// Redirect to public directory
header('Location: public/');
exit;

