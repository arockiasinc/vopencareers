<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';

function homepageCmsSectionDefinitions(): array
{
    static $definitions = null;

    if (is_array($definitions)) {
        return $definitions;
    }

    $definitions = [
        'hero' => [
            'label' => 'Hero mission',
            'anchor' => 'top',
            'default_data' => [
                'heading' => 'SURROUND YOURSELF WITH THOSE ON THE SAME MISSION AS YOU!',
                'content' => '',
                'image' => 'images/banner.webp',
                'image_alt' => 'People sharing food together',
            ],
            'fields' => [
                [
                    'name' => 'heading',
                    'label' => 'Heading',
                    'type' => 'text',
                    'required' => true,
                ],
                [
                    'name' => 'image_alt',
                    'label' => 'Image alt text',
                    'type' => 'text',
                    'required' => true,
                ],
                [
                    'name' => 'image',
                    'label' => 'Banner image',
                    'type' => 'file',
                    'accept' => 'image/*',
                ],
            ],
        ],
        'gallery' => [
            'label' => 'Featured gallery',
            'anchor' => 'home-gallery',
            'default_data' => [
                'heading' => '',
                'content' => '',
                'image' => 'images/Canada-Flag-VOpen-Market.webp',
                'image_alt' => 'Just Eat Takeaway team members',
                'image_secondary' => 'images/jobs-in-VOpen-Market.webp',
                'image_secondary_alt' => 'Food and kitchen ingredients',
                'image_tertiary' => 'images/Passion-led-us-to-VOpen-Market.webp',
                'image_tertiary_alt' => 'People standing together outdoors',
            ],
            'fields' => [
                [
                    'name' => 'image_alt',
                    'label' => 'Image 1 alt text',
                    'type' => 'text',
                    'required' => true,
                ],
                [
                    'name' => 'image',
                    'label' => 'Image 1',
                    'type' => 'file',
                    'accept' => 'image/*',
                ],
                [
                    'name' => 'image_secondary_alt',
                    'label' => 'Image 2 alt text',
                    'type' => 'text',
                    'required' => true,
                ],
                [
                    'name' => 'image_secondary',
                    'label' => 'Image 2',
                    'type' => 'file',
                    'accept' => 'image/*',
                ],
                [
                    'name' => 'image_tertiary_alt',
                    'label' => 'Image 3 alt text',
                    'type' => 'text',
                    'required' => true,
                ],
                [
                    'name' => 'image_tertiary',
                    'label' => 'Image 3',
                    'type' => 'file',
                    'accept' => 'image/*',
                ],
            ],
        ],
        'culture' => [
            'label' => 'Culture section',
            'anchor' => 'culture',
            'default_data' => [
                'heading' => 'At VOpen Market',
                'content' => "At VOpen Market, we only hire individuals who are truly committed and passionate about growing with the company. We have built Canada's #1 open marketplace, and we are looking for team members who share our vision and dedication.",
                'requirements_title' => 'Important Requirements',
                'requirements_list' => "All candidates must have a criminal record check completed within the past 3 months.\nWe will contact candidates who best match our requirements and company culture.",
                'note_title' => 'Note',
                'note_text' => 'Please note: Do not call or submit multiple applications, as it will not increase your chances of selection. We value professionalism and respect throughout the hiring process.',
                'closing_text' => 'If you are passionate about making an impact and growing with a leading Canadian marketplace, we encourage you to apply!',
                'image' => 'images/a-true-north-company-vopen-market.webp',
                'image_alt' => 'Just Eat Takeaway paper bag',
            ],
            'fields' => [
                [
                    'name' => 'heading',
                    'label' => 'Heading',
                    'type' => 'text',
                    'required' => true,
                ],
                [
                    'name' => 'content',
                    'label' => 'Intro text',
                    'type' => 'textarea',
                    'rows' => 5,
                    'required' => true,
                ],
                [
                    'name' => 'requirements_title',
                    'label' => 'Requirements title',
                    'type' => 'text',
                    'required' => true,
                ],
                [
                    'name' => 'requirements_list',
                    'label' => 'Requirements list',
                    'type' => 'textarea',
                    'rows' => 4,
                    'required' => true,
                ],
                [
                    'name' => 'note_title',
                    'label' => 'Note title',
                    'type' => 'text',
                ],
                [
                    'name' => 'note_text',
                    'label' => 'Note text',
                    'type' => 'textarea',
                    'rows' => 4,
                ],
                [
                    'name' => 'closing_text',
                    'label' => 'Closing text',
                    'type' => 'textarea',
                    'rows' => 3,
                ],
                [
                    'name' => 'image_alt',
                    'label' => 'Image alt text',
                    'type' => 'text',
                    'required' => true,
                ],
                [
                    'name' => 'image',
                    'label' => 'Section image',
                    'type' => 'file',
                    'accept' => 'image/*',
                ],
            ],
        ],
        'jet_at_a_glance' => [
            'label' => 'Jet at a glance',
            'anchor' => 'jet-at-a-glance',
            'default_data' => [
                'heading' => 'Vopen Market',
                'content' => '',
                'stats_text' => "356 k|Partners\n61 m|Active consumers\n653 m|Orders\n€19|Gross transaction value\n16|Countries",
                'image' => 'images/project-folder.webp',
                'image_alt' => 'Fries in a Just Eat Takeaway container',
            ],
            'fields' => [
                [
                    'name' => 'heading',
                    'label' => 'Heading',
                    'type' => 'text',
                    'required' => true,
                ],
                [
                    'name' => 'stats_text',
                    'label' => 'Stats',
                    'type' => 'textarea',
                    'rows' => 6,
                    'required' => true,
                    'help' => 'Use one stat per line in the format value|label.',
                ],
                [
                    'name' => 'image_alt',
                    'label' => 'Image alt text',
                    'type' => 'text',
                    'required' => true,
                ],
                [
                    'name' => 'image',
                    'label' => 'Section image',
                    'type' => 'file',
                    'accept' => 'image/*',
                ],
            ],
        ],
        'about_intro' => [
            'label' => 'About hero and intro',
            'anchor' => 'about-hero',
            'default_data' => [
                'heading' => 'Join the VOpen Market Team',
                'content' => "At VOpen Market Inc., we're on a mission to revolutionize how construction materials and products are sourced across Canada.\n\nFrom coast to coast, our robust network of suppliers ensures we provide everything from gravel, rubber products, and precast concrete to lumber and other essential construction materials.\n\nWe're dedicated to making it easier for contractors, businesses, and DIY enthusiasts to access the products they need, efficiently and cost-effectively.",
            ],
            'fields' => [
                [
                    'name' => 'heading',
                    'label' => 'Hero heading',
                    'type' => 'text',
                    'required' => true,
                ],
                [
                    'name' => 'content',
                    'label' => 'Intro content',
                    'type' => 'textarea',
                    'rows' => 7,
                    'required' => true,
                ],
            ],
        ],
        'about_why_work' => [
            'label' => 'Why work with us',
            'anchor' => 'about-why-work',
            'default_data' => [
                'heading' => 'Why Work With Us',
                'content' => '',
                'card_one_heading' => 'Innovation at the Core',
                'card_one_content' => "We are leveraging cutting-edge AI technology to transform the construction materials industry. Our AI-driven platform connects customers with the right materials at the best prices in real time. By joining our team, you'll have the opportunity to contribute to innovative solutions that empower contractors and DIYers across Canada.",
                'card_two_heading' => 'Commitment to Excellence',
                'card_two_content' => "Quality and reliability aren't just for our customers; they're part of our culture. We partner with trusted suppliers and ensure every product meets the highest standards. As a team member, you'll be part of a company that values excellence, collaboration, and continuous improvement.",
                'card_three_heading' => 'Empowering Your Growth',
                'card_three_content' => "At VOpen Market, your career growth matters. Whether you're an industry veteran, a tech enthusiast, or someone looking to grow in a fast-paced environment, you'll find opportunities to learn, innovate, and make an impact.",
            ],
            'fields' => [
                [
                    'name' => 'heading',
                    'label' => 'Section label',
                    'type' => 'text',
                    'required' => true,
                ],
                [
                    'name' => 'card_one_heading',
                    'label' => 'Card 1 heading',
                    'type' => 'text',
                    'required' => true,
                ],
                [
                    'name' => 'card_one_content',
                    'label' => 'Card 1 content',
                    'type' => 'textarea',
                    'rows' => 5,
                    'required' => true,
                ],
                [
                    'name' => 'card_two_heading',
                    'label' => 'Card 2 heading',
                    'type' => 'text',
                    'required' => true,
                ],
                [
                    'name' => 'card_two_content',
                    'label' => 'Card 2 content',
                    'type' => 'textarea',
                    'rows' => 5,
                    'required' => true,
                ],
                [
                    'name' => 'card_three_heading',
                    'label' => 'Card 3 heading',
                    'type' => 'text',
                    'required' => true,
                ],
                [
                    'name' => 'card_three_content',
                    'label' => 'Card 3 content',
                    'type' => 'textarea',
                    'rows' => 5,
                    'required' => true,
                ],
            ],
        ],
        'about_life' => [
            'label' => 'Life at VOpen Market',
            'anchor' => 'about-life',
            'default_data' => [
                'heading' => 'Life at VOpen Market',
                'content' => "We believe that great work happens in a supportive, forward-thinking environment. Our team thrives on collaboration, creativity, and shared success. When you join VOpen Market, you become part of a team that's shaping the future of shopping in Canada.",
            ],
            'fields' => [
                [
                    'name' => 'heading',
                    'label' => 'Heading',
                    'type' => 'text',
                    'required' => true,
                ],
                [
                    'name' => 'content',
                    'label' => 'Content',
                    'type' => 'textarea',
                    'rows' => 5,
                    'required' => true,
                ],
            ],
        ],
        'about_cta' => [
            'label' => 'Impact call to action',
            'anchor' => 'about-impact',
            'default_data' => [
                'heading' => 'Ready to make an impact?',
                'content' => "Explore our current openings and join a team that's redefining how projects get built.",
                'button_label' => 'Explore current openings',
            ],
            'fields' => [
                [
                    'name' => 'heading',
                    'label' => 'Heading',
                    'type' => 'text',
                    'required' => true,
                ],
                [
                    'name' => 'content',
                    'label' => 'Content',
                    'type' => 'textarea',
                    'rows' => 4,
                    'required' => true,
                ],
                [
                    'name' => 'button_label',
                    'label' => 'Button label',
                    'type' => 'text',
                    'required' => true,
                ],
            ],
        ],
    ];

    return $definitions;
}

