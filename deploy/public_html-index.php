<?php

/*
|--------------------------------------------------------------------------
| cPanel primary-domain shim
|--------------------------------------------------------------------------
| pestone.co.ke's document root is locked to ~/public_html. The Laravel app
| lives in ~/pestone-shop. This file is copied to ~/public_html/index.php by
| .cpanel.yml and simply hands the request to the app's real front controller.
| Static assets (build/, brand/, js|css|fonts/filament/) are rsynced alongside
| it so Apache serves them directly.
*/

require __DIR__.'/../pestone-shop/public/index.php';
