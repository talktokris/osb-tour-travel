<?php
declare(strict_types=1);

// Legacy TCPDF scripts expect this include path.
// Route them to the new app database connection.
require_once dirname(__DIR__, 2) . '/config.php';
