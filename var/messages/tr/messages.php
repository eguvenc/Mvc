<?php

# Error & Http response messages
# 
return [

    // -------------------------------------------------------------------
    // Http Errors
    // -------------------------------------------------------------------
    //
    400 => 'Geçersiz İstek',
    401 => 'Yetkisiz',
    403 => 'Yasak',
    404 => 'Sayfa Bulunamadı',
    405 => 'Bu Metot Desteklenmiyor',
    500 => 'İç Sunucu Hatası',
    502 => 'Kötü Ağ Geçidi',
    503 => 'Hizmet Servis Dışı',
    504 => 'Ağ Geçidi Zaman Aşımı',
    505 => 'HTTP Sürümü desteklenmiyor',

    // -------------------------------------------------------------------
    // Application Errors
    // -------------------------------------------------------------------
    //
    'An error was encountered' => 'Bilinmeyen bir hata oluştu',
    'The page you are looking for could not be found' => 'Aradığınız sayfa bulunamadı',
    'Application Error' => 'Uygulama Hatası',
];