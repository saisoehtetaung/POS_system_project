<?php

namespace App\Http\Resources;

use App\Http\Resources\Concerns\HidesCostUnlessAuthorized;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductVariantResource extends JsonResource
{
    use HidesCostUnlessAuthorized;

    /**
     * @param  \Illuminate\Http\Request  $request
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        $data = [
            'id' => $this->id,
            'product_id' => $this->product_id,
            'sku' => $this->sku,
            'barcode' => $this->barcode,
            'name' => $this->name,
            'retail_price' => $this->retail_price,
            'wholesale_price' => $this->wholesale_price,
            'min_wholesale_qty' => $this->min_wholesale_qty,
            'reorder_level' => $this->reorder_level,
            'is_active' => $this->is_active,
        ];

        if ($this->viewerCanSeeCost($request)) {
            $data['cost_price'] = $this->cost_price;
        }

        return $data;
    }
}
