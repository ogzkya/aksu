<?php
// admin/test-paths.php
require_once '../includes/init.php';

$auth = new Auth();
$auth->requireLogin();

// Yeni URL sistemi test
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
$domain = $_SERVER['HTTP_HOST'];

$scriptName = $_SERVER['SCRIPT_NAME'];
$adminPos = strpos($scriptName, '/admin/');
if ($adminPos !== false) {
    $siteRoot = substr($scriptName, 0, $adminPos + 1);
} else {
    $siteRoot = '/';
}

$baseUrl = $protocol . $domain . $siteRoot;
$adminUrl = $baseUrl . 'admin/';

echo "<h3>Yeni URL Sistemi Test Sonuçları:</h3>";
echo "<p>Protocol: " . htmlspecialchars($protocol) . "</p>";
echo "<p>Domain: " . htmlspecialchars($domain) . "</p>";
echo "<p>Script Name: " . htmlspecialchars($scriptName) . "</p>";
echo "<p>Admin Position: " . $adminPos . "</p>";
echo "<p>Site Root: " . htmlspecialchars($siteRoot) . "</p>";
echo "<p>Base URL: " . htmlspecialchars($baseUrl) . "</p>";
echo "<p>Admin URL: " . htmlspecialchars($adminUrl) . "</p>";

echo "<h4>Test Linkleri:</h4>";
echo "<p>Admin CSS: <a href='" . $adminUrl . "assets/css/admin-clean.css' target='_blank'>CSS Test</a></p>";
echo "<p>Dashboard: <a href='" . $adminUrl . "index.php'>Dashboard</a></p>";
echo "<p>Listings: <a href='" . $adminUrl . "listings/index.php'>Listings</a></p>";
echo "<p>Site Ana Sayfa: <a href='" . $baseUrl . "index.php' target='_blank'>Site Ana Sayfa</a></p>";

echo "<h4>CSS Dosyası Var mı?</h4>";
$cssPath = $_SERVER['DOCUMENT_ROOT'] . $siteRoot . 'admin/assets/css/admin-clean.css';
echo "<p>CSS Path: " . htmlspecialchars($cssPath) . "</p>";
echo "<p>CSS Exists: " . (file_exists($cssPath) ? 'YES' : 'NO') . "</p>";
?>
