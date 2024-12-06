<?php
namespace IntegrationHelper\ProcessRunner\Api;

interface ConstraintsInterface
{
    public const TABLE = 'integration_process_profile';

    public const CODE = 'code';

    public const IDENTITY = 'identity';

    public const CREATED_AT = 'created_at';

    public const MODEL = 'model';

    public const MESSAGE = 'message';

    public const MODEL_METHOD_ARGUMENTS = 'arguments';

    public const STATUS = 'status';

    public const STATUS_WAITING = 'waiting';

    public const STATUS_PROCESSING = 'processing';

    public const STATUS_COMPLETE = 'complete';

    public const STATUS_ERROR = 'error';
}
