<?php

namespace BaksDev\Contacts\Region\Repository\ContactCallByRegion;

final class ContactCallByRegionResult
{
    public function __construct(
        private ?string $region_name,
        private ?string $call_name,
        private ?string $calls_phone,

        private ?string $region_description = null,
        private ?string $call_description = null,
        private ?string $call_address = null,
        private ?string $call_email = null,
        private ?string $call_working = null,
        private ?string $call_latitude = null,
        private ?string $call_longitude = null,
        private ?string $call_cover_name = null,
        private ?string $call_cover_ext = null,
        private ?string $call_cover_cdn = null,
    ) {}

    public function getRegionName(): ?string
    {
        return $this->region_name;
    }

    public function getRegionDescription(): ?string
    {
        return $this->region_description;
    }

    public function getCallName(): ?string
    {
        return $this->call_name;
    }

    public function getCallDescription(): ?string
    {
        return $this->call_description;
    }

    public function getCallsPhone(): ?string
    {
        return $this->calls_phone;
    }

    public function getCallAddress(): ?string
    {
        return $this->call_address;
    }

    public function getCallEmail(): ?string
    {
        return $this->call_email;
    }

    public function getCallWorking(): ?string
    {
        return $this->call_working;
    }

    public function getCallLatitude(): ?string
    {
        return $this->call_latitude;
    }

    public function getCallLongitude(): ?string
    {
        return $this->call_longitude;
    }

    public function getCallCoverName(): ?string
    {
        return $this->call_cover_name;
    }

    public function getCallCoverExt(): ?string
    {
        return $this->call_cover_ext;
    }

    public function getCallCoverCdn(): ?string
    {
        return $this->call_cover_cdn;
    }
}