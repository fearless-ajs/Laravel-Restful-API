<?php

namespace App\Exceptions;

use App\Traits\ApiResponser;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    use ApiResponser;
    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     * Overrides the parent render function
     *
     * @return Response
     * @throws Throwable
     */

    public function render($request, Throwable $e)
    {
        if ($e instanceof ValidationException){
            return $this->convertValidationExceptionToResponse($e, $request);
        }

        if ($e instanceof ModelNotFoundException) {
            $modelName = strtolower(class_basename($e->getModel()));
            return $this->errorResponse("$modelName with the specified identifier does not exist", 404);
        }

        if ($e instanceof AuthenticationException){
            return $this->unauthenticated($request, $e);
        }

        if ($e instanceof AuthorizationException){
            return $this->errorResponse($e->getMessage(), 403);
        }

        if ($e instanceof MethodNotAllowedHttpException){
            return $this->errorResponse('Method specified for this route is invalid', 405);
        }

        if ($e instanceof NotFoundHttpException){
            return $this->errorResponse('The specified URL does not exist', 404);
        }

        // Defining other http exceptions that this restful API could have
        if ($e instanceof HttpException){
            return $this->errorResponse($e->getMessage(), $e->getStatusCode());
        }

        // For database foreign key related conflict
        // 409 means conflict
        if ($e instanceof QueryException){
            $errorCode  = $e->errorInfo[1];
            if ($errorCode == 1451){
                return $this->errorResponse('Cannot permanently remove the resource, it is related with another resource', 409);
            }
        }

        // For any Form of unexpected exception
        // If app is in debug mode, show details of the error
        if (config('app.debug')){
            return parent::render($request, $e);
        }
        return $this->errorResponse('Unexpected exception, try again later', 500);

    }

    public function register()
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }


    /**
     * Create a response object from the given validation exception.
     *
     * @param  \Illuminate\Validation\ValidationException  $e
     * @param  Request  $request
     * @return Response
     */
    protected function convertValidationExceptionToResponse(ValidationException $e, $request)
    {
       $errors = $e->validator->errors()->getMessages();
       return $this->errorResponse($errors, 422);
    }

    /**
     * Convert an authentication exception into a response.
     *
     * @param  Request  $request
     * @param AuthenticationException $exception
     * @return Response
     */
    protected function unauthenticated($request, AuthenticationException $exception)
    {
        return $this->errorResponse('Unauthenticated', 401);
    }
}
