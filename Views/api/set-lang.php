<?php

use App\Api\Response;

if (MULTILINGUAL) {

    if (isset($_POST['lang'])) {
        // Make a check if the lang is supported
        $supportdLangs = [];
        // Check the config/lang folder for supported languages
        $langFiles = scandir(ROOT . '/config/lang');
        foreach ($langFiles as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }
            $lang = pathinfo($file, PATHINFO_FILENAME);
            $supportdLangs[] = $lang;
        }
        if (!in_array($_POST['lang'], $supportdLangs)) {
            Response::output('Language not supported', 400);
        }
        $_SESSION['lang'] = $_POST['lang'];
    }
    Response::output([]);
}

Response::output('Multilingual is disabled', 400);
