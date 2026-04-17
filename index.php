<?php

$uri = $_SERVER['REQUEST_URI'];

if (preg_match('/\.php(\?|$)/', $uri)) {
    $html = preg_replace('/\.php(\?|$)/', '.html$1', $uri);
    header("Location: $html", true, 302);
    exit;
}