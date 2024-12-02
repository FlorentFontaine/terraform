<?php

namespace Helpers;

class URLHelper
{

    /**
     * Ajoute un paramètre à la requête HTTP
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
     * Ajoute un tableau de paramètres à la requête HTTP
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
