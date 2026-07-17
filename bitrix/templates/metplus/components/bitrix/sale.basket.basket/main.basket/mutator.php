<? if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Localization\Loc;
use Bitrix\Sale\PriceMaths;

/**
 *
 * This file modifies result for every request (including AJAX).
 * Use it to edit output result for "{{ mustache }}" templates.
 *
 * @var array $result
 */

$mobileColumns = isset($this->arParams['COLUMNS_LIST_MOBILE'])
	? $this->arParams['COLUMNS_LIST_MOBILE']
	: $this->arParams['COLUMNS_LIST'];
$mobileColumns = array_fill_keys($mobileColumns, true);

$result['BASKET_ITEM_RENDER_DATA'] = array();

foreach ($this->basketItems as $row)
{
	$rowData = array(
		'ID' => $row['ID'],
		'PRODUCT_ID' => $row['PRODUCT_ID'],
		'NAME' => isset($row['~NAME']) ? htmlspecialchars_decode($row['~NAME']) : htmlspecialchars_decode($row['NAME']),
		'NOTES' => isset($row['~NOTES']) ? htmlspecialchars_decode($row['~NOTES']) : htmlspecialchars_decode((string)$row['NOTES']),
		'QUANTITY' => $row['QUANTITY'],
		'PROPS' => $row['PROPS'],
		'PROPS_ALL' => $row['PROPS_ALL'],
		'HASH' => $row['HASH'],
		'SORT' => $row['SORT'],
		'DETAIL_PAGE_URL' => $row['DETAIL_PAGE_URL'],
		'CURRENCY' => $row['CURRENCY'],
		'DISCOUNT_PRICE_PERCENT' => $row['DISCOUNT_PRICE_PERCENT'],
		'DISCOUNT_PRICE_PERCENT_FORMATED' => $row['DISCOUNT_PRICE_PERCENT_FORMATED'],
		'SHOW_DISCOUNT_PRICE' => (float)$row['DISCOUNT_PRICE'] > 0,
		'PRICE' => $row['PRICE'],
		'PRICE_FORMATED' => $row['PRICE_FORMATED'],
		'FULL_PRICE' => $row['FULL_PRICE'],
		'FULL_PRICE_FORMATED' => $row['FULL_PRICE_FORMATED'],
		'DISCOUNT_PRICE' => $row['DISCOUNT_PRICE'],
		'DISCOUNT_PRICE_FORMATED' => $row['DISCOUNT_PRICE_FORMATED'],
		'SUM_PRICE' => $row['SUM_VALUE'],
		'SUM_PRICE_FORMATED' => $row['SUM'],
		'SUM_FULL_PRICE' => $row['SUM_FULL_PRICE'],
		'SUM_FULL_PRICE_FORMATED' => $row['SUM_FULL_PRICE_FORMATED'],
		'SUM_DISCOUNT_PRICE' => $row['SUM_DISCOUNT_PRICE'],
		'SUM_DISCOUNT_PRICE_FORMATED' => $row['SUM_DISCOUNT_PRICE_FORMATED'],
		'MEASURE_RATIO' => isset($row['MEASURE_RATIO']) ? $row['MEASURE_RATIO'] : 1,
		'MEASURE_TEXT' => $row['MEASURE_TEXT'],
		'AVAILABLE_QUANTITY' => $row['AVAILABLE_QUANTITY'],
		'CHECK_MAX_QUANTITY' => $row['CHECK_MAX_QUANTITY'],
		'MODULE' => $row['MODULE'],
		'PRODUCT_PROVIDER_CLASS' => $row['PRODUCT_PROVIDER_CLASS'],
		'NOT_AVAILABLE' => $row['NOT_AVAILABLE'] === true,
		'DELAYED' => $row['DELAY'] === 'Y',
		'SKU_BLOCK_LIST' => array(),
		'COLUMN_LIST' => array(),
		'SHOW_LABEL' => false,
		'LABEL_VALUES' => array(),
		'BRAND' => isset($row[$this->arParams['BRAND_PROPERTY'].'_VALUE'])
			? $row[$this->arParams['BRAND_PROPERTY'].'_VALUE']
			: '',
        'IS_CUTTING' => false,
        'ONLY_PIECES' => false,
        'HALF_PIECES' => false,
        'BASIC_SHEET' => false,
        'WHOLE_SHEET_PIECES' => false,
        'IS_SHEET' => false,
        'FREE_CUTTING_1M' => false,
        'HALF_PIECE_CUT' => false,
        'HALF_PIECE_CUT_NOTICE' => '',
        'DEFAULT_CUT_PRICE' => 0,
        'BASE_METER_PRICE' => 0,
        'CUTTING_ENABLED' => false,
        'CUTTING_SURCHARGE_10' => false,
        'CUTTING_PLAN_TEXT' => '',
        'CUTTING_PLAN_TEXT_VIEW' => '',
        'CUTTING_OPTIONS' => array(),
        'STEEL_GRADE' => '',
        'SECTION_PAGE_URL' => '',
        'HAS_SECTION_LINK' => false,
	);

    if ($row['PRODUCT_ID'] > 0) {
        $onlyPieces = isOnlyPiecesProduct(getPropVal(36, $row['PRODUCT_ID'], 'TOLKO_SHT'));
        $halfPieces = isHalfPiecesProduct(getPropVal(36, $row['PRODUCT_ID'], 'TOLKO_SHT_I_0_5_SHT'));
        $rowData['ONLY_PIECES'] = $onlyPieces;
        $rowData['HALF_PIECES'] = $halfPieces;
        $rowData['BASKET_WIDTH'] = floatval(getPropVal(36, $row['PRODUCT_ID'], 'SHIRINA_RASCHET'));
        $rowData['IS_SHEET'] = isSheetProduct($rowData['BASKET_WIDTH']);
        // Лист: шаг кратно 1 м длины. Флаг 0,5 шт → без +10% за кусок (только резы)
        $rowData['BASIC_SHEET'] = $rowData['IS_SHEET'] && !$onlyPieces;
        $rowData['WHOLE_SHEET_PIECES'] = $rowData['IS_SHEET'] && !$halfPieces && !$rowData['BASIC_SHEET'];
        $rowData['FREE_CUTTING_1M'] = $halfPieces && !$rowData['IS_SHEET'];

        $cuttingServices = getProductCuttingServices($row['PRODUCT_ID']);
        $rowData['IS_CUTTING'] = count($cuttingServices) > 0 && !$onlyPieces;
        $rowData['BASKET_LENGTH_PER_PIECE'] = floatval(getPropVal(36, $row['PRODUCT_ID'], 'DLINA_RASCHET'));
        $rowData['STEEL_GRADE'] = trim((string)getPropVal(36, $row['PRODUCT_ID'], '_5_MARKASAYT_ILI_RAZMER_SETKI'));
        $rowData['BASKET_WEIGHT_PER_METER'] = getProductWeightPerMeterKg($row['PRODUCT_ID'], 36);

        // ссылка на раздел каталога, где лежит товар
        if (\Bitrix\Main\Loader::includeModule('iblock')) {
            $productId = (int)$row['PRODUCT_ID'];
            $sectionId = 0;
            $elRes = \CIBlockElement::GetList(
                [],
                ['ID' => $productId, 'IBLOCK_ID' => 36],
                false,
                false,
                ['ID', 'IBLOCK_ID', 'IBLOCK_SECTION_ID', 'DETAIL_PAGE_URL']
            );
            if ($el = $elRes->GetNext()) {
                $sectionId = (int)($el['IBLOCK_SECTION_ID'] ?? 0);
                if ($rowData['SECTION_PAGE_URL'] === '' && !empty($el['DETAIL_PAGE_URL'])) {
                    // запасной вариант — карточка товара
                    $rowData['DETAIL_PAGE_URL'] = (string)$el['DETAIL_PAGE_URL'];
                }
            }
            if ($sectionId <= 0) {
                $groups = \CIBlockElement::GetElementGroups($productId, true, ['ID', 'CODE']);
                if ($g = $groups->Fetch()) {
                    $sectionId = (int)$g['ID'];
                }
            }
            if ($sectionId > 0) {
                $secRes = \CIBlockSection::GetList(
                    [],
                    ['ID' => $sectionId, 'IBLOCK_ID' => 36, 'ACTIVE' => 'Y'],
                    false,
                    ['ID', 'CODE', 'SECTION_PAGE_URL']
                );
                if ($sec = $secRes->GetNext()) {
                    $sectionUrl = trim((string)($sec['SECTION_PAGE_URL'] ?? ''));
                    if ($sectionUrl === '' && !empty($sec['CODE'])) {
                        $sectionUrl = '/catalog/' . rawurlencode((string)$sec['CODE']) . '/';
                    }
                    if ($sectionUrl !== '') {
                        $rowData['SECTION_PAGE_URL'] = $sectionUrl;
                    }
                }
            }
        }
        if ($rowData['SECTION_PAGE_URL'] === '') {
            $detailUrl = trim((string)($rowData['DETAIL_PAGE_URL'] ?? ''));
            if ($detailUrl !== '') {
                $rowData['SECTION_PAGE_URL'] = $detailUrl;
            }
        }
        $rowData['HAS_SECTION_LINK'] = $rowData['SECTION_PAGE_URL'] !== '';
        $rowData['BASKET_CUTTING_STOCK'] = $rowData['BASKET_LENGTH_PER_PIECE'] > 0
            ? $rowData['BASKET_LENGTH_PER_PIECE']
            : $rowData['BASKET_WIDTH'];
        $qtyDisplay = getBasketItemQuantityDisplay($row['PRODUCT_ID'], $row['QUANTITY']);
        $rowData['DISPLAY_PIECES'] = $qtyDisplay['PIECES'];
        $rowData['DISPLAY_AREA'] = $qtyDisplay['AREA'];
        $rowData['DISPLAY_AREA_UNIT'] = $qtyDisplay['AREA_UNIT'];
        $rowData['DISPLAY_WEIGHT'] = $qtyDisplay['WEIGHT'];

        $defaultCutPrice = 0;
        foreach ($cuttingServices as $service) {
            if (empty($service['CODE'])) {
                continue;
            }
            $price = (float)($service['VALUE'] ?? 0);
            if ($defaultCutPrice <= 0 && $price > 0) {
                $defaultCutPrice = $price;
            }
            $humanName = getCuttingServiceHumanName($service['CODE'], $service['NAME'] ?? '');
            $priceLabel = $price > 0
                ? (' — ' . number_format($price, 0, '.', ' ') . ' ₽')
                : '';
            $rowData['CUTTING_OPTIONS'][] = array(
                'CODE' => (string)$service['CODE'],
                'NAME' => $humanName,
                'PRICE' => $price,
                'PRICE_FORMATED' => $price > 0 ? (number_format($price, 0, '.', ' ') . ' ₽') : '',
                'LABEL' => $humanName . $priceLabel,
            );
        }
        $rowData['DEFAULT_CUT_PRICE'] = $defaultCutPrice;
        $rowData['BASE_METER_PRICE'] = 0;
        if ($rowData['BASIC_SHEET'] || $rowData['IS_SHEET']) {
            $baseMeterRow = function_exists('fetchCatalogPriceRow') ? fetchCatalogPriceRow((int)$row['PRODUCT_ID'], 17) : null;
            if ($baseMeterRow) {
                $rowData['BASE_METER_PRICE'] = (float)$baseMeterRow['PRICE'];
            }
        }

        if (
            $rowData['BASKET_LENGTH_PER_PIECE'] > 0
            && $rowData['IS_CUTTING']
        ) {
            $piecesFrac = getIncompletePieceFraction((float)$row['QUANTITY'], $rowData['BASKET_LENGTH_PER_PIECE']);
            if ($piecesFrac > 0.0001) {
                $rowData['HALF_PIECE_CUT'] = true;
                $rowData['HALF_PIECE_CUT_NOTICE'] = getIncompletePieceCutNotice($piecesFrac, $defaultCutPrice);
            }
        }

        if (!empty($row['PROPS']) && is_array($row['PROPS'])) {
            foreach ($row['PROPS'] as $prop) {
                $code = (string)($prop['CODE'] ?? '');
                if ($code === 'CUTTING_ENABLED') {
                    $rowData['CUTTING_ENABLED'] = !$onlyPieces && (string)($prop['VALUE'] ?? '') === 'Y';
                }
                if ($code === 'CUTTING_PLAN_TEXT') {
                    $rowData['CUTTING_PLAN_TEXT'] = (string)($prop['VALUE'] ?? '');
                }
                if ($code === 'CUTTING_SURCHARGE_10') {
                    $rowData['CUTTING_SURCHARGE_10'] = (string)($prop['VALUE'] ?? '') === 'Y';
                }
            }
        }

        if ($rowData['CUTTING_PLAN_TEXT'] !== '') {
            $viewLines = [];
            foreach (preg_split('/\R+/u', $rowData['CUTTING_PLAN_TEXT']) ?: [] as $line) {
                $line = trim((string)$line);
                if ($line === '') {
                    continue;
                }
                $chunks = array_map('trim', explode('|', $line));
                if (count($chunks) >= 3 && preg_match('/^неполн/ui', $chunks[0])) {
                    $typeName = $chunks[3] ?? $chunks[1];
                    $viewLines[] = 'неполная · ' . $typeName . ' · ' . $chunks[2] . ' м';
                    continue;
                }
                if (count($chunks) >= 3 && preg_match('/^(\d+)\s*шт$/ui', $chunks[0], $m)) {
                    $typeName = $chunks[3] ?? $chunks[1];
                    $viewLines[] = $m[1] . ' шт · ' . $typeName . ' · ' . $chunks[2] . ' м';
                    continue;
                }
                if (preg_match('/^(\d+)\s*шт\s*[—\-:]\s*(.+)$/ui', $line, $m)) {
                    $viewLines[] = $m[1] . ' шт · ' . trim(preg_replace('/\s*м\s*$/ui', '', $m[2])) . ' м';
                    continue;
                }
                // совсем старый мусор вида "1 - 1+1+10"
                if (preg_match('/^(\d+)\s*[-–—]\s*(.+)$/u', $line, $m)) {
                    $viewLines[] = $m[1] . ' шт · ' . trim($m[2]) . ' м';
                    continue;
                }
                $viewLines[] = $line;
            }
            $rowData['CUTTING_PLAN_TEXT_VIEW'] = implode("\n", $viewLines);
        }
    } else {
        $rowData['BASKET_LENGTH_PER_PIECE'] = 0;
        $rowData['BASKET_WEIGHT_PER_METER'] = 0;
        $rowData['BASKET_WIDTH'] = 0;
        $rowData['IS_SHEET'] = false;
        $rowData['BASKET_CUTTING_STOCK'] = 0;
        $rowData['DISPLAY_PIECES'] = '—';
        $rowData['DISPLAY_AREA'] = '—';
        $rowData['DISPLAY_AREA_UNIT'] = '';
        $rowData['DISPLAY_WEIGHT'] = '—';
    }

    applyBasketCustomPriceDisplay($rowData);

	// show price including ratio
	if ($rowData['MEASURE_RATIO'] != 1)
	{
		$price = PriceMaths::roundPrecision($rowData['PRICE'] * $rowData['MEASURE_RATIO']);
		if ($price != $rowData['PRICE'])
		{
			$rowData['PRICE'] = $price;
			$rowData['PRICE_FORMATED'] = CCurrencyLang::CurrencyFormat($price, $rowData['CURRENCY'], true);
		}

		$fullPrice = PriceMaths::roundPrecision($rowData['FULL_PRICE'] * $rowData['MEASURE_RATIO']);
		if ($fullPrice != $rowData['FULL_PRICE'])
		{
			$rowData['FULL_PRICE'] = $fullPrice;
			$rowData['FULL_PRICE_FORMATED'] = CCurrencyLang::CurrencyFormat($fullPrice, $rowData['CURRENCY'], true);
		}

		$discountPrice = PriceMaths::roundPrecision($rowData['DISCOUNT_PRICE'] * $rowData['MEASURE_RATIO']);
		if ($discountPrice != $rowData['DISCOUNT_PRICE'])
		{
			$rowData['DISCOUNT_PRICE'] = $discountPrice;
			$rowData['DISCOUNT_PRICE_FORMATED'] = CCurrencyLang::CurrencyFormat($discountPrice, $rowData['CURRENCY'], true);
		}
	}

	$rowData['SHOW_PRICE_FOR'] = (float)$rowData['QUANTITY'] !== (float)$rowData['MEASURE_RATIO'];

	$hideDetailPicture = false;

	if (!empty($row['PREVIEW_PICTURE_SRC']))
	{
		$rowData['IMAGE_URL'] = $row['PREVIEW_PICTURE_SRC'];
	}
	elseif (!empty($row['DETAIL_PICTURE_SRC']))
	{
		$hideDetailPicture = true;
		$rowData['IMAGE_URL'] = $row['DETAIL_PICTURE_SRC'];
	}

	if (!empty($row['SKU_DATA']))
	{
		$propMap = array();

		foreach($row['PROPS'] as $prop)
		{
			$propMap[$prop['CODE']] = !empty($prop['~VALUE']) ? $prop['~VALUE'] : $prop['VALUE'];
		}

		$notSelectable = true;

		foreach ($row['SKU_DATA'] as $skuBlock)
		{
			$skuBlockData = array(
				'ID' => $skuBlock['ID'],
				'CODE' => $skuBlock['CODE'],
				'NAME' => $skuBlock['NAME']
			);

			$isSkuSelected = false;
			$isImageProperty = false;

			if (count($skuBlock['VALUES']) > 1)
			{
				$notSelectable = false;
			}

			foreach ($skuBlock['VALUES'] as $skuItem)
			{
				if ($skuBlock['TYPE'] === 'S' && $skuBlock['USER_TYPE'] === 'directory')
				{
					$valueId = $skuItem['XML_ID'];
				}
				elseif ($skuBlock['TYPE'] === 'E')
				{
					$valueId = $skuItem['ID'];
				}
				else
				{
					$valueId = $skuItem['NAME'];
				}

				$skuValue = array(
					'ID' => $skuItem['ID'],
					'NAME' => $skuItem['NAME'],
					'SORT' => $skuItem['SORT'],
					'PICT' => !empty($skuItem['PICT']) ? $skuItem['PICT']['SRC'] : false,
					'XML_ID' => !empty($skuItem['XML_ID']) ? $skuItem['XML_ID'] : false,
					'VALUE_ID' => $valueId,
					'PROP_ID' => $skuBlock['ID'],
					'PROP_CODE' => $skuBlock['CODE']
				);

				if (
					!empty($propMap[$skuBlockData['CODE']])
					&& ($propMap[$skuBlockData['CODE']] == $skuItem['NAME'] || $propMap[$skuBlockData['CODE']] == $skuItem['XML_ID'])
				)
				{
					$skuValue['SELECTED'] = true;
					$isSkuSelected = true;
				}

				$skuBlockData['SKU_VALUES_LIST'][] = $skuValue;
				$isImageProperty = $isImageProperty || !empty($skuItem['PICT']);
			}

			if (!$isSkuSelected && !empty($skuBlockData['SKU_VALUES_LIST'][0]))
			{
				$skuBlockData['SKU_VALUES_LIST'][0]['SELECTED'] = true;
			}

			$skuBlockData['IS_IMAGE'] = $isImageProperty;

			$rowData['SKU_BLOCK_LIST'][] = $skuBlockData;
		}
	}

	if ($row['NOT_AVAILABLE'])
	{
		foreach ($rowData['SKU_BLOCK_LIST'] as $blockKey => $skuBlock)
		{
			if (!empty($skuBlock['SKU_VALUES_LIST']))
			{
				if ($notSelectable)
				{
					foreach ($skuBlock['SKU_VALUES_LIST'] as $valueKey => $skuValue)
					{
						$rowData['SKU_BLOCK_LIST'][$blockKey]['SKU_VALUES_LIST'][0]['NOT_AVAILABLE_OFFER'] = true;
					}
				}
				elseif (!isset($rowData['SKU_BLOCK_LIST'][$blockKey + 1]))
				{
					foreach ($skuBlock['SKU_VALUES_LIST'] as $valueKey => $skuValue)
					{
						if ($skuValue['SELECTED'])
						{
							$rowData['SKU_BLOCK_LIST'][$blockKey]['SKU_VALUES_LIST'][$valueKey]['NOT_AVAILABLE_OFFER'] = true;
						}
					}
				}
			}
		}
	}

	if (!empty($result['GRID']['HEADERS']) && is_array($result['GRID']['HEADERS']))
	{
		$skipHeaders = [
			'NAME' => true,
			'QUANTITY' => true,
			'PRICE' => true,
			'PREVIEW_PICTURE' => true,
			'SUM' => true,
			'PROPS' => true,
			'DELETE' => true,
			'DELAY' => true,
		];

		foreach ($result['GRID']['HEADERS'] as &$value)
		{
			if (
				empty($value['id'])
				|| isset($skipHeaders[$value['id']])
				|| ($hideDetailPicture && $value['id'] === 'DETAIL_PICTURE'))
			{
				continue;
			}

			if ($value['id'] === 'DETAIL_PICTURE')
			{
				$value['name'] = Loc::getMessage('SBB_DETAIL_PICTURE_NAME');

				if (!empty($row['DETAIL_PICTURE_SRC']))
				{
					$rowData['COLUMN_LIST'][] = array(
						'CODE' => $value['id'],
						'NAME' => $value['name'],
						'VALUE' => array(
							array(
								'IMAGE_SRC' => $row['DETAIL_PICTURE_SRC'],
								'IMAGE_SRC_2X' => $row['DETAIL_PICTURE_SRC_2X'],
								'IMAGE_SRC_ORIGINAL' => $row['DETAIL_PICTURE_SRC_ORIGINAL'],
								'INDEX' => 0
							)
						),
						'IS_IMAGE' => true,
						'HIDE_MOBILE' => !isset($mobileColumns[$value['id']])
					);
				}
			}
			elseif ($value['id'] === 'PREVIEW_TEXT')
			{
				$value['name'] = Loc::getMessage('SBB_PREVIEW_TEXT_NAME');

				if ($row['PREVIEW_TEXT_TYPE'] === 'text' && !empty($row['PREVIEW_TEXT']))
				{
					$rowData['COLUMN_LIST'][] = array(
						'CODE' => $value['id'],
						'NAME' => $value['name'],
						'VALUE' => $row['PREVIEW_TEXT'],
						'IS_TEXT' => true,
						'HIDE_MOBILE' => !isset($mobileColumns[$value['id']])
					);
				}
			}
			elseif ($value['id'] === 'TYPE')
			{
				$value['name'] = Loc::getMessage('SBB_PRICE_TYPE_NAME');

				if (!empty($row['NOTES']))
				{
					$rowData['COLUMN_LIST'][] = array(
						'CODE' => $value['id'],
						'NAME' => $value['name'],
						'VALUE' => isset($row['~NOTES']) ? $row['~NOTES'] : $row['NOTES'],
						'IS_TEXT' => true,
						'HIDE_MOBILE' => !isset($mobileColumns[$value['id']])
					);
				}
			}
			elseif ($value['id'] === 'DISCOUNT')
			{
				$value['name'] = Loc::getMessage('SBB_DISCOUNT_NAME');

				if ($row['DISCOUNT_PRICE_PERCENT'] > 0 && !empty($row['DISCOUNT_PRICE_PERCENT_FORMATED']))
				{
					$rowData['COLUMN_LIST'][] = array(
						'CODE' => $value['id'],
						'NAME' => $value['name'],
						'VALUE' => $row['DISCOUNT_PRICE_PERCENT_FORMATED'],
						'IS_TEXT' => true,
						'HIDE_MOBILE' => !isset($mobileColumns[$value['id']])
					);
				}
			}
			elseif ($value['id'] === 'WEIGHT')
			{
				$value['name'] = Loc::getMessage('SBB_WEIGHT_NAME');

				if (!empty($row['WEIGHT_FORMATED']))
				{
					$rowData['COLUMN_LIST'][] = array(
						'CODE' => $value['id'],
						'NAME' => $value['name'],
						'VALUE' => $row['WEIGHT_FORMATED'],
						'IS_TEXT' => true,
						'HIDE_MOBILE' => !isset($mobileColumns[$value['id']])
					);
				}
			}
			elseif (!empty($row[$value['id'].'_SRC']))
			{
				$i = 0;

				foreach ($row[$value['id'].'_SRC'] as &$image)
				{
					$image['INDEX'] = $i++;
				}

				$rowData['COLUMN_LIST'][] = array(
					'CODE' => $value['id'],
					'NAME' => $value['name'],
					'VALUE' => $row[$value['id'].'_SRC'],
					'IS_IMAGE' => true,
					'HIDE_MOBILE' => !isset($mobileColumns[$value['id']])
				);
			}
			elseif (!empty($row[$value['id'].'_DISPLAY']))
			{
				$rowData['COLUMN_LIST'][] = array(
					'CODE' => $value['id'],
					'NAME' => $value['name'],
					'VALUE' => $row[$value['id'].'_DISPLAY'],
					'IS_TEXT' => true,
					'HIDE_MOBILE' => !isset($mobileColumns[$value['id']])
				);
			}
			elseif (!empty($row[$value['id'].'_LINK']))
			{
				$linkValues = array();

				foreach ($row[$value['id'].'_LINK'] as $index => $link)
				{
					$linkValues[] = array(
						'LINK' => $link,
						'IS_LAST' => !isset($row[$value['id'].'_LINK'][$index + 1])
					);
				}

				$rowData['COLUMN_LIST'][] = array(
					'CODE' => $value['id'],
					'NAME' => $value['name'],
					'VALUE' => $linkValues,
					'IS_LINK' => true,
					'HIDE_MOBILE' => !isset($mobileColumns[$value['id']])
				);
			}
			elseif (isset($row[$value['id']]))
			{
				$rowData['COLUMN_LIST'][] = array(
					'CODE' => $value['id'],
					'NAME' => $value['name'],
					'VALUE' => $row[$value['id']],
					'IS_TEXT' => true,
					'HIDE_MOBILE' => !isset($mobileColumns[$value['id']])
				);
			}
		}

		unset($value);
	}

	if (!empty($row['LABEL_ARRAY_VALUE']))
	{
		$labels = array();

		foreach ($row['LABEL_ARRAY_VALUE'] as $code => $value)
		{
			$labels[] = array(
				'NAME' => $value,
				'HIDE_MOBILE' => !isset($this->arParams['LABEL_PROP_MOBILE'][$code])
			);
		}

		$rowData['SHOW_LABEL'] = true;
		$rowData['LABEL_VALUES'] = $labels;
	}

	$result['BASKET_ITEM_RENDER_DATA'][] = $rowData;
}

$totalData = array(
	'DISABLE_CHECKOUT' => (int)$result['ORDERABLE_BASKET_ITEMS_COUNT'] === 0,
	'PRICE' => $result['allSum'],
	'PRICE_FORMATED' => $result['allSum_FORMATED'],
	'PRICE_WITHOUT_DISCOUNT_FORMATED' => $result['PRICE_WITHOUT_DISCOUNT'],
	'CURRENCY' => $result['CURRENCY']
);

if ($result['DISCOUNT_PRICE_ALL'] > 0)
{
	$totalData['DISCOUNT_PRICE_FORMATED'] = $result['DISCOUNT_PRICE_FORMATED'];
}

if ($result['allWeight'] > 0)
{
	$totalData['WEIGHT_FORMATED'] = $result['allWeight_FORMATED'];
}

if ($this->priceVatShowValue === 'Y')
{
	$totalData['SHOW_VAT'] = true;
	$totalData['VAT_SUM_FORMATED'] = $result['allVATSum_FORMATED'];
	$totalData['SUM_WITHOUT_VAT_FORMATED'] = $result['allSum_wVAT_FORMATED'];
}

if ($this->hideCoupon !== 'Y' && !empty($result['COUPON_LIST']))
{
	$totalData['COUPON_LIST'] = $result['COUPON_LIST'];
	
	foreach ($totalData['COUPON_LIST'] as &$coupon)
	{
		if ($coupon['JS_STATUS'] === 'ENTERED')
		{
			$coupon['CLASS'] = 'danger';
		}
		elseif ($coupon['JS_STATUS'] === 'APPLYED')
		{
			$coupon['CLASS'] = 'muted';
		}
		else
		{
			$coupon['CLASS'] = 'danger';
		}
	}
}

$result['TOTAL_RENDER_DATA'] = $totalData;