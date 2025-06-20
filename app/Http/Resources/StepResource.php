<?php

namespace App\Http\Resources;

use App\Models\Step;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Step
 */
class StepResource extends JsonResource
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
            'survey_id' => $this->survey_id,
            'order' => $this->order,
            'title' => $this->title,
            'content' => $this->content,
            'step_type' => $this->step_type,
            'question_type' => $this->question_type,
            'options' => $this->options,
            'choices' => ChoiceResource::collection($this->whenLoaded('choices')),
        ];
    }
}
