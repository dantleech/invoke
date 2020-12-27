<?php

namespace DTL\Invoke\Internal;

class ArgumentValue
{
    /**
     * @param mixed $value
     */
    public static function resolveInternalTypeName($value): string
    {
        $type = gettype($value);

        if ($type === 'double') {
            return 'float';
        }

        if ($type === 'integer') {
            return 'int';
        }

        if ($type === 'boolean') {
            return 'bool';
        }

        return $type;
    }
}
