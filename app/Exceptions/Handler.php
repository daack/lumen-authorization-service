<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Laravel\Lumen\Exceptions\Handler as ExceptionHandler;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        AuthorizationException::class,
        HttpException::class,
        ModelNotFoundException::class,
        ValidationException::class,
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Exception  $e
     * @return void
     */
    public function report(Exception $e)
    {
        parent::report($e);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $e
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $e)
    {
        if ($e instanceof HttpException)
        {
            return $this->handleHttpException($e);
        }

        return parent::render($request, $e);
    }

    /**
     * Handle the response for HttpException
     *
     * @param  HttpException $e
     * @return Illuminate\Http\Response
     */
    protected function handleHttpException(HttpException $e)
    {
        $message = $e->getMessage();
        $decoded = json_decode($message);

        if (json_last_error() == JSON_ERROR_NONE)
        {
            $message = $decoded;
        }

        return response([
            'status'  => $e->getStatusCode(),
            'message' => $message,
        ],  $e->getStatusCode());
    }
}
