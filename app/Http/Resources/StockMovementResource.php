<?php

namespace App\Http\Resources;

use App\Http\Resources\Concerns\HidesCostUnlessAuthorized;
use Illuminate\Http\Resources\Json\JsonResource;

class StockMovementResource extends JsonResource
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
            'shop_id' => $this->shop_id,
            'variant_id' => $this->variant_id,
            'lot_id' => $this->lot_id,
            'movement_type' => $this->movement_type,
            'qty_delta' => $this->qty_delta,
            'qty_after' => $this->qty_after,
            'reference_type' => $this->reference_type,
            'reference_id' => $this->reference_id,
            'reason' => $this->reason,
            'occurred_at' => $this->occurred_at,
            'created_by' => $this->created_by,
            'approved_by' => $this->approved_by,
        ];

        if ($this->viewerCanSeeCost($request)) {
            $data['unit_cost'] = $this->unit_cost;
        }

        return $data;
    }
}
