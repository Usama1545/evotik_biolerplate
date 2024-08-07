<?php

namespace App\Services;

use App\Models\Country;

class PhoneValidationService
{
    protected string $phone;

    protected string $countryCode;

    protected bool $isProcessed = false;

    public function __construct(string $phone, string $countryCode)
    {
        $this->phone = $phone;
        $this->countryCode = $countryCode;
    }

    public function process(): self
    {
        $this->phone = $this->arabicToEnglishNumbers($this->phone);
        $this->phone = preg_replace('/\s+/', '', $this->phone);
        $this->phone = ltrim($this->phone, '0');

        $callingCode = Country::where('iso_2', $this->countryCode)->pluck('calling_code')->first();

        if (str_starts_with($this->phone, $callingCode)) {
            $this->phone = substr($this->phone, strlen($callingCode));
        }

        $this->isProcessed = true;

        return $this;
    }

    public function getProcessedPhone(): array
    {
        if (!$this->isProcessed) {
            $this->process();
        }

        $callingCode = Country::where('iso_2', $this->countryCode)->pluck('calling_code')->first();

        return [$callingCode, $this->phone];
    }

    protected function arabicToEnglishNumbers($string)
    {
        $arabicNumbers = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];
        $englishNumbers = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];

        return str_replace($arabicNumbers, $englishNumbers, $string);
    }

    public function isValid()
    {
        $phoneUtil = \libphonenumber\PhoneNumberUtil::getInstance();
        $numberProto = $phoneUtil->parse($this->phone, $this->countryCode);
        return $phoneUtil->isValidNumber($numberProto);
    }
}
