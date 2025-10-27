<?php

declare(strict_types=1);

use App\Support\Config;
use App\Support\Database;

if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(__DIR__));
}

require_once __DIR__ . '/support/helpers.php';
require_once __DIR__ . '/support/Config.php';
require_once __DIR__ . '/support/Database.php';
require_once __DIR__ . '/support/Response.php';
require_once __DIR__ . '/support/View.php';
require_once __DIR__ . '/support/Csrf.php';
require_once __DIR__ . '/support/Auth.php';

require_once __DIR__ . '/Models/Property.php';
require_once __DIR__ . '/Models/Amenity.php';
require_once __DIR__ . '/Models/Media.php';
require_once __DIR__ . '/Models/Portal.php';
require_once __DIR__ . '/Models/Setting.php';
require_once __DIR__ . '/Models/Repositories/PropertyRepository.php';
require_once __DIR__ . '/Models/Repositories/AmenityRepository.php';
require_once __DIR__ . '/Models/Repositories/MediaRepository.php';
require_once __DIR__ . '/Models/Repositories/PortalRepository.php';
require_once __DIR__ . '/Models/Repositories/SettingRepository.php';

require_once __DIR__ . '/Services/FeedService.php';
require_once __DIR__ . '/Services/ImportService.php';
require_once __DIR__ . '/Services/UpdateService.php';

require_once __DIR__ . '/Http/Router.php';
require_once __DIR__ . '/Http/Controllers/DashboardController.php';
require_once __DIR__ . '/Http/Controllers/PropertyController.php';
require_once __DIR__ . '/Http/Controllers/FeedController.php';
require_once __DIR__ . '/Http/Controllers/AdminController.php';
require_once __DIR__ . '/Http/Controllers/AuthController.php';

Config::load(__DIR__ . '/../config.php');

Database::migrate();
