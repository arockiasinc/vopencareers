<?php
declare(strict_types=1);

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/container/homepage-cms.php';

header('Content-Type: application/json; charset=UTF-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Only POST requests are allowed.',
    ]);
    exit;
}

if (!isAdminAuthenticated()) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Your admin session has expired. Please sign in again.',
    ]);
    exit;
}

$sectionKey = trim((string) ($_POST['section_key'] ?? ''));
$definitions = homepageCmsSectionDefinitions();

if ($sectionKey === '' || !isset($definitions[$sectionKey])) {
    http_response_code(422);
    echo json_encode([
        'success' => false,
        'message' => 'Unknown homepage section.',
    ]);
    exit;
}

try {
    $sections = homepageCmsLoadSections();
    $currentSection = $sections[$sectionKey] ?? ($definitions[$sectionKey]['default_data'] ?? []);
    $nextSection = homepageCmsSaveSection($sectionKey, $_POST, $_FILES, $currentSection);

    echo json_encode([
        'success' => true,
        'message' => 'Section updated successfully.',
        'sectionKey' => $sectionKey,
        'anchor' => (string) ($definitions[$sectionKey]['anchor'] ?? 'top'),
        'section' => $nextSection,
    ]);
} catch (Throwable $exception) {
    http_response_code(422);
    echo json_encode([
        'success' => false,
        'message' => $exception->getMessage(),
    ]);
}
