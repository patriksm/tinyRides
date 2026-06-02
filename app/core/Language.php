<?php

class Language
{
    private string $currentLang;
    private string $defaultLang = 'en';
    private array $translations = [];
    private array $fallbackTranslations = [];

    public function __construct(?string $lang = null)
    {
        $this->currentLang = $lang ?: $this->defaultLang;

        $this->fallbackTranslations = $this->load($this->defaultLang);
        $this->translations = $this->load($this->currentLang);
    }

    private function load(string $lang): array
    {
        $path = __DIR__ . '/../../lang/' . $lang . '.json';

        if (!file_exists($path)) {
            return [];
        }

        $json = file_get_contents($path);
        $data = json_decode($json, true);

        return is_array($data) ? $data : [];
    }

    public function getCurrentLang(): string
    {
        return $this->currentLang;
    }

    public function translate(string $key): string
    {
        if (isset($this->translations[$key])) {
            return $this->translations[$key];
        }

        if (isset($this->fallbackTranslations[$key])) {
            return $this->fallbackTranslations[$key];
        }

        return $key;
    }
}
