<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

/** @var array $arResult */
/** @var CBitrixComponentTemplate $this */
/** @global CMain $APPLICATION */

$this->setFrameMode(true);

if (empty($arResult['ITEMS'])) {
    return;
}
?>
<section class="main-section main-section--picture">
    <div class="main-slider main-slider--picture">
        <?php foreach ($arResult['ITEMS'] as $arItem):
            $this->AddEditAction($arItem['ID'], $arItem['EDIT_LINK'], CIBlock::GetArrayByID($arItem['IBLOCK_ID'], 'ELEMENT_EDIT'));
            $this->AddDeleteAction(
                $arItem['ID'],
                $arItem['DELETE_LINK'],
                CIBlock::GetArrayByID($arItem['IBLOCK_ID'], 'ELEMENT_DELETE'),
                ['CONFIRM' => GetMessage('CT_BNL_ELEMENT_DELETE_CONFIRM')]
            );

            $desktop = $arItem['SLIDER_IMAGES']['IMG_DESKTOP'] ?? '';
            $tablet = $arItem['SLIDER_IMAGES']['IMG_TABLET'] ?? '';
            $mobile = $arItem['SLIDER_IMAGES']['IMG_MOBILE'] ?? '';
            $link = $arItem['SLIDER_LINK'] ?? '';
            $alt = htmlspecialcharsbx($arItem['NAME']);

            if ($desktop === '') {
                continue;
            }
            ?>
            <div class="main-slide main-slide--picture" id="<?=$this->GetEditAreaId($arItem['ID'])?>">
                <?php if ($link !== ''): ?>
                <a class="main-slide__link" href="<?=htmlspecialcharsbx($link)?>">
                <?php endif; ?>

                    <picture class="main-slide__picture">
                        <source media="(max-width: 767px)" srcset="<?=htmlspecialcharsbx($mobile)?>">
                        <source media="(max-width: 1199px)" srcset="<?=htmlspecialcharsbx($tablet)?>">
                        <img
                            class="main-slide__img"
                            src="<?=htmlspecialcharsbx($desktop)?>"
                            alt="<?=$alt?>"
                            loading="eager"
                            decoding="async"
                        >
                    </picture>

                <?php if ($link !== ''): ?>
                </a>
                <?php endif; ?>

                <?php if (($arItem['PROPERTIES']['ADS']['VALUE_XML_ID'] ?? '') === 'Y'): ?>
                <div class="ads-block ads-block--picture">
                    <?php
                    $APPLICATION->IncludeComponent('prime:ads.btn', '', [
                        'SHOW' => 'Y',
                        'DESCRIPTION' => 'ООО «КОРПОРАЦИЯ МЕТАЛЛИНВЕСТ» <br /> erid:2Wfslghjslfasdgsdfgsdgsdgfsgdgk',
                        'POSITOPN' => 'tooltip-top-right',
                    ]);
                    ?>
                </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
</section>
