<?php

namespace App\Http\Resources;

use App\Models\Source;
use Illuminate\Http\Resources\Json\JsonResource;

class UserPreferenceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        // Get source objects for preferred sources
        $sources = collect([]);
        if (!empty($this->preferred_sources)) {
            $sources = Source::whereIn('id', $this->preferred_sources)->get();
        }

        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'preferred_sources' => SourceResource::collection($sources),
            'preferred_categories' => $this->preferred_categories ?? [],
            'preferred_authors' => $this->preferred_authors ?? [],
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
        ];
    }
}
