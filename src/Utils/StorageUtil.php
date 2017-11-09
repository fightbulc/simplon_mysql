<?php

namespace Simplon\Mysql\Utils;

use Simplon\Helper\SecurityUtil;
use Simplon\Mysql\Crud\CrudStoreInterface;
use Simplon\Mysql\QueryBuilder\CreateQueryBuilder;
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

    /**
     * @param CreateQueryBuilder $builder
     * @param string $token
     *
     * @return CreateQueryBuilder
     */
    public static function autosetEmptyToken(CreateQueryBuilder $builder, string $token): CreateQueryBuilder
    {
        $model = $builder->getModel();

        if (method_exists($model, 'getToken') && method_exists($model, 'setToken'))
        {
            if (!$model->getToken())
            {
                $builder->setModel(
                    $model->setToken($token)
                );
            }
        }

        return $builder;
    }
}