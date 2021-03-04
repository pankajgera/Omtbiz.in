<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\Tax;
use App\Models\TaxType;
use Faker\Generator as Faker;
use App\Models\User;

$factory->define(Tax::class, function (Faker $faker) {
    return [
        'tax_type_id' => function () {
            return factory(TaxType::class)->create()->id;
        },
        'percent' => function (array $item) {
            return TaxType::find($item['tax_type_id'])->percent;
        },
        'name' => function (array $item) {
            return TaxType::find($item['tax_type_id'])->name;
        },
        'company_id' => User::find(1)->company_id,
        'amount' => $faker->randomDigitNotNull
    ];
});
