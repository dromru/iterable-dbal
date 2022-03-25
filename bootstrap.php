<?php /** @noinspection ALL */

require_once __DIR__ . '/vendor/autoload.php';

if (!\class_exists(\Doctrine\DBAL\ParameterType::class)) {
    class DummyParameterType {
        public const INTEGER = 'dummy';
    }

    class_alias(DummyParameterType::class, \Doctrine\DBAL\ParameterType::class);
}

if (!\class_exists(\Doctrine\DBAL\Connection::class)) {
    class DummyConnection {
        public const PARAM_INT_ARRAY = 'dummy';
    }

    class_alias(DummyConnection::class, \Doctrine\DBAL\Connection::class);
}
