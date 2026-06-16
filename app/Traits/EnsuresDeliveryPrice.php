<?php
declare(strict_types=1);

namespace App\Traits;

use App\Models\Area;
use App\Models\City;
use App\Models\Country;
use App\Models\DeliveryPrice;

trait EnsuresDeliveryPrice
{
    protected function ensureCountryDeliveryPrice(Country $country, float $price = 0): void
    {
        if ($country->deliveryPrice()->exists()) {
            return;
        }

        DeliveryPrice::create([
            'price'      => $price,
            'region_id'  => $country->region_id,
            'country_id' => $country->id,
        ]);
    }

    protected function ensureCityDeliveryPrice(City $city, float $price = 0): void
    {
        if ($city->deliveryPrice()->exists()) {
            return;
        }

        DeliveryPrice::create([
            'price'      => $price,
            'region_id'  => $city->region_id,
            'country_id' => $city->country_id,
            'city_id'    => $city->id,
        ]);
    }

    protected function ensureAreaDeliveryPrice(Area $area, float $price = 0): void
    {
        if ($area->deliveryPrice()->exists()) {
            return;
        }

        DeliveryPrice::create([
            'price'      => $price,
            'region_id'  => $area->region_id,
            'country_id' => $area->country_id,
            'city_id'    => $area->city_id,
            'area_id'    => $area->id,
        ]);
    }
}
