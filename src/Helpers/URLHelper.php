<?php

namespace Helpers;

class URLHelper
{

    /**
     * Ajoute un param�tre � la requ�te HTTP
     *
     * @param array $data
     * @param string $param
     * @param $value
     * @return string
     */
    public static function withParam(array $data, string $param, $value): string
    {
        return http_build_query(array_merge($data, [$param => $value]));
    }

    /**
     * Ajoute un tableau de param�tres � la requ�te HTTP
     *
     * @param array $data
     * @param array $params
     * @return string
     */
    public static function withParams(array $data, array $params): string
    {
        return http_build_query(array_merge($data, $params));
    }
}
