<?php

namespace App\Http\Resources;

use App\Models\Plan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $plan = $this->subscription ? $this->subscription->plan : null;

        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'role' => User::ROLES[$this->role],
            'subscription' => $this->subscription,
            'plan' => $plan,
        ];
    }
}
