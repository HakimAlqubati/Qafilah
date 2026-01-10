<?php

namespace App\Http\Controllers\Api\Address;

use App\Http\Controllers\Api\ApiController;
use App\Http\Resources\CustomerAddressResource;
use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Category;
use App\Models\Currency;
use App\Models\Unit;
use App\Repositories\Address\CustomerAddressRepository;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use App\Http\Controllers\Controller;

class CustomerAddressController extends Controller
{
    use ApiResponse;
    public function __construct(
        private readonly CustomerAddressRepository $repo
    ) {}

    private function customerId(): int
    {
        return (int) auth()->id();
    }

    public function index()
    {
        $items = $this->repo->listForCustomer($this->customerId());

        return $this->successResponse(CustomerAddressResource::collection($items), 'Addresses fetched successfully.');

    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'type'        => ['sometimes', 'string', 'max:191'],
            'city_id'     => ['nullable', 'integer'],
            'district_id' => ['nullable', 'integer'],
            'address'     => ['nullable', 'string'],
            'latitude'    => ['nullable', 'numeric'],
            'longitude'   => ['nullable', 'numeric'],
            'is_default'  => ['sometimes', 'boolean'],
        ]);

        $created = $this->repo->createForCustomer($this->customerId(), $validated);
        return $this->successResponse(new CustomerAddressResource($created), 'Address created successfully.');

    }


    public function update(Request $request, int $id)
    {
        $validated = $request->validate([
            'type'        => ['sometimes', 'string', 'max:191'],
            'city_id'     => ['sometimes', 'nullable', 'integer'],
            'district_id' => ['sometimes', 'nullable', 'integer'],
            'address'     => ['sometimes', 'nullable', 'string'],
            'latitude'    => ['sometimes', 'nullable', 'numeric'],
            'longitude'   => ['sometimes', 'nullable', 'numeric'],
        ]);

        $address = $this->repo->updateForCustomer($this->customerId(), $id, $validated);
        return $this->successResponse(new CustomerAddressResource($address), 'Address created successfully.');
    }

    public function destroy(int $id)
    {
        $this->repo->deleteForCustomer($this->customerId(), $id);
        return $this->successResponse(null, 'Address deleted successfully.');


    }

    public function setDefault(int $id)
    {
        $address = $this->repo->setDefaultForCustomer($this->customerId(), $id);
        return $this->successResponse(new CustomerAddressResource($address), 'Default address updated successfully.');


    }
}
