<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Product;
use App\Models\Vendor;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\Ecommerce\ProductController;

// Find a product with variants and vendor offers
$product = Product::whereHas('variants.vendorOffers')->first();

if (!$product) {
    echo "No suitable product found for testing.\n";
    exit(1);
}

echo "Testing with Product ID: " . $product->id . "\n";

// Test details API
echo "\n--- Testing details API ---\n";
$controller = new ProductController();
$request = Request::create('/api/v1/ecommerce/products/' . $product->id . '/details', 'GET');
$response = $controller->details($product->id);
$data = $response->getData(true);

if ($data['success']) {
    echo "Details API Success!\n";
    $p = $data['data'];
    echo "Product: " . $p['name'] . "\n";
    
    echo "Attributes:\n";
    if (!empty($p['attributes'])) {
        foreach ($p['attributes'] as $attr) {
            echo " - " . $attr['name'] . " (Code: " . ($attr['code'] ?? 'N/A') . ")\n";
            if (!empty($attr['values'])) {
                echo "   Values: ";
                foreach ($attr['values'] as $val) {
                    echo ($val['formatted_value'] ?? $val['value']) . ", ";
                }
                echo "\n";
            }
        }
    } else {
        echo " - None\n";
    }

    echo "Variant Options:\n";
    if (!empty($p['variant_options'])) {
        foreach ($p['variant_options'] as $opt) {
            echo " - " . $opt['name'] . " (" . count($opt['values']) . " values)\n";
        }
    } else {
        echo " - None\n";
    }

    echo "Variants: " . count($p['variants']) . "\n";
} else {
    echo "Details API Failed: " . $data['message'] . "\n";
}

// Test vendor prices API
$vendorId = $product->variants->first()->vendorOffers->first()->vendor_id ?? null;

if ($vendorId) {
    echo "\n--- Testing vendor prices API for Vendor ID: $vendorId ---\n";
    $request = Request::create('/api/v1/ecommerce/products/' . $product->id . '/vendor/' . $vendorId . '/prices', 'GET');
    $response = $controller->vendorPrices($product->id, $vendorId);
    $data = $response->getData(true);

    if ($data['success']) {
        echo "Vendor Prices API Success!\n";
        // print_r($data['data']);
        foreach ($data['data'] as $sku) {
            echo "Variant ID: " . ($sku['variant']['id'] ?? 'N/A') . "\n";
            echo "Units:\n";
            foreach ($sku['units'] as $unit) {
                echo " - " . ($unit['unit_name'] ?? 'N/A') . ": " . $unit['selling_price'] . "\n";
            }
        }
    } else {
        echo "Vendor Prices API Failed: " . $data['message'] . "\n";
    }
} else {
    echo "No vendor ID found to test prices API.\n";
}
