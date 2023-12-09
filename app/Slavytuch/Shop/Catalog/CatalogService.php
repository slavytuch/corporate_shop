<?php

namespace App\Slavytuch\Shop\Catalog;

use App\Models\Product;
use App\Slavytuch\Shop\Catalog\DTO\DisplayProductDTO;
use App\Slavytuch\Shop\Catalog\Exceptions\CatalogServiceException;

class CatalogService
{
    public function itemsPerPage(): int
    {
        return 2;
    }

    public function prepareProductDisplay(int $productId): DisplayProductDTO
    {
        $product = Product::find($productId);

        if (!$product) {
            throw new CatalogServiceException('Не могу найти товар');
        }

        $caption = $product->name . PHP_EOL . PHP_EOL;

        $priceList = $product->prices()->get();
        foreach ($priceList as $price) {
            $caption .= $price->name . ': ' . $price->pivot->price . PHP_EOL;
        }

        if ($product->description) {
            $caption .= PHP_EOL . $product->description;
        }

        return new DisplayProductDTO(product: $product, caption: $caption, picture: $product->picture);
    }
}
