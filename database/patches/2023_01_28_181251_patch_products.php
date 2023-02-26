<?php

use App\Models\Product;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $products = [
            [
                //   test             'price_id'=>'price_1LgCQbAe7hH7XTwzXnqkkXoG',
                'price_id' => 'price_1LgdmeAe7hH7XTwzzoyACnwt',
                'image_url' => '/images/vidcoins/ArmfulOfCoins.png',
                'description' => 'Give 2 Platinum, or 5 Gold, or 10 Silver Awards.',
                'name'=> '5,000 VidCoins',
                'price'=> 599
            ],
            [
                //   test             'price_id'=>'price_1LgSWFAe7hH7XTwzoakAGX9a',
                'price_id' => 'price_1LgdodAe7hH7XTwz97hyZqcG',
                'image_url' => '/images/vidcoins/DraggingSack.png',
                'description' => 'Give 12 Platinum, or 25 Gold, or 50 Silver Awards.',
                'name'=> '25,000 VidCoins',
                'price'=> 2499
            ],
            [
                //   test             'price_id'=>'price_1LgSlLAe7hH7XTwzMXBu8lOX',
                'price_id' => 'price_1LgdpDAe7hH7XTwzuXViqe2F',
                'image_url' => '/images/vidcoins/PushingCrate.png',
                'description' => 'Give 50 Platinum, or 100 Gold, or 200 Silver Awards.',
                'name'=> '100,000 VidCoins',
                'price'=> 7999
            ],
        ];

        foreach($products as $product) {
            $existingProduct = Product::where('price_id', $product['price_id'])->first();
            if (!$existingProduct) {
                Product::forceCreate(
                    [
                        'price_id' => $product['price_id'],
                        'image_url' => $product['image_url'],
                        'description' => $product['description'],
                        'name'=> $product['name'],
                        'price'=> $product['price']
                    ]
                );
            }
        }

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
};
