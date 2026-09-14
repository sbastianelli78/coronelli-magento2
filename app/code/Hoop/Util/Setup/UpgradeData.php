<?php
namespace Hoop\Util\Setup;

use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Sales\Setup\SalesSetupFactory;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Eav\Model\Config;

class UpgradeData implements UpgradeDataInterface
{
    /**
     * Sales setup factory
     *
     * @var SalesSetupFactory
     */
    private $salesSetupFactory;
    private $eavSetupFactory;
    private $eavConfig;

    /**
     * @param SalesSetupFactory $salesSetupFactory
     */
    public function __construct(
        SalesSetupFactory $salesSetupFactory,
        EavSetupFactory $eavSetupFactory,
        Config $eavConfig
    ) {
        $this->salesSetupFactory = $salesSetupFactory;
        $this->eavSetupFactory = $eavSetupFactory;
        $this->eavConfig = $eavConfig;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        /** @var \Magento\Sales\Setup\SalesSetup $salesSetup */
        $salesSetup = $this->salesSetupFactory->create(['setup' => $setup]);

        if ($context->getVersion() && version_compare($context->getVersion(), '1.0.3') < 0) {
            $attributes = [
                'exported' =>['type' =>'varchar','visible' => false, 'required' => false],
            ];

            foreach ($attributes as $attributeCode => $attributeParams) {
                $salesSetup->addAttribute('order', $attributeCode, $attributeParams);
            }
        }

        if ($context->getVersion() && version_compare($context->getVersion(), '1.0.4') < 0) {
            $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
            $eavSetup->addAttribute(
                \Magento\Customer\Model\Customer::ENTITY,
                'fiscalcode',
                [
                    'type' => 'varchar',
                    'label' => 'Codice fiscale',
                    'input' => 'text',
                    'default' => '',
                    'sort_order' => 100,
                    'system' => false,
                    'position' => 100
                ]
            );
            $fiscalDataAttr = $this->eavConfig->getAttribute(\Magento\Customer\Model\Customer::ENTITY, 'fiscalcode');
            $fiscalDataAttr->setData(
                'used_in_forms',
                ['adminhtml_customer', 'customer_register_address']
            );
            $fiscalDataAttr->save();
        }

        if ($context->getVersion() && version_compare($context->getVersion(), '1.0.5') < 0) {
            $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
            $eavSetup->updateAttribute(
                \Magento\Customer\Model\Customer::ENTITY,
                'fiscalcode',
                [
                    'type' => 'varchar',
                    'label' => 'Codice fiscale',
                    'input' => 'text',
                    'default' => '',
                    'sort_order' => 100,
                    'system' => false,
                    'position' => 100
                ]
            );
            $fiscalDataAttr = $this->eavConfig->getAttribute(\Magento\Customer\Model\Customer::ENTITY, 'fiscalcode');
            $fiscalDataAttr->setData(
                'used_in_forms',
                ['adminhtml_customer', 'checkout_register', 'customer_account_create', 'customer_account_edit']
            );
            $fiscalDataAttr->save();
        }

    }
}