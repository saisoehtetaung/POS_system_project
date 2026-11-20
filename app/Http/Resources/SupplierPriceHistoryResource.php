<?php

namespace App\Http\Resources;

use App\Http\Resources\Concerns\HidesCostUnlessAuthorized;
use Illuminate\Http\Resources\Json\JsonResource;

class SupplierPriceHistoryResource extends JsonResource
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
            'supplier_id' => $this->supplier_id,
            'variant_id' => $this->variant_id,
            'unit_id' => $this->unit_id,
            'purchase_item_id' => $this->purchase_item_id,
            'effective_at' => $this->effective_at,
        ];

        if ($this->viewerCanSeeCost($request)) {
            $data['unit_cost'] = $this->unit_cost;
        }

        return $data;
    }
}