function homepageCmsSectionKeys(): array
{
    return array_keys(homepageCmsSectionDefinitions());
}

function homepageCmsDecodePayload(string $payloadJson): array
{
    if (trim($payloadJson) === '') {
        return [];
    }

    $decoded = json_decode($payloadJson, true);

    return is_array($decoded) ? $decoded : [];
}

function homepageCmsLoadSections(): array
{
    $definitions = homepageCmsSectionDefinitions();
    $records = [];

    try {
        $records = fetchHomepageCmsRecords();
    } catch (Throwable $exception) {
        $records = [];
    }

    $sections = [];

    foreach ($definitions as $sectionKey => $definition) {
        $defaultData = $definition['default_data'] ?? [];
        $record = $records[$sectionKey] ?? null;

        if (!is_array($record)) {
            $sections[$sectionKey] = $defaultData;
            continue;
        }

        $payload = homepageCmsDecodePayload((string) ($record['payload_json'] ?? ''));
        $section = $defaultData;
        $section['heading'] = (string) ($record['heading'] ?? '');
        $section['content'] = (string) ($record['content'] ?? '');
        $section['image'] = trim((string) ($record['image_path'] ?? '')) ?: (string) ($defaultData['image'] ?? '');
        $section['image_secondary'] = trim((string) ($record['image_path_secondary'] ?? '')) ?: (string) ($defaultData['image_secondary'] ?? '');
        $section['image_tertiary'] = trim((string) ($record['image_path_tertiary'] ?? '')) ?: (string) ($defaultData['image_tertiary'] ?? '');

        foreach ($payload as $key => $value) {
            if (is_array($value)) {
                $section[$key] = array_values(array_map('strval', $value));
                continue;
            }

            if (is_scalar($value) || $value === null) {
                $section[$key] = trim((string) $value);
            }
        }

        $sections[$sectionKey] = $section;
    }

    return $sections;
}

