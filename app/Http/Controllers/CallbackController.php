<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use League\OAuth2\Client\Provider\GenericProvider;

class CallbackController extends Controller
{
    /**
     * @var \League\OAuth2\Client\Provider\GenericProvider
     */
    private $provider;

    public function __construct(GenericProvider $genericProvider)
    {
        $this->provider = $genericProvider;
    }

    public function webhook(Request $request, Response $response)
    {
        //TODO: sign check

    }
}
