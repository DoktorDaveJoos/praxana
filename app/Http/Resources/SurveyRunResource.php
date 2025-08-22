<?php

namespace App\Http\Resources;

use App\Models\SurveyRun;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin SurveyRun
 */
class SurveyRunResource extends JsonResource
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
            'name' => $this->survey->name,
            'status' => $this->status,
            'started_at' => $this->started_at?->format('Y-m-d H:i:s'),
            'finished_at' => $this->finished_at?->format('Y-m-d H:i:s'),
            'ai_analysis' => $this->ai_analysis,
            'current_step_id' => $this->current_step_id ?? $this->survey->steps->sortBy('order')->first()?->id,
            'responses' => $this->whenLoaded('responses', function () {
                return ResponseResource::collection($this->responses);
            }),
        ];
    }
}
