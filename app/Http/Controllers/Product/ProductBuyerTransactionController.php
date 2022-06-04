<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\ApiController;
use App\Models\Buyer;
use App\Models\Product;
use App\Models\Seller;
use App\Models\Transaction;
use App\Models\User;
use App\Transformers\TransactionTransformer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductBuyerTransactionController extends ApiController
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware('transform.input:' . TransactionTransformer::class)->only(['store']);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @param Product $product
     * @param Buyer $buyer
     * @param User $seller
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request, Product $product, User $buyer)
    {
        $rules = [
          'quantity'  => 'required|integer|min:1'
        ];
       $this->validate($request, $rules);

        if ($buyer->id == $product->seller_id){
            return $this->errorResponse('The buyer must be diffeent from the seller', 409);
        }

        if (!$buyer->isVerified()){
            return $this->errorResponse('The buyer must be a verified user', 409);
        }

        if (!$product->seller->isVerified()){
            return $this->errorResponse('The seller must be a verified user', 409);
        }

        if (!$product->isAvailable()){
            return $this->errorResponse('The product is not available', 409);
        }

        if ($product->quantity < $request->quantity){
            return $this->errorResponse('This product does not have enough unit for this transaction', 409);
        }


        // So that the db rolls back all changes if there is a failure, we use db facade
        return DB::transaction(function () use ($request, $product, $buyer) {
            $product->quantity -= $request->quantity;
            $product->save();

            $transaction = Transaction::create([
               'quantity' => $request->quantity,
               'buyer_id' => $buyer->id,
               'product_id' => $buyer->id,
            ]);

            return $this->showOne($transaction, 201);
        });

    }


}
