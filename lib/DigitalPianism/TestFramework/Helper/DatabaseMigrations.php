<?php

trait DigitalPianism_TestFramework_Helper_DatabaseMigrations
{
    public function runDatabaseMigrations()
    {
        $this->addSetUpHook(50, 'begin-database-transaction', function () {
            Mage::getSingleton('core/resource')->getConnection('default_write')->beginTransaction();
        });

        $this->addTearDownHook(5, 'rollback-database-transaction', function () {
            Mage::getSingleton('core/resource')->getConnection('default_write')->rollBack();
        });
    }
}
