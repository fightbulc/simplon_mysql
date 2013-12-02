<?php

    namespace Simplon\Mysql;

    class SqlQueryHelper
    {
        /**
         * @param $fieldName
         * @param array $values
         *
         * @return string
         */
        public static function getInStatementWithIntegers($fieldName, array $values)
        {
            return "{$fieldName} IN (" . join(',', $values) . ")";
        }

        // ##########################################

        /**
         * @param $fieldName
         * @param array $values
         *
         * @return string
         */
        public static function getInStatementWithStrings($fieldName, array $values)
        {
            $_preparedValues = [];

            foreach ($values as $value)
            {
                $_preparedValues[] = "'{$value}'";
            }

            return "{$fieldName} IN (" . join(',', $_preparedValues) . ")";
        }
    }
