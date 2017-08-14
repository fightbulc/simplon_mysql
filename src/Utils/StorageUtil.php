<?php

namespace Simplon\Mysql\Utils;

use Simplon\Helper\SecurityUtil;
use Simplon\Mysql\Crud\CrudStoreInterface;
use Simplon\Mysql\QueryBuilder\ReadQueryBuilder;

class StorageUtil
{
    /**
     * @param CrudStoreInterface $storage
     * @param null|UniqueTokenOptions $options
     *
     * @return string
     */
    public static function getUniqueToken(CrudStoreInterface $storage, ?UniqueTokenOptions $options = null)
    {
        $token = null;
        $isUnique = false;

        if (!$options)
        {
            $options = new UniqueTokenOptions();
        }

        while ($isUnique === false)
        {
            $token = SecurityUtil::createRandomToken($options->getLength(), $options->getPrefix(), $options->getCharacters());

            $query = $options->mergeReadQuery(
                (new ReadQueryBuilder())->addCondition($options->getColumn(), $token)
            );

            $isUnique = $storage->readOne($query) === null;
        }

        return $token;
    }
}