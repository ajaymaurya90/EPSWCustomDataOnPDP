<?php

declare(strict_types=1);

namespace EPSWCustomDataOnPDP;

use Shopware\Core\Framework\App\Manifest\Xml\CustomFieldTypes\CustomFieldType;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsAnyFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\IdSearchResult;
use Shopware\Core\Framework\Plugin;
use Shopware\Core\Framework\Plugin\Context\InstallContext;
use Shopware\Core\Framework\Plugin\Context\UnInstallContext;
use Shopware\Core\System\CustomField\CustomFieldTypes;

class EPSWCustomDataOnPDP extends Plugin
{

    const CUSTOM_FIELD_SET = 'epsw_product_detail_media';
    public function install(InstallContext $installContext): void
    {
        $context = $installContext->getContext();
        $customFieldSetRepository = $this->container->get('custom_field_set.repository');

        if($this->ifCustomFieldSetExist($customFieldSetRepository, $context))
        {
            $customFieldSetRepository->create([
                [
                    'name' => self::CUSTOM_FIELD_SET,
                    'relation' => [
                                [
                                    'entityName' => 'product'
                                ]
                            ],
                    'config' => [
                        'label' => [
                            'en-GB' => 'Product Detail Page',
                            'de-DE' => 'Produkt Detail Seite',
                        ]
                    ],
                    'customFields' => [
                        [
                            'name' => self::CUSTOM_FIELD_SET.'_youtube_video',
                            'type' => CustomFieldTypes::TEXT,
                            'config' => [
                                'type' => CustomFieldTypes::TEXT,
                                'label' => [
                                    'en-GB' => 'Youtube Video Url',
                                    'de-DE' => 'Youtube Video Url'
                                ]
                            ]
                        ],
                        [
                            'name' => self::CUSTOM_FIELD_SET.'_normal_video',
                            'type' => 'media',
                            'config' => [
                                'type' => 'media',
                                'componentName' => 'sw-media-field',
                                'label' => [
                                    'en-GB' => 'Video Url',
                                    'de-DE' => 'Video Url'
                                ]
                             ]
                        ],
                        [
                            'name' => self::CUSTOM_FIELD_SET.'_image',
                            'type' => 'media',
                            'config' => [
                                'type' => 'media',
                                'componentName' => 'sw-media-field',
                                'label' => [
                                    'en-GB' => 'Image',
                                    'de-DE' => 'Foto'
                                ]
                            ]
                        ]
                    ]
                ]
            ], $context);
        }
    }

    public function uninstall(UnInstallContext $uninstallContext): void
    {
        parent::uninstall($uninstallContext);
        if($uninstallContext->keepUserData())
        {
            return;
        }

        $context = $uninstallContext->getContext();

        $this->removeCustomFieldSet($context);

    }

    /**
     * To check custom field exist or not
     *
     * @param [type] $customeFieldRepository
     * @param [type] $context
     * @return boolean
     */
    public function ifCustomFieldSetExist($customFieldSetRepository, $context): bool
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('name', self::CUSTOM_FIELD_SET));

        if($customFieldSetRepository->search($criteria, $context)->getTotal() == 0)
        {
            return true;
        }

        return false;
    }

    /**
     * To remove custom field set
     *
     * @param [type] $context
     * @return void
     */
    public function removeCustomFieldSet($context): void
    {
        $customFieldSetRepository = $this->container->get('custom_field_set.repository');
        $mediaSet = $this->customFieldExist($customFieldSetRepository, $context);
        if($mediaSet)
        {
            $customFieldSetRepository->delete(array_values($mediaSet->getData()), $context);
        }
    }

    /**
     * to get custom Field Id's if exist else null
     *
     * @param [type] $customFieldSetRepository
     * @param [type] $context
     * @return IdSearchResult|null
     */
    public function customFieldExist($customFieldSetRepository, $context): ?IdSearchResult
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsAnyFilter('name', [self::CUSTOM_FIELD_SET]));
        $ids = $customFieldSetRepository->searchIds($criteria, $context);

        return $ids->getTotal() > 0 ? $ids : null;
    }
}
