<?php

/**
 * Envios Kanguro Shipping
 *
 * @author Javier Telio Z <jtelio118@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 */

namespace Envioskanguro\Shipping\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\DB\Adapter\AdapterInterface;

use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\InstallSchemaInterface;

class InstallSchema implements InstallSchemaInterface
{
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

        $connection = $installer->getConnection();
        $tableName = $setup->getTable('envioskanguro_shipping_rates');

        if (!$installer->tableExists('envioskanguro_shipping_rates')) {

            $table = $connection
                ->newTable($tableName)
                ->addColumn(
                    'rate_id',
                    Table::TYPE_INTEGER,
                    null,
                    [
                        'identity' => true,
                        'nullable' => false,
                        'primary'  => true,
                        'unsigned' => true,
                    ],
                    'Rate ID'
                )
                ->addColumn(
                    'quote_id',
                    Table::TYPE_INTEGER,
                    null,
                    [
                        'nullable' => false,
                        'unsigned' => true,
                    ],
                    'Quote ID'
                )
                ->addColumn(
                    'session_id',
                    Table::TYPE_INTEGER,
                    null,
                    [
                        'nullable' => false,
                        'unsigned' => true,
                    ],
                    'Card ID'
                )
                ->addColumn(
                    'content',
                    Table::TYPE_TEXT,
                    '64k',
                    [],
                    'Rate Serializer'
                )
                ->addColumn(
                    'created_at',
                    Table::TYPE_TIMESTAMP,
                    null,
                    ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
                    'Created At'
                )->addColumn(
                    'updated_at',
                    Table::TYPE_TIMESTAMP,
                    null,
                    ['nullable' => false, 'default' => Table::TIMESTAMP_INIT_UPDATE],
                    'Updated At'
                )
                ->setComment('Post Table');
            $installer->getConnection()->createTable($table);

            $installer->getConnection()->addIndex(
                $tableName,
                $setup->getIdxName(
                    $tableName,
                    ['content'],
                    AdapterInterface::INDEX_TYPE_FULLTEXT
                ),
                ['content'],
                AdapterInterface::INDEX_TYPE_FULLTEXT
            );
        }
        $installer->endSetup();
    }
}
