<?php

namespace Aifst\Logger\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Log extends Model
{
    use HasFactory;

    /**
     * @var string[]
     */
    protected $fillable = ['user_id', 'action', 'before', 'after', 'model_id', 'model_type'];

    /**
     * @param $value
     */
    public function setUpdatedAtAttribute($value)
    {
        // to Disable updated_at
    }

    public function loggable()
    {
        return $this->morphTo();
    }

    /**
     * @param $q
     * @param $action
     * @return mixed
     */
    public function scopeAction($q, $action)
    {
        return $q->where('action', $action);
    }

    /**
     * @param $q
     * @return mixed
     */
    public function scopeWasDeleted($q)
    {
        return $q->action('deleted');
    }

    /**
     * @param $q
     * @return mixed
     */
    public function scopeWasUpdated($q)
    {
        return $q->action('updated');
    }

    /**
     * @param $q
     * @return mixed
     */
    public function scopeWasCreated($q)
    {
        return $q->action('created');
    }

    /**
     * @param $q
     * @param $start
     * @param $end
     * @return mixed
     */
    public function scopeBetween($q, $start, $end)
    {
        return $q->whereBetween('created_at', [$start, $end]);
    }

    /**
     * @param $q
     * @param $modelType
     * @param $modelId
     * @return mixed
     */
    public function scopeEntity($q, $modelType, $modelId)
    {
        return $q->where('model_type', $modelType)
            ->where('model_id', $modelId);
    }

    /**
     * @param $q
     * @param $datetime
     * @return mixed
     */
    public function scopeStateOn($q, $datetime)
    {
        $query = clone($q);
        $class = $q->first()->model_type;
        $attrs = $q->wasCreated()->first()->after;

        $changes = $query->wasUpdated()
            ->where('created_at', '<=', $datetime)
            ->get();

        foreach ($changes as $change) {
            $attrs = array_merge($attrs, $change->after);
        }

        return new $class($attrs);
    }

    /**
     * @param $value
     * @return array|null
     */
    public function getBeforeAttribute($value)
    {
        return $value ? (array)json_decode($value) : null;
    }

    /**
     * @param $value
     * @return array|null
     */
    public function getAfterAttribute($value)
    {
        return $value ? (array)json_decode($value) : null;
    }
}
