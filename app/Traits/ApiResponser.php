<?php


namespace App\Traits;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;

trait ApiResponser
{
    private function successResponse($data, $code){
        return response()->json($data, $code);
    }

    protected function errorResponse($message, $code){
        return response()->json([
            'error' => $message,
            'code'  => $code,
        ], $code);
    }

    protected function showAll(Collection $collection, $code = 200){
       // Perform a fractal transformation on the collection if not empty
        if ($collection->isEmpty()){
            return $this->successResponse([
                'data' => $collection
            ], $code);
        }
        $transformer = $collection->first()->transformer;

        // The sort must be done before transform data method
        $collection = $this->filterData($collection, $transformer);
        $collection = $this->sortData($collection, $transformer);
        $collection = $this->paginate($collection);
        $collection = $this->transformData($collection, $transformer); //Note: transformData returns an array, not a collection
        $collection = $this->cacheResponse($collection);

        return $this->successResponse($collection, $code);
    }

    protected function showOne(Model $instance, $code = 200){
        $transformer = $instance->transformer;
        $instance = $this->transformData($instance, $transformer);
        return $this->successResponse($instance, $code);
    }

    protected function showMessage($message, $code = 200){
        return $this->successResponse([
            'data' => $message
        ], $code);
    }

    protected function paginate(Collection $collection){
        // Attach pagination validation rules
        $rules = [
            'per_page'  =>  'integer|min:2|max:50'
        ];
        Validator::validate(request()->all(), $rules);


        $page  = LengthAwarePaginator::resolveCurrentPage(); // fetch the current page
        $perPage = 15; // Results expected per page
        if (request()->has('per_page')){
            $perPage = (int) request()->per_page;
        }

        $results = $collection->slice(($page - 1) * $perPage, $perPage)->values(); // Divide the results

        $paginated = new LengthAwarePaginator($results, $collection->count(), $perPage, $page, [
            'path'  => LengthAwarePaginator::resolveCurrentPath()
        ]); // returns links to other pages of the collection

        $paginated->appends(request()->all()); // Attach or other request parameters to the links

        return $paginated; // return the paginated data
    }

    protected function filterData(Collection $collection, $transformer){
        foreach (request()->query() as $query => $value){
            $attribute = $transformer::originalAttribute($query);

            if (isset($attribute, $value)){
                $collection = $collection->where($attribute, $value);
            }
        }

        return $collection;
    }

    protected function sortData(Collection $collection, $transformer){
        if (request()->has('sort_by')){
            $attribute  = $transformer::originalAttribute(request()->sort_by);
            $collection = $collection->sortBy->{$attribute};
        }
        return $collection;
    }

    protected function transformData($data, $transformer){
        $transformation = fractal($data, new $transformer);
        return $transformation->toArray();
    }

    protected function cacheResponse($data){
        $url = request()->url();
        $queryParams = request()->query(); //For returning query params

        // The sort the params
        ksort($queryParams);

        $queryString = http_build_query($queryParams);
        $fillUrl = "{$url}?{$queryString}";

        return Cache::remember($fillUrl, 30/60, function () use($data){
            return $data;
        });
    }


}