function homepageCmsNormalizeFieldValue(mixed $value): string
{
    if (is_array($value)) {
        return '';
    }

    return trim((string) $value);
}

function homepageCmsPrepareSectionRecord(string $sectionKey, array $sectionData): array
{
    $definitions = homepageCmsSectionDefinitions();

    if (!isset($definitions[$sectionKey])) {
        throw new InvalidArgumentException('Unknown homepage CMS section.');
    }

    $heading = trim((string) ($sectionData['heading'] ?? ''));
    $content = trim((string) ($sectionData['content'] ?? ''));
    $imagePath = trim((string) ($sectionData['image'] ?? ''));
    $imagePathSecondary = trim((string) ($sectionData['image_secondary'] ?? ''));
    $imagePathTertiary = trim((string) ($sectionData['image_tertiary'] ?? ''));
    $payload = [];

    foreach ($sectionData as $key => $value) {
        if (in_array($key, ['heading', 'content', 'image', 'image_secondary', 'image_tertiary'], true)) {
            continue;
        }

        if (is_array($value)) {
            $payload[$key] = array_values(array_map('strval', $value));
            continue;
        }

        $payload[$key] = trim((string) $value);
    }

    $payloadJson = $payload === []
        ? ''
        : (string) json_encode(
            $payload,
            JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
        );

    return [
        'section_key' => $sectionKey,
        'heading' => $heading,
        'content' => $content,
        'image_path' => $imagePath,
        'image_path_secondary' => $imagePathSecondary,
        'image_path_tertiary' => $imagePathTertiary,
        'payload_json' => $payloadJson,
    ];
}

