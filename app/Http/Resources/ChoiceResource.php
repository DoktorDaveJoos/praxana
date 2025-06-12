<?php

namespace App\Http\Resources;

use App\Models\Choice;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Choice
 */
class ChoiceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'step_id' => $this->step_id,
            'label' => $this->label,
            'value' => $this->value,
            'order' => $this->order,
            'optional_next_step' => $this->optional_next_step,
        ];
    }
}
