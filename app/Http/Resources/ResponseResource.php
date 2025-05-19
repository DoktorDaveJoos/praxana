<?php

namespace App\Http\Resources;

use App\Models\Response;
use App\StepType;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Response
 */
class ResponseResource extends JsonResource
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
            'survey_run_id' => $this->survey_run_id,
            'step_id' => $this->step_id,
            'choice_id' => $this->choice_id,
            'question' => $this->step->content,
            'question_type' => $this->type,
            'value' => $this->type->cast($this->value),
        ];
    }
}
