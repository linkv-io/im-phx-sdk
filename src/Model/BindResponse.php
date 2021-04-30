<?php

namespace LinkV\IM\Model;

use LinkV\IM\Exception\ResponseException;

/**
 * Class BindResponse
 *
 * @package LinkV\IM\Model
 */
class BindResponse
{
    /**
     * @var string The token.
     */
    protected $token;
    /**
     * @var string The im_token.
     */
    protected $im_token;
    /**
     * @var string The open_id.
     */
    protected $open_id;

    /**
     * Instantiates a new Response super-class object.
     *
     * @param array $data
     *
     * @throws ResponseException
     */
    public function __construct($data)
    {
        $this->token = isset($data['token']) ? $data['token'] : '';
        $this->open_id = isset($data['openId']) ? $data['openId'] : '';
        $this->im_token = isset($data['im_token']) ? $data['im_token'] : '';

        if ($this->token == '' || $this->im_token  == '' || $this->open_id == '') {
            throw new ResponseException("token or im_token or open_id error data:{".json_encode($data)."}");
        }
    }

    /**
     * return token
     *
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * return im_token
     *
     * @return string
     */
    public function getIMToken()
    {
        return $this->im_token;
    }

    /**
     * return open_id
     *
     * @return string
     */
    public function getOpenID()
    {
        return $this->open_id;
    }
}
