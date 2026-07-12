<? if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

/**
 * @var CBitrixComponentTemplate $this
 * @var CatalogSectionComponent $component
 */

$component = $this->getComponent();
$arParams = $component->applyTemplateModifications();

$arResult['FIELDS'] = [
    'Наименование товара, <span class="min">размер (мм)</span>',
    'Марка Стали',
    'Вес пм',
    'Цена руб/кг <span class="min">(с НДС)</span>',
    'Порезка, руб',
    'Итог',
    'Купить',
];

if(count($arResult['PATH']) > 0){
    foreach (array_reverse($arResult['PATH']) as $section){
        if($curSec = CIBlockSection::GetList(Array( $by => $order), Array('IBLOCK_ID' => $section['IBLOCK_ID'], 'ID' => $section['ID']), false, array('NAME','UF_FIELD_TABLE'))->GetNext())
            if($curSec['UF_FIELD_TABLE']){
                foreach ($arResult['FIELDS'] as $key => $field){
                    if(strlen(trim($curSec['UF_FIELD_TABLE'][$key]))){
                        $arResult['FIELDS'][$key] = $curSec['UF_FIELD_TABLE'][$key];
                    }
                }
                break;
            }
    }
}
