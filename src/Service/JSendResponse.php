<?php

namespace Loyals\JSend\Service;

use Loyals\JSend\Exceptions\JSendParseException;
use Exception;

/**
 * Generate a proper json response for (ajax) communication
 *
 * @Author Martijn Schenk
 * @Alias  Chibby
 * @Email  martijnschenk@loyals.nl
 */
class JSendResponse
{
    /**
     * Constant statusses
     */
    const STATUS_SUCCESS = 'success';
    const STATUS_FAIL    = 'fail';
    const STATUS_ERROR   = 'error';

    /**
     * Status of the response
     *
     * @var string
     */
    private $status;

    /**
     * Data of the response
     *
     * @var object|array
     */
    private $data;

    /**
     * Code of the response
     *
     * @var int
     */
    private $code;

    /**
     * Message of the response
     *
     * @var string
     */
    private $message;

    /**
     * Parse a JSend response json
     *
     * @param $json
     *
     * @return static
     * @throws JSendParseException
     */
    public static function parse($json)
    {
        $json = json_decode($json);

        if (!isset($json->status)) {
            throw new JSendParseException('Could not parse data; required element \'status\' missing.');
        }

        $instance = new static($json->status);

        if (!isset($json->code)) {
            $json->code = 200;
        }
        $instance->setCode($json->code);

        if ($instance->isError()) {
            if (!isset($json->message)) {
                throw new JSendParseException('Could not parse data; required element \'message\' missing.');
            }
            $instance->setMessage($json->message);
        }

        if ($instance->isSuccess() || $instance->isFail()) {
            if (!isset($json->data)) {
                throw new JSendParseException('Could not parse data; required element \'data\' missing.');
            }
            $instance->setData($json->data);
        } else {
            if ($instance->isError()) {
                if (isset($json->data)) {
                    $instance->setData($json->data);
                }
            }
        }

        return $instance;
    }

    /**
     * JSendResponse constructor.
     *
     * @param string            $status
     * @param object|array|null $data
     * @param int|null          $code
     * @param string            $message
     */
    public function __construct($status = self::STATUS_SUCCESS, $data = null, $code = null, $message = '')
    {
        $this->setStatus($status);
        if ($data) {
            $this->setData($data);
        }
        if ($code) {
            $this->setCode($code);
        }
        if ($message) {
            $this->setMessage($message);
        }
    }

    /**
     * Retrieve the json response
     *
     * @return string
     */
    public function getJson()
    {
        $response = [
            'status' => $this->status,
        ];

        switch ($this->status) {
            case self::STATUS_SUCCESS:
            case self::STATUS_FAIL:
                $response = array_merge(
                    $response,
                    [
                        'data' => $this->data,
                    ]
                );
                break;
            case self::STATUS_ERROR;
                $code     = $this->code ? ['code' => $this->code] : [];
                $data     = $this->data ? ['data' => $this->data] : [];
                $response = array_merge(
                    $response,
                    [
                        'message' => $this->message,
                    ],
                    $code,
                    $data
                );
                break;
            default:
                // because of the check in setStatus, this should never occur
                $response = [
                    'status'  => self::STATUS_ERROR,
                    'code'    => 500,
                    'message' => 'JSend response was built illegally',
                    'data'    => [
                        'original-data' => [
                            'status'  => $this->status,
                            'code'    => $this->code,
                            'message' => $this->message,
                            'data'    => $this->data,
                        ],
                    ],
                ];
                break;
        }

        return json_encode($response);
    }

    /**
     * Set the status for this response
     *
     * @param string $status
     *
     * @throws \Exception
     * @return $this
     */
    public function setStatus($status)
    {
        if (!in_array($status, $this->getStatusses())) {
            throw new Exception(sprintf('Illegal status %1$s given. Must be one of %2$s', $status, implode(', ', $this->getStatusses())));
        }

        $this->status = $status;

        return $this;
    }

    /**
     * Retrieve the status for this response
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Is the status for this response success?
     *
     * @return bool
     */
    public function isSuccess()
    {
        return $this->status == static::STATUS_SUCCESS;
    }

    /**
     * Is the status for this response fail?
     *
     * @return bool
     */
    public function isFail()
    {
        return $this->status == static::STATUS_FAIL;
    }

    /**
     * Is the status for this response error?
     *
     * @return bool
     */
    public function isError()
    {
        return $this->status == static::STATUS_ERROR;
    }

    /**
     * Set the data for this response
     *
     * @param object|array $data
     *
     * @return $this
     */
    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Retrieve the data for this response
     *
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Does this response have data?
     *
     * @return bool
     */
    public function hasData()
    {
        return !empty($this->data);
    }

    /**
     * Set the code for this response
     *
     * @param int $code
     *
     * @return $this
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * Retrieve the code for this response
     *
     * @return int
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Set the message for this response
     *
     * @param string $message
     *
     * @return $this
     */
    public function setMessage($message)
    {
        $this->message = $message;

        return $this;
    }

    /**
     * Retrieve the message for this response
     *
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Does this response have a message?
     *
     * @return bool
     */
    public function hasMessage()
    {
        return !empty($this->message);
    }

    /**
     * Retrieve the statusses this response allows
     *
     * @return array
     */
    public function getStatusses()
    {
        return [
            self::STATUS_SUCCESS,
            self::STATUS_FAIL,
            self::STATUS_ERROR,
        ];
    }
}
