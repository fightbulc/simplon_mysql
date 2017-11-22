<?php

namespace Simplon\Mysql\Utils;

use Simplon\Mysql\Data\CondsQueryBuild;

class QueryUtil
{
    const OP_NOT = '!';

    /**
     * @param array $conds
     *
     * @return CondsQueryBuild
     */
    public static function buildCondsQuery(array $conds): CondsQueryBuild
    {
        $strippedConds = [];
        $pairs = [];

        foreach ($conds as $key => $data)
        {
            $value = $data['value'];
            $operator = $data['operator'];

            // handle db named columns e.g. "db.id"
            $strippedKey = str_replace('.', '', $key);

            $strippedConds[$strippedKey] = $data;

            // handle only columns (non-column conds are prepend with "_")
            if (substr($key, 0, 1) !== '_')
            {
                $key = strpos($key, '.') !== false ? $key : '`' . $key . '`';
                $query = $key . ' ' . $operator . ' :' . $strippedKey;

                $useNot = null;

                if ($operator === self::OP_NOT)
                {
                    $useNot = ' NOT';
                }

                if ($value === null)
                {
                    $query = $key . $useNot . ' IS NULL';
                }
                elseif (is_array($value))
                {
                    $query = $key . $useNot . ' IN(:' . $strippedKey . ')';
                }

                $pairs[] = $query;
            }
        }

        return new CondsQueryBuild($strippedConds, $pairs);
    }
}