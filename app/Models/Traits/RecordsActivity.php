<?php


namespace App\Models\Traits;

use App\Models\Activity;
use Illuminate\Support\Arr;

trait RecordsActivity
{
    /**
     * The models old attributes
     *
     * @var array
     */
    public array $oldAttributes = [];

    /**
     * Boot the trait
     */
    public static function bootRecordsActivity()
    {
        foreach (self::recordableEvents() as $event) {
            static::$event(function ($model) use ($event) {
                $model->recordActivity($model->activityDescription($event));
            });

            if ($event === 'updated') {
                static::updating(function ($model) {
                    $model->oldAttributes = $model->getOriginal();
                });
            }
        }
    }

    /**
     * @param $description
     * @return string
     */
    protected function activityDescription($description): string
    {
        return "{$description} " . strtolower(class_basename($this));
    }

    /**
     * @return array
     */
    protected static function recordableEvents(): array
    {
        return (isset(static::$recordableEvents)) ? static::$recordableEvents : ['created', 'updated', 'deleted'];
    }

    /**
     * Record activity for the given model
     *
     * @param $description
     */
    public function recordActivity($description)
    {
        $user = (auth()->user()) ?? $this->owner;

        Activity::create([
            'user_id'        => ($user?->id) ?? 0,
            'content_type'   => lcfirst(class_basename(static::class)),
            'object_id'      => $this->id,
            'change_message' => $description,
            'action_flag'    => 1,
            'changes'        => $this->activityChanges(),
            'user_real_name' => ($user?->getFullNameAttribute()) ?? "CRONJOB"
        ]);
    }

    /**
     * Fetch all activities for a given model
     *
     * @return mixed
     */
    public function activities()
    {
        return Activity::where('object_id', $this->id)->where('content_type', lcfirst(class_basename(static::class)));
    }

    /**
     * @return array
     */
    protected function activityChanges(): array
    {
        return ($this->wasChanged())
            ?
            [
                'before' => Arr::except(array_diff($this->oldAttributes, $this->getAttributes()), ['updated_at']),
                'after'  => Arr::except($this->getChanges(), ['updated_at'])
            ]
            : [];
    }
}