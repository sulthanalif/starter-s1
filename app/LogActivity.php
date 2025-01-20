<?php

namespace App;

use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

trait LogActivity
{
    use LogsActivity;


    /**
     * Define whether changes should be logged.
     *
     * @var bool
     */
    protected static $logOnlyDirty = true;

     /**
     * Define the attributes that should be excluded from logging.
     *
     * @var string[]
     */
    // protected static $logAttributesToIgnore = ['password', 'secret_key', 'api_token'];

    /**
     * Customize log name.
     *
     * @return string
     */
    public function getActivitylogOptions(): \Spatie\Activitylog\LogOptions
    {
        return \Spatie\Activitylog\LogOptions::defaults()
            ->useLogName($this->getLogName())
            ->logOnly($this->getLogAttributes())
            ->logOnlyDirty()
            ->dontLogIfAttributesChangedOnly($this->getLogAttributesToIgnore())
            ->dontSubmitEmptyLogs();
            // ->useAttributeRawValues($this->getLogAttributesToIgnore());
    }

    public function tapActivity(Activity $activity, string $eventName)
    {
        $properties = $activity->properties->toArray();

            // Ambil atribut yang harus diabaikan
            $attributesToIgnore = $this->getLogAttributesToIgnore();

            // Ganti nilai atribut yang diabaikan menjadi 'rahasia'
            foreach ($attributesToIgnore as $attribute) {
                if (isset($properties['attributes'][$attribute])) {
                    $properties['attributes'][$attribute] = 'Secret';
                }
                if (isset($properties['old'][$attribute])) {
                    $properties['old'][$attribute] = 'Secret';
                }
            }

            $activity->properties = $properties;
    }

    /**
     * Get the name for the activity log.
     *
     * @return string
     */
    protected function getLogName(): string
    {
        return property_exists($this, 'logName') ? $this->logName : 'default';
    }

    protected function getLogAttributesToIgnore(): array
    {
        return property_exists($this, 'logAttributesToIgnore') ? $this->logAttributesToIgnore : [];
    }

    protected function getLogAttributes(): array
    {
        return property_exists($this, 'fillable') ? (array) $this->fillable : [];
    }
}
