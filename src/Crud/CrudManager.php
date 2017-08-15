<?php

namespace Simplon\Mysql\Crud;

use Simplon\Mysql\Mysql;
use Simplon\Mysql\MysqlException;
use Simplon\Mysql\MysqlQueryIterator;
use Simplon\Mysql\QueryBuilder\CreateQueryBuilder;
use Simplon\Mysql\QueryBuilder\DeleteQueryBuilder;
use Simplon\Mysql\QueryBuilder\ReadQueryBuilder;
use Simplon\Mysql\QueryBuilder\UpdateQueryBuilder;
use Simplon\Mysql\Utils\QueryUtil;

class CrudManager
{
    /**
     * @var Mysql
     */
    protected $mysql;

    /**
     * @param Mysql $mysql
     */
    public function __construct(Mysql $mysql)
    {
        $this->mysql = $mysql;
    }

    /**
     * @return Mysql
     */
    public function getMysql(): Mysql
    {
        return $this->mysql;
    }

    /**
     * @param CreateQueryBuilder $builder
     *
     * @return CrudModelInterface
     * @throws MysqlException
     */
    public function create(CreateQueryBuilder $builder): CrudModelInterface
    {
        $builder->getModel()->beforeSave();

        $insertId = $this->getMysql()->insert(
            $builder->getTableName(),
            $builder->getData(),
            $builder->isInsertIgnore()
        )
        ;

        if ($insertId === false)
        {
            throw new MysqlException('Could not create dataset');
        }

        if (is_bool($insertId) !== true && method_exists($builder->getModel(), 'setId'))
        {
            $builder->getModel()->setId($insertId);
        }

        return $builder->getModel();
    }

    /**
     * @param ReadQueryBuilder $builder
     *
     * @return null|MysqlQueryIterator
     * @throws MysqlException
     */
    public function read(ReadQueryBuilder $builder): ?MysqlQueryIterator
    {
        return $this
            ->getMysql()
            ->fetchRowManyCursor(
                $builder->renderQuery(),
                $this->filterCondsNullValues(
                    $this->filterCondsValues($builder->getConditions())
                )
            )
            ;
    }

    /**
     * @param ReadQueryBuilder $builder
     *
     * @return array|null
     * @throws MysqlException
     */
    public function readOne(ReadQueryBuilder $builder): ?array
    {
        return $this
            ->getMysql()
            ->fetchRow(
                $builder->renderQuery(),
                $this->filterCondsNullValues(
                    $this->filterCondsValues($builder->getConditions())
                )
            )
            ;
    }

    /**
     * @param UpdateQueryBuilder $builder
     *
     * @return CrudModelInterface
     * @throws MysqlException
     */
    public function update(UpdateQueryBuilder $builder): CrudModelInterface
    {
        $builder->getModel()->beforeUpdate();

        $conds = [];
        $condsQuery = null;

        if (!empty($builder->getConditions()))
        {
            $condsQuery = $this->buildCondsQuery($builder->getConditions(), $builder->getCondsQuery());
            $conds = $this->filterCondsNullValues(
                $this->filterCondsValues($builder->getConditions())
            );
        }

        $this->getMysql()->update(
            $builder->getTableName(),
            $conds,
            $builder->getData(),
            $condsQuery
        )
        ;

        return $builder->getModel();
    }

    /**
     * @param DeleteQueryBuilder $builder
     *
     * @return bool
     * @throws MysqlException
     */
    public function delete(DeleteQueryBuilder $builder): bool
    {
        $conds = [];
        $condsQuery = null;

        if (!empty($builder->getConditions()))
        {
            $condsQuery = $this->buildCondsQuery($builder->getConditions(), $builder->getCondsQuery());
            $conds = $this->filterCondsNullValues(
                $this->filterCondsValues($builder->getConditions())
            );
        }

        return $this->getMysql()->delete($builder->getTableName(), $conds, $condsQuery);
    }

    /**
     * @param array $conds
     * @param null|string $condsQuery
     *
     * @return string
     */
    private function buildCondsQuery(array $conds, ?string $condsQuery = null): string
    {
        if ($condsQuery !== null)
        {
            return (string)$condsQuery;
        }

        $condsQueryBuild = QueryUtil::buildCondsQuery($conds);

        return join(' AND ', $condsQueryBuild->getCondPairs());
    }

    /**
     * @param array $conds
     *
     * @return array
     */
    private function filterCondsValues(array $conds): array
    {
        $values = [];

        foreach ($conds as $key => $data)
        {
            $values[$key] = $data['value'];
        }

        return $values;
    }

    /**
     * @param array $filteredCondsValues
     *
     * @return array
     */
    private function filterCondsNullValues(array $filteredCondsValues): array
    {
        $new = [];

        foreach ($filteredCondsValues as $key => $value)
        {
            if ($value !== null)
            {
                $new[$key] = $value;
            }
        }

        return $new;
    }
}