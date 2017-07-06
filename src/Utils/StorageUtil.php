<?php

namespace Simplon\Mysql\Utils;

use Simplon\Helper\SecurityUtil;
use Simplon\Mysql\Crud\CrudStoreInterface;
use Simplon\Mysql\QueryBuilder\ReadQueryBuilder;

/**
 * @package Simplon\Mysql\Utils
 */
class StorageUtil
{
    /**
     * @param CrudStoreInterface $storage
     * @param string $tokenColumnName
     * @param int $length
     * @param string $prefix
     *
     * @return string
     */
    public static function getUniqueToken(CrudStoreInterface $storage, string $tokenColumnName = 'token', int $length = 12, ?string $prefix = null)
    {
        $token = null;
        $isUnique = false;
        $characters = SecurityUtil::TOKEN_UPPERCASE_LETTERS_NUMBERS;

        while ($isUnique === false)
        {
            $token = SecurityUtil::createRandomToken($length, $prefix, $characters);
            $isUnique = $storage->readOne((new ReadQueryBuilder())->addCondition($tokenColumnName, $token)) === null;
        }

        return $token;
    }
}