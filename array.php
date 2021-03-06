<?php
require_once __DIR__ . '/lambda.php';

class Collection extends ArrayObject
{
    function map($fn)
    {
        $arr = [];
        if (!is_callable($fn)) {
            $fn = Lambda::create('$_', $fn, true);
        }
        foreach ($this as $i => $v) {
            $arr[$i] = $fn($v);
        }
        return new self($arr);
    }

    function filter($fn)
    {
        if (!is_callable($fn)) {
            $fn = Lambda::create('$_', $fn, true);
        }
        return new self(array_filter((array)$this, $fn));
    }


    function __call($method, $args)
    {
        $method = preg_replace('/[A-Z]/', '_\0', $method);
        $func = 'array_' . $method;
        if (!function_exists($func)) {
            throw new \BadMethodCallException("func is not exists.");
        }

        foreach ($args as $i => $a) {
            if ($a instanceof self) {
                $args[$i] = (array)$a;
            }
        }

        $args = array_merge([(array)$this], $args);
        $res = call_user_func_array($func, $args);

        if (is_array($res)) {
            return new self($res);
        } else {
            return $res;
        }
    }
}
