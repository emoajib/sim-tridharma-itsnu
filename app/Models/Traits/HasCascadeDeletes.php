<?php

namespace App\Models\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/** @mixin SoftDeletes */
trait HasCascadeDeletes
{
    public static function bootHasCascadeDeletes(): void
    {
        static::deleting(function (Model $model) {
            $relations = property_exists($model, 'cascadeDeletes') ? $model->cascadeDeletes : [];

            foreach ($relations as $relation) {
                if (method_exists($model, 'isForceDeleting') && $model->isForceDeleting()) {
                    $model->{$relation}()->forceDelete();
                } else {
                    $model->{$relation}()->delete();
                }
            }
        });
    }
}
