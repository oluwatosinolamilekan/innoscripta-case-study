<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AuthResource extends JsonResource
{
    /**
     * @var string
     */
    private string $token;
    
    /**
     * @var string
     */
    private string $message;

    /**
     * Create a new resource instance.
     *
     * @param  mixed  $resource
     * @param  string  $token
     * @param  string  $message
     * @return void
     */
    public function __construct($resource, string $token, string $message = '')
    {
        parent::__construct($resource);
        $this->token = $token;
        $this->message = $message;
    }

    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     */
    public function toArray($request): array
    {
        $response = [
            'user' => new UserResource($this->resource),
            'access_token' => $this->token,
            'token_type' => 'Bearer',
        ];
        
        if (!empty($this->message)) {
            $response['message'] = $this->message;
        }
        
        return $response;
    }
}
