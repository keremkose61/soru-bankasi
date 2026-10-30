<?php
// core/Language.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Varsayılan dil
if (!isset($_SESSION['lang'])) {
    $_SESSION['lang'] = 'tr'; 
}

// Dil değiştirme isteği
if (isset($_GET['lang']) && in_array($_GET['lang'], ['tr', 'en'])) {
    $_SESSION['lang'] = $_GET['lang'];
    
    // URL'den lang parametresini temizleyip aynı sayfaya yönlendir
    $current_url = $_SERVER['REQUEST_URI'];
    $redirect_url = preg_replace('/([&?])lang=[a-z]{2}(&|$)/', '$1', $current_url);
    $redirect_url = rtrim($redirect_url, '?&');
    if ($redirect_url != $current_url) {
        header("Location: $redirect_url");
        exit();
    }
}

// İlgili dil dosyasını yükle
$lang_file = __DIR__ . '/../lang/' . $_SESSION['lang'] . '.php';
if (file_exists($lang_file)) {
    global $site_lang;
    $site_lang = include $lang_file;
} else {
    global $site_lang;
    $site_lang = [];
}

/**
 * Çeviri fonksiyonu
 */
function __($key) {
    global $site_lang;
    if (isset($site_lang[$key])) {
        return $site_lang[$key];
    }
    return $key;
}
