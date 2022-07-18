y<?php

namespace App\Traits;

use Aifst\Logger\Models\Log;

trait Logger
{
    /**
     * @return bool
     */
    protected static function loggedCreated(): bool
    {
        return true;
    }

    /**
     * @return bool
     */
    protected static function loggedUpdating(): bool
    {
        return true;
    }

    /**
     * @return bool
     */
    protected static function loggedDeleting(): bool
    {
        return true;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function logs()
    {
        return $this->morphMany('App\Models\Log', 'model');
    }

    /**
     *
     */
    public static function bootLogger()
    {
        static::loggedCreated() && static::created(function ($model) {
            $model->logCreated();
        });

        static::loggedUpdating() && static::updating(function ($model) {
            $model->logUpdated();
        });

        static::loggedDeleting() && static::deleting(function ($model) {
            $model->logDeleted($model);
        });
    }

    protected static function loggerUserId()
    {
        return null;
    }

    /**
     * @param $action
     * @param $before
     * @param $after
     * @return false|\Illuminate\Database\Eloquent\Model
     */
    protected function insertNewLog($action, $before, $after)
    {
        return $this->logs()->save(new Log([
            'user_id' => static::loggerUserId(),
            'action' => $action,
            'before' => $before ? json_encode($before) : null,
            'after' => $after ? json_encode($after) : null
        ]));
    }

    /**
     * @return false|\Illuminate\Database\Eloquent\Model
     */
    protected function logCreated()
    {
        $model = $this->stripRedundantKeys();
        return $this->insertNewLog('created', null, $model);
    }

    /**
     * @return false|\Illuminate\Database\Eloquent\Model
     */
    protected function logUpdated()
    {
        $diff = $this->getDiff();
        return $this->insertNewLog('updated', $diff['before'], $diff['after']);
    }

    /**
     * @return false|\Illuminate\Database\Eloquent\Model
     */
    protected function logDeleted()
    {
        $model = $this->stripRedundantKeys();
        return $this->insertNewLog('deleted', $model, null);
    }

    /**
     * Fetch a diff for the model's current state.
     */
    protected function getDiff()
    {
        $after = $this->getDirty();
        $before = array_intersect_key($this->fresh()->toArray(), $after);

        return compact('before', 'after');
    }

    /**
     * @return array|null
     */
    protected static function loggedFields(): ?array
    {
        return [];
    }

    /**
     * @return array
     */
    protected function stripRedundantKeys()
    {
        $model = $this->toArray();

        if (isset($model['created_at'])) {
            unset($model['created_at']);
        }

        if (isset($model['updated_at'])) {
            unset($model['updated_at']);
        }

        if (isset($model['id'])) {
            unset($model['id']);
        }

        if ( $fields = static::loggedFields() ) {
            $model = array_intersect_key(
                $model,
                array_flip($fields)
            );
        }

        return $model;
    }
}