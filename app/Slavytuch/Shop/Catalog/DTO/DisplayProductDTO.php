<?php

namespace App\Slavytuch\Shop\Catalog\DTO;

use App\Models\Product;

readonly class DisplayProductDTO
{
    public function __construct(
        public Product $product,
        public string $caption,
        public ?string $picture = null
    ) {
    }
}
