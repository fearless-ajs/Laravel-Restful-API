<?php

namespace App\Providers;

use App\Mail\UserCreated;
use App\Mail\UserMailChanged;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // Set default string length for database
        \Schema::defaultStringLength(191);

        // When a new user is created
        User::created(function ($user){
            // retry( is used for handling communication that could fail)
            retry(5, function () use ($user) {
                Mail::to($user->email)->send(new UserCreated($user));
            });
        });

        // When a user mail is changed
        User::updated(function ($user){
            if ($user->isDirty('email')){
                retry(5, function () use ($user) {
                    Mail::to($user->email)->send(new UserMailChanged($user));
                });
            }
        });

        Product::updated(function ($product) {
            if ($product->quantity == 0 && $product->isAvailable()){
                $product->status = Product::UNAVAILABLE_PRODUCT;

                $product->save();
            }
        });
    }
}
