<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\Estimate;
use App\Models\User;
use App\Models\Tax;
use Faker\Generator as Faker;
use App\Models\EstimateItem;
use App\Models\EstimateTemplate;

$factory->define(Estimate::class, function (Faker $faker) {
    return [
        'estimate_date' => $faker->date($format = 'd/m/Y', $max = 'now'),
        'expiry_date' => $faker->date($format = 'd/m/Y', $max = 'now'),
        'estimate_number' => 'EST-'.Estimate::getNextEstimateNumber(),
        'reference_number' => Estimate::getNextEstimateNumber(),
        'company_id' => User::find(1)->company_id,
        'user_id' => function () {
            return factory(User::class)->create(['role' => 'customer'])->id;
        },
        'status' => Estimate::DRAFT,
        'estimate_template_id' => 1,
        'sub_total' => $faker->randomDigitNotNull,
        'discount' => 0,
        'discount_type' => 'fixed',
        'discount_val' => 0,
        'tax_per_item' => 'YES',
        'discount_per_item' => 'No',
        'total' => $faker->randomDigitNotNull,
        'tax' => $faker->randomDigitNotNull,
        'notes' => $faker->text(80),
        'unique_hash' => str_random(60)
    ];
});

$factory->afterCreating(Estimate::class, function ($estimate, $faker) {
    $estimate->items()->save(factory(EstimateItem::class)->make());
    $estimate->items()->save(factory(EstimateItem::class)->make());
});

$factory->afterCreating(Estimate::class, function ($estimate, $faker) {
    $estimate->taxes()->save(factory(Tax::class)->make());
    $estimate->items()->save(factory(Tax::class)->make());
});
