<?php

namespace Zl\Common\Exception;

use Exception;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\ValidationException;
use Zl\Common\Exception\Exceptions\BusinessException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
//use YiluTech\MicroApi\MicroApiRequestException;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Exception  $exception
     * @return void
     */
    public function report(Exception $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $exception
     * @return \Illuminate\Http\Response
     */

    public function render($request, Exception $e)
    {

        if (method_exists($e, 'render') && $response = $e->render($request)) {
            return Router::toResponse($request, $response);
        } elseif ($e instanceof Responsable) {
            return $e->toResponse($request);
        }

        $e = $this->prepareException($e);
        

        if ($e instanceof HttpResponseException) {
            return $e->getResponse();
        }
        elseif ($e instanceof AuthenticationException) {
            return $this->unauthenticated($request, $e);
        }
        elseif ($e instanceof ValidationException) {
            return response()->json([
                'type' => 'validation_error',
                'data' => $e->validator->errors()->getMessages()
            ], 422);
        }
        elseif ($request->expectsJson() && $e instanceof NotFoundHttpException){
            return response()->json([
                'message' => 'Sorry, the page you are looking for could not be found.',
            ], 404);
        }
        elseif ($e instanceof BusinessException) {  //业务错误
            return $e->getResponse();
        }
//        elseif ($e instanceof MicroApiRequestException){
//            return response()->json([
//                'message' => $e->getMessage(),
//                'exception' => get_class($e),
//                'file' => $e->getFile(),
//                'line' => $e->getLine(),
//                'body' => $e->getData()
//            ], 502);
//        }

        return $request->expectsJson()
            ? $this->prepareJsonResponse($request, $e)
            : $this->prepareResponse($request, $e);

    }

}
