<?php

namespace Modules\Advertisements\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Advertisement Resource
 */
class AdvertisementResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'advertisement_number' => $this->advertisement_number,
            'title' => $this->title,
            'slug' => $this->slug,
            'description' => $this->description,
            'short_description' => $this->short_description,
            'price' => $this->price,
            'currency' => $this->currency,
            'status' => $this->status->value,
            'visibility' => $this->visibility->value,
            'published_at' => $this->published_at,
            'expires_at' => $this->expires_at,
            'views_count' => $this->views_count,
            'contacts_count' => $this->contacts_count,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
        ];
    }
}

/**
 * Advertisement Workflow State Resource
 */
class AdvertisementWorkflowStateResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'current_state' => $this->status->value,
            'label' => $this->status->value,
            'description' => config("advertisement-workflow.states.{$this->status->value}.description", ''),
            'is_final' => config("advertisement-workflow.states.{$this->status->value}.is_final", false),
            'is_published' => config("advertisement-workflow.states.{$this->status->value}.is_published", false),
            'is_archived' => config("advertisement-workflow.states.{$this->status->value}.is_archived", false),
            'is_searchable' => config("advertisement-workflow.states.{$this->status->value}.is_searchable", false),
            'is_editable' => config("advertisement-workflow.states.{$this->status->value}.is_editable", false),
            'is_deletable' => config("advertisement-workflow.states.{$this->status->value}.is_deletable", false),
        ];
    }
}

/**
 * Workflow Transition Response Resource
 */
class WorkflowTransitionResponseResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'success' => $this->success,
            'message' => $this->message,
            'old_state' => $this->old_state,
            'new_state' => $this->new_state,
            'advertisement' => $this->advertisement ? new AdvertisementResource($this->advertisement) : null,
        ];
    }
}
