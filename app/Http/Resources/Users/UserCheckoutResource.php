<?php

namespace App\Http\Resources\Users;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserCheckoutResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'order_id' => $this->id,
            'user_id' => $this->user_id,
            'total_quantity' => $this->total_quantity,
            'total_for_all' => $this->total_for_all,
            'order_details' => UserOrderDetailResource::collection($this->orderDetails),
        ];
    }

    /**
     * Customize the response format.
     */
    public function toResponse($request)
    {
        return response()->json([
            'status' => 'success',
            'message' => "Order placed successfully with {$this->total_quantity} items totaling \${$this->total_for_all}.",
            'data' => $this->toArray($request),
        ], 200);
    }
}
