<?php

namespace App\Http\Resources;

use App\Http\Resources\Concerns\HidesCostUnlessAuthorized;
use Illuminate\Http\Resources\Json\JsonResource;

class PurchaseItemResource extends JsonResource
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
            'purchase_id' => $this->purchase_id,
            'variant_id' => $this->variant_id,
            'unit_id' => $this->unit_id,
            'quantity' => $this->quantity,
            'factor_to_base' => $this->factor_to_base,
            'quantity_base' => $this->quantity_base,
            'line_total' => $this->line_total,
            'expiry_date' => $this->expiry_date,
            'lot_code' => $this->lot_code,
        ];

        if ($this->viewerCanSeeCost($request)) {
            $data['unit_cost'] = $this->unit_cost;
        }

        return $data;
    }
}
