<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * @param  \Illuminate\Http\Request  $request
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'shop_id' => $this->shop_id,
            'category_id' => $this->category_id,
            'brand_id' => $this->brand_id,
            'base_unit_id' => $this->base_unit_id,
            'name' => $this->name,
            'description' => $this->description,
            'image_path' => $this->image_path,
            'product_type' => $this->product_type,
            'track_stock' => $this->track_stock,
            'track_expiry' => $this->track_expiry,
            'is_active' => $this->is_active,
            'variants' => ProductVariantResource::collection($this->whenLoaded('variants')),
        ];
    }
}
