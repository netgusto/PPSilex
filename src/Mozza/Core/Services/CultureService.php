<?php

namespace Mozza\Core\Services;

class CultureService {

    protected $locale;
    protected $dateformat;
    protected $timezone;

    public function __construct(/* string */ $locale, /* string */ $dateformat, /* string */ $timezonename) {
        $this->locale = $locale;
        $this->dateformat = $dateformat;
        $this->timezone = new \DateTimeZone($timezonename);
    }

    public function humanDate(\DateTime $date) {
        return $date->format($this->dateformat);
    }

    public function getTimezone() {
        return $this->timezone;
    }

    public function getLocale() {
        return $this->locale;
    }
}