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
            'title' => $this->title,
            'content' => $this->content,
            'step_type' => $this->step_type,
            'question_type' => $this->question_type,
            'options' => $this->options,
            'default_next_step_id' => $this->default_next_step_id,
            'choices' => ChoiceResource::collection($this->whenLoaded('choices')),
        ];
    }
}