function homepageCmsPersistSection(string $sectionKey, array $sectionData): void
{
    $record = homepageCmsPrepareSectionRecord($sectionKey, $sectionData);

    upsertHomepageCmsRecord(
        $record['section_key'],
        $record['heading'],
        $record['content'],
        $record['image_path'],
        $record['image_path_secondary'],
        $record['image_path_tertiary'],
        $record['payload_json']
    );
}

function homepageCmsUploadsRelativeDirectory(): string
{
    return 'images/cms';
}

function homepageCmsUploadsDirectory(): string
{
    return dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . 'cms';
}

function homepageCmsEnsureUploadsDirectory(): string
{
    $directory = homepageCmsUploadsDirectory();

    if (!is_dir($directory) && !mkdir($directory, 0777, true) && !is_dir($directory)) {
        throw new RuntimeException('Unable to create the CMS uploads directory.');
    }

    if (!is_writable($directory)) {
        @chmod($directory, 0777);
    }

    if (!is_writable($directory)) {
        throw new RuntimeException('The CMS uploads directory is not writable. Please make images/cms writable by the web server.');
    }

    return $directory;
}

function homepageCmsManagedImagePath(string $relativePath): bool
{
    $normalizedPath = str_replace('\\', '/', trim($relativePath));

    return $normalizedPath !== '' && str_starts_with($normalizedPath, homepageCmsUploadsRelativeDirectory() . '/');
}

function homepageCmsDeleteManagedImage(string $relativePath): void
{
    if (!homepageCmsManagedImagePath($relativePath)) {
        return;
    }

    $absolutePath = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relativePath);

    if (is_file($absolutePath)) {
        @unlink($absolutePath);
    }
}

function homepageCmsSanitizeFileStem(string $value): string
{
    $sanitized = preg_replace('/[^a-z0-9]+/i', '-', trim($value)) ?? '';
    $sanitized = trim($sanitized, '-');

    return $sanitized !== '' ? strtolower($sanitized) : 'image';
}

