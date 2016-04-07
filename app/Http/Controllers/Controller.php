<?php

namespace App\Http\Controllers;

use League\Fractal;
use App\User;
use Predis\Client as Redis;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Auth\Passwords\DatabaseTokenRepository;
use Laravel\Lumen\Routing\Controller as BaseController;

class Controller extends BaseController
{
    /**
     * Make fractal response
     *
     * @param  mixed  $data
     * @param  string $class
     * @return array
     */
    protected function transform($data, $class)
    {
        $fractal  = new Fractal\Manager();
        $resource = $this->formatResource(
            $data,
            $this->getTransformerClass($class)
        );

        return $fractal->createData($resource)->toArray();
    }

    /**
     * Format resource for item with fractal transformer
     *
     * @param  mixed                       $data
     * @param  Fractal\TransformerAbstract $transformer
     * @return mixed
     */
    private function formatResource($data, Fractal\TransformerAbstract $transformer)
    {
        if ($data instanceof Model)
        {
            return new Fractal\Resource\Item($data, $transformer);
        }

        $resource = new Fractal\Resource\Collection($data, $transformer);

        if ($data instanceof LengthAwarePaginator)
        {
            $resource->setPaginator(new Fractal\Pagination\IlluminatePaginatorAdapter($data));
        }

        return $resource;
    }

    /**
     * Instance of Transformer Model
     *
     * @param  string $class
     * @return League\Fractal\TransformerAbstract
     */
    private function getTransformerClass($class)
    {
        $transformer = "App\Transformers\\" . class_basename($class) . "Transformer";

        return new $transformer;
    }

    /**
     * {@inheritDoc}
     */
    public function validate(Request $request, array $rules, array $messages = [], array $customAttributes = [])
    {
        $validation = app('validator')->make($request->all(), $rules, $messages, $customAttributes);

        if ( ! $validation->passes())
        {
             abort(Response::HTTP_BAD_REQUEST, $validation->errors());
        }
    }

    /**
     * TokenAbstract Instance
     *
     * @param  array  $params
     * @return Illuminate\Auth\Passwords\DatabaseTokenRepository
     */
    protected function resetter(array $params)
    {
        return new DatabaseTokenRepository(
            app('db')->connection(),
            $params['table'],
            env('APP_KEY'),
            $params['expire']
        );
    }

    /**
     * Retrive user instance
     *
     * @param  mixed  $param
     * @param  string $field
     * @return App\User
     */
    protected function getUser($param, $field = 'id')
    {
        $user = User::where($field, $param)->first();

        if ( ! $user) abort(Response::HTTP_NOT_FOUND, 'user does not exist');

        return $user;
    }

    /**
     * Generate token
     *
     * @param  integer $length
     * @return string
     */
    protected function token($length = 40)
    {
        return str_random($length);
    }

    /**
     * Redis client
     *
     * @return Predis\Client
     */
    protected function redis()
    {
        $config = config('database.redis.default');

        return new Redis([
            'host' => $config['host'],
            'port' => $config['port'],
        ]);
    }
}
