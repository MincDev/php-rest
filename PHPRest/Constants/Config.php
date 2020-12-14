<?php

namespace PHPRest\Constants;

class Config 
{
    /**
     * The key name for the element containing the boolean status of the response.
     * @var string
     */
    public const RESPONSE_STATUS_KEY    = "successful";

    /**
     * The key name for the element containing the error message of an unsuccessful response.
     * @var string
     */
    public const RESPONSE_MESSAGE_KEY   = "error";

    /**
     * If set to true, the API will display a sandbox warning when self::IS_SANDBOX is toggled to true
     * @var boolean
     */
    public const DISPLAY_SANDBOX_WARNING = true;

    /**
     * Whether or not the API framework is on sandbox mode. 
     * @var boolean
     */
    public const IS_SANDBOX = true;

    /**
     * The message to display when sandbox mode is enabled.
     * @var string
     */
    public const SANDBOX_WARNING_MESSAGE = "You are in Sandbox Mode. Transaction may be simulated.";

    /**
     * If enabled, the API will be able to make use of basic authentication.
     * @var boolean
     */
    public const BASIC_AUTHENTICATION_ENABLED = true;
}