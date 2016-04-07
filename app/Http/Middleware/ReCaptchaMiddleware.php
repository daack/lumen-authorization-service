<?php

namespace App\Http\Middleware;

use GuzzleHttp;
use Closure;
use Symfony\Component\HttpFoundation\Response;

class ReCaptchaMiddleware
{
    /**
     * Check ReCaptcha in incoming request
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (app()->environment() != 'production')
        {
            return $next($request);
        }

        $params = [
            'secret'   => env('RECAPTCHA_SECRET'),
            'response' => $request->input('g-recaptcha-response'),
            'remoteip' => $request->ip()
        ];

        $data = $this->request(new GuzzleHttp\Client, $params);

        if ( ! $data->success)
        {
            abort(Response::HTTP_BAD_REQUEST, 'Gatorade me bitch!');
        }

        return $next($request);
    }

    /**
     * Made request to google
     *
     * @param  GuzzleHttp\Client $client
     * @param  array             $params
     * @return stdClass
     */
    private function request(GuzzleHttp\Client $client, $params)
    {
        $response = $client->request(
            'POST',
            'https://www.google.com/recaptcha/api/siteverify',
            $params
        );

        return json_decode($response->getBody());
    }
}
