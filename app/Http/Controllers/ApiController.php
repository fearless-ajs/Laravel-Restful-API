<?php

namespace App\Http\Controllers;

use App\Traits\ApiResponser;
use App\Transformers\CategoryTransformer;
use Illuminate\Http\Request;

class ApiController extends Controller
{
    public function __construct()
    {

    }
    use ApiResponser;

}
