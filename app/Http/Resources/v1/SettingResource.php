<?php

namespace App\Http\Resources\v1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SettingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'key'         => $this->key,
            'value'       => $this->casted_value,
            'raw_value'   => $this->value,
            'group'       => $this->group,
            'type'        => $this->type,
            'label'       => $this->label,
            'description' => $this->description,
            'is_public'   => (bool) $this->is_public,
            'is_editable' => (bool) $this->is_editable,
            'created_at'  => $this->created_at,
            'updated_at'  => $this->updated_at,
        ];
    }
}