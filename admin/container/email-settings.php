<?php
declare(strict_types=1);

const JOB_APPLICATION_EMAIL_GROUPS = ['to', 'cc', 'bcc'];

function jobApplicationEmailSettingsPath(): string
{
    return dirname(__DIR__, 2) . '/storage/job-application-email-settings.json';
}

function defaultJobApplicationEmailSettings(): array
{
    return [
        'to' => [
            ['email' => 'arockiasinc31@gmail.com', 'name' => 'Recruitment'],
        ],
        'cc' => [],
        'bcc' => [],
    ];
}

function normalizeJobApplicationEmailRecipients(array $recipients): array
{
    $normalizedRecipients = [];
    $seenEmails = [];

    foreach ($recipients as $recipient) {
        if (is_array($recipient)) {
            $email = trim((string) ($recipient['email'] ?? ''));
            $name = trim((string) ($recipient['name'] ?? ''));
        } else {
            $email = trim((string) $recipient);
            $name = '';
        }

        if ($email === '') {
            continue;
        }

        $validatedEmail = filter_var($email, FILTER_VALIDATE_EMAIL);

        if ($validatedEmail === false) {
            continue;
        }

        $dedupeKey = strtolower($validatedEmail);

        if (isset($seenEmails[$dedupeKey])) {
            continue;
        }

        $seenEmails[$dedupeKey] = true;
        $normalizedRecipients[] = [
            'email' => $validatedEmail,
            'name' => $name,
        ];
    }

    return $normalizedRecipients;
}

function normalizeJobApplicationEmailSettings(array $settings): array
{
    $normalizedSettings = [];

    foreach (JOB_APPLICATION_EMAIL_GROUPS as $group) {
        $normalizedSettings[$group] = normalizeJobApplicationEmailRecipients((array) ($settings[$group] ?? []));
    }

    return $normalizedSettings;
}

function loadJobApplicationEmailSettings(): array
{
    $settingsPath = jobApplicationEmailSettingsPath();
    $defaultSettings = normalizeJobApplicationEmailSettings(defaultJobApplicationEmailSettings());

    if (!is_file($settingsPath)) {
        return $defaultSettings;
    }

    $json = @file_get_contents($settingsPath);

    if (!is_string($json) || trim($json) === '') {
        return $defaultSettings;
    }

    $decoded = json_decode($json, true);

    if (!is_array($decoded)) {
        return $defaultSettings;
    }

    $normalizedSettings = normalizeJobApplicationEmailSettings($decoded);

    foreach (JOB_APPLICATION_EMAIL_GROUPS as $group) {
        if ($group === 'to' && $normalizedSettings[$group] === []) {
            $normalizedSettings[$group] = $defaultSettings[$group];
        }
    }

    return $normalizedSettings;
}

function saveJobApplicationEmailSettings(array $settings): void
{
    $settingsPath = jobApplicationEmailSettingsPath();
    $storageDirectory = dirname($settingsPath);

    if (!is_dir($storageDirectory) && !@mkdir($storageDirectory, 0777, true) && !is_dir($storageDirectory)) {
        throw new RuntimeException('Unable to create the email settings storage directory.');
    }

    @chmod($storageDirectory, 0777);

    $normalizedSettings = normalizeJobApplicationEmailSettings($settings);

    if ($normalizedSettings['to'] === []) {
        throw new RuntimeException('At least one To recipient is required.');
    }

    $encodedSettings = json_encode($normalizedSettings, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

    if (!is_string($encodedSettings) || @file_put_contents($settingsPath, $encodedSettings, LOCK_EX) === false) {
        throw new RuntimeException('Unable to save the email settings.');
    }
}
