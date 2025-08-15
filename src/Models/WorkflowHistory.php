<?php

namespace Edwinrtoha\Laravelboilerplate\Models;

use Edwinrtoha\Laravelboilerplate\Models\ModelStd;
use Spatie\Activitylog\Models\Activity;

class WorkflowHistory extends ModelStd
{
    protected static function booted()
    {
        static::created(function ($data) {
            $model = new $data->model_type;
            $data = WorkflowHistory::with('state')->where('id', $data->id)->first();
            $model_item = $model->where('id', $data->model_id)->first();
            activity()
                ->performedOn($model_item)
                ->event($data->state->state_name)
                ->causedBy(auth()->user() ?? null)
                ->withProperties(["attributes" => []]);
                // ->log('Workflow change to ' . $data->state->state_name);
            // activity()
            //     ->performedOn($model)
            //     ->causedBy(auth()->user() ?? null)
            //     ->withProperties(['custom' => 'value'])
            //     ->log($model->state);
        });

        static::updating(function ($model) {
            // Add logic to execute when a KYC model is being updated
        });
    }
    public function state()
    {
        return $this->hasOne(WorkflowState::class, 'id', 'state_id');
    }
}