function homepageCmsStoreUploadedImage(string $sectionKey, string $fieldName, array $file, string $currentPath = ''): string
{
    $uploadError = (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE);

    if ($uploadError === UPLOAD_ERR_NO_FILE) {
        return trim($currentPath);
    }

    if ($uploadError !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Image upload failed for ' . $fieldName . '.');
    }

    $tmpName = (string) ($file['tmp_name'] ?? '');

    if ($tmpName === '' || !is_uploaded_file($tmpName)) {
        throw new RuntimeException('The uploaded image for ' . $fieldName . ' is invalid.');
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mimeType = (string) $finfo->file($tmpName);
    $allowedMimeTypes = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/gif' => 'gif',
        'image/webp' => 'webp',
    ];

    if (!isset($allowedMimeTypes[$mimeType])) {
        throw new RuntimeException('Only JPG, PNG, GIF, and WEBP images are allowed.');
    }

    $uploadDirectory = homepageCmsEnsureUploadsDirectory();
    $filename = implode(
        '-',
        [
            homepageCmsSanitizeFileStem($sectionKey),
            homepageCmsSanitizeFileStem($fieldName),
            date('YmdHis'),
            bin2hex(random_bytes(4)),
        ]
    ) . '.' . $allowedMimeTypes[$mimeType];
    $destinationPath = $uploadDirectory . DIRECTORY_SEPARATOR . $filename;

    if (!move_uploaded_file($tmpName, $destinationPath)) {
        throw new RuntimeException('Unable to save the uploaded image. Please make images/cms writable by the web server.');
    }

    $nextRelativePath = homepageCmsUploadsRelativeDirectory() . '/' . $filename;

    if (trim($currentPath) !== '' && trim($currentPath) !== $nextRelativePath) {
        homepageCmsDeleteManagedImage($currentPath);
    }

    return $nextRelativePath;
}

function homepageCmsSaveSection(string $sectionKey, array $postedValues, array $uploadedFiles, array $currentSection): array
{
    $definitions = homepageCmsSectionDefinitions();

    if (!isset($definitions[$sectionKey])) {
        throw new InvalidArgumentException('Unknown homepage CMS section.');
    }

    $sectionDefinition = $definitions[$sectionKey];
    $nextSection = $currentSection;

    foreach (($sectionDefinition['fields'] ?? []) as $field) {
        $fieldName = (string) ($field['name'] ?? '');
        $fieldType = (string) ($field['type'] ?? 'text');
        $fieldLabel = (string) ($field['label'] ?? $fieldName);
        $isRequired = (bool) ($field['required'] ?? false);

        if ($fieldName === '') {
            continue;
        }

        if ($fieldType === 'file') {
            $file = $uploadedFiles[$fieldName] ?? null;

            if (is_array($file)) {
                $nextSection[$fieldName] = homepageCmsStoreUploadedImage(
                    $sectionKey,
                    $fieldName,
                    $file,
                    (string) ($currentSection[$fieldName] ?? '')
                );
            }

            continue;
        }

        $nextValue = homepageCmsNormalizeFieldValue($postedValues[$fieldName] ?? '');

        if ($isRequired && $nextValue === '') {
            throw new RuntimeException($fieldLabel . ' is required.');
        }

        $nextSection[$fieldName] = $nextValue;
    }

    homepageCmsPersistSection($sectionKey, $nextSection);

    return $nextSection;
}

function homepageCmsBuildEditorConfig(array $sections): array
{
    $config = [
        'saveUrl' => 'admin/homepage-cms-save.php',
        'sections' => [],
    ];

    foreach (homepageCmsSectionDefinitions() as $sectionKey => $definition) {
        $sectionDefinition = $definition;
        unset($sectionDefinition['default_data']);
        $sectionDefinition['values'] = $sections[$sectionKey] ?? ($definition['default_data'] ?? []);
        $config['sections'][$sectionKey] = $sectionDefinition;
    }

    return $config;
}

function homepageCmsSplitTextareaLines(string $value): array
{
    $rows = preg_split('/\R+/', trim($value)) ?: [];

    return array_values(array_filter(array_map('trim', $rows), static fn(string $row): bool => $row !== ''));
}

function homepageCmsParseStats(string $value): array
{
    $rows = homepageCmsSplitTextareaLines($value);
    $stats = [];

    foreach ($rows as $row) {
        $parts = explode('|', $row, 2);
        $statValue = trim((string) ($parts[0] ?? ''));
        $statLabel = trim((string) ($parts[1] ?? ''));

        if ($statValue === '' || $statLabel === '') {
            continue;
        }

        $stats[] = [
            'value' => $statValue,
            'label' => $statLabel,
        ];
    }

    return $stats;
}
