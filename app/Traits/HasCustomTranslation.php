<?php

namespace App\Traits;

use Spatie\Translatable\HasTranslations;

trait HasCustomTranslation
{

    use HasTranslations {
        setTranslation as protected setTranslationFromSpatie;
        getTranslation as protected getTranslationFromSpatie;
    }

    public function setTranslation(string $key, string $locale, $value): self
    {
        if (in_array($this->key, self::TRANSLATED_VALUES)) {
            return $this->setTranslationFromSpatie($key, $locale, $value);
        } else {
            return parent::setAttribute($key, $value);
        }
    }

    public function getTranslation(string $key, string $locale, bool $useFallbackLocale = true): mixed
    {
        if (is_array($this->getOriginal($key))) {
            if (array_key_exists('en', $this->getOriginal()[$key])) {
                return $this->getTranslationFromSpatie($key, $locale);
            }
        }
        return parent::getAttributeValue($key);
    }
}
