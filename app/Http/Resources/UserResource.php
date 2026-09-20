<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,

            'avatar' => $this->avatar,
            'phone' => $this->phone,
            'birthday' => $this->birthday,
            'national_id' => $this->national_id,
            'job' => $this->job,

            'is_active' => $this->is_active,
            'email_verified_at' => $this->email_verified_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            'roles' => $this->whenLoaded('roles', function () {
                return RoleResource::collection($this->roles);
            }),

            'permissions' => PermissionResource::collection(
                $this->getAllPermissions()
            ),

        ];
    }
}
