<?php

function getProp($ID_BLOCK, $ID, $CODE)
{
    if (CModule::IncludeModule('iblock')) {
        $db_props = CIBlockElement::GetProperty($ID_BLOCK, $ID, array("sort" => "asc"), array("CODE" => $CODE));
        if ($ar_props = $db_props->Fetch()) {
            return $ar_props;
        }
    }
}

function getPropVal($ID_BLOCK, $ID, $CODE)
{
    if ($ar_props = getProp($ID_BLOCK, $ID, $CODE)) {
        return $ar_props['VALUE'];
    }

    return null;
}

function getCoefficientProduct($ID_BLOCK, $ID)
{
    return floatval(getPropVal($ID_BLOCK, $ID, 'KOEFFITSENT_RASCHET'));
}

function getLengthProduct($ID_BLOCK, $ID)
{
    return floatval(getPropVal($ID_BLOCK, $ID, 'DLINA_RASCHET'));
}

function isCustomPrice($ID_BLOCK, $ID)
{
    return getCoefficientProduct($ID_BLOCK, $ID) > 0 && getLengthProduct($ID_BLOCK, $ID) > 0;
}

/**
 * Возвращает путь к WebP-версии картинки (кеш в /upload/webp_cache/).
 * Если конвертация невозможна — исходный SRC.
 */
function getImageWebpSrc($src, $quality = 82)
{
    $src = trim((string)$src);
    if ($src === '') {
        return '';
    }

    if (preg_match('/\.webp$/i', $src)) {
        return $src;
    }

    if (!function_exists('imagewebp')) {
        return $src;
    }

    $docRoot = rtrim((string)($_SERVER['DOCUMENT_ROOT'] ?? ''), '/');
    if ($docRoot === '' || $src[0] !== '/') {
        return $src;
    }

    $absSource = $docRoot . $src;
    if (!is_file($absSource) || !is_readable($absSource)) {
        return $src;
    }

    $sourceMtime = (int)@filemtime($absSource);
    $sourceSize = (int)@filesize($absSource);
    $hash = md5($src . '|' . $sourceMtime . '|' . $sourceSize);
    $webpRel = '/upload/webp_cache/' . substr($hash, 0, 2) . '/' . substr($hash, 2, 2) . '/' . $hash . '.webp';
    $absWebp = $docRoot . $webpRel;

    if (is_file($absWebp) && (int)@filemtime($absWebp) >= $sourceMtime) {
        return $webpRel;
    }

    $info = @getimagesize($absSource);
    if (!$info || empty($info[2])) {
        return $src;
    }

    switch ((int)$info[2]) {
        case IMAGETYPE_JPEG:
            $image = @imagecreatefromjpeg($absSource);
            break;
        case IMAGETYPE_PNG:
            $image = @imagecreatefrompng($absSource);
            break;
        case IMAGETYPE_GIF:
            $image = @imagecreatefromgif($absSource);
            break;
        default:
            return $src;
    }

    if (!$image) {
        return $src;
    }

    if (function_exists('imagepalettetotruecolor')) {
        @imagepalettetotruecolor($image);
    }

    if (function_exists('imagealphablending')) {
        imagealphablending($image, true);
        imagesavealpha($image, true);
    }

    $dir = dirname($absWebp);
    if (!is_dir($dir) && !@mkdir($dir, 0775, true) && !is_dir($dir)) {
        imagedestroy($image);
        return $src;
    }

    $tmpWebp = $absWebp . '.tmp.' . getmypid();
    $ok = @imagewebp($image, $tmpWebp, (int)$quality);
    imagedestroy($image);

    if (!$ok || !is_file($tmpWebp)) {
        @unlink($tmpWebp);
        return $src;
    }

    if (!@rename($tmpWebp, $absWebp)) {
        @unlink($tmpWebp);
        return $src;
    }

    @chmod($absWebp, 0664);

    return $webpRel;
}

/**
 * Разрешает заказ товара каталога при нулевом остатке (B2B: заказ под поставку).
 */
function ensureCatalogProductOrderable($productId, $iblockId = 36)
{
    $productId = (int)$productId;
    $iblockId = (int)$iblockId;

    if ($productId <= 0 || !CModule::IncludeModule('catalog') || !CModule::IncludeModule('iblock')) {
        return false;
    }

    $element = CIBlockElement::GetList(
        [],
        ['ID' => $productId, 'IBLOCK_ID' => $iblockId, 'ACTIVE' => 'Y'],
        false,
        false,
        ['ID']
    )->Fetch();

    if (!$element) {
        return false;
    }

    $product = CCatalogProduct::GetByID($productId);
    if (!$product) {
        return false;
    }

    if (($product['AVAILABLE'] ?? 'N') === 'Y') {
        return true;
    }

    return (bool)CCatalogProduct::Update($productId, [
        'QUANTITY_TRACE' => 'N',
        'CAN_BUY_ZERO' => 'Y',
        'AVAILABLE' => 'Y',
    ]);
}

/**
 * @param $ID
 * @return array
 */
function getProductCuttingServices($ID)
{
    $props = [];
    $codes = ['REZKA_GAZ_RASCHET', 'REZKA_ABRAZIV_RASCHET'];

    if (!CModule::IncludeModule('iblock')) {
        return [];
    }

    $res = CIBlockElement::GetByID($ID);
    if($ar_res = $res->GetNext()) {
        foreach ($codes as $code) {
            $prop = getProp($ar_res['IBLOCK_ID'], $ID, $code);
            if (!empty($prop['CODE'])) {
                $props[] = $prop;
            }
        }
    }

    return $props;
}

/**
 * Человекочитаемое название типа резки по коду свойства.
 */
function getCuttingServiceHumanName($code, $fallback = '')
{
    $map = [
        'REZKA_GAZ_RASCHET' => 'Газовая резка',
        'REZKA_ABRAZIV_RASCHET' => 'Абразивная резка',
    ];

    $code = (string)$code;
    if (isset($map[$code])) {
        return $map[$code];
    }

    $fallback = trim((string)$fallback);
    if ($fallback !== '') {
        $fallback = str_replace(['_', 'Расчет', 'расчет'], [' ', '', ''], $fallback);
        $fallback = preg_replace('/\s+/u', ' ', $fallback);
        return trim($fallback) !== '' ? trim($fallback) : $code;
    }

    return $code;
}

function isSquareMeterSection($sectionCode)
{
    return in_array($sectionCode, ['list_g_k'], true);
}

function shouldShowPlusPriceColumn($sectionCode)
{
    return isSquareMeterSection($sectionCode);
}

function isWeightSection($sectionCode)
{
    return in_array($sectionCode, ['stal_armaturnaya_a3'], true);
}

function isOnlyPiecesProduct($value)
{
    if ($value === null || $value === '') {
        return false;
    }

    $normalized = mb_strtolower(trim((string)$value));

    return in_array($normalized, ['да', 'y', 'yes', '1', 'true'], true);
}

function isHalfPiecesProduct($value)
{
    return isOnlyPiecesProduct($value);
}

function productAllowsFreeMeterCutting($productId, $iblockId = 36)
{
    if (isSheetProduct(getPropVal($iblockId, $productId, 'SHIRINA_RASCHET'))) {
        return false;
    }

    return isHalfPiecesProduct(getPropVal($iblockId, $productId, 'TOLKO_SHT_I_0_5_SHT'));
}

function getIncompletePieceFraction($quantity, $lengthPerPiece)
{
    $lengthPerPiece = (float)$lengthPerPiece;
    if ($lengthPerPiece <= 0) {
        return 0;
    }

    $piecesExact = (float)$quantity / $lengthPerPiece;

    return max(0, $piecesExact - floor($piecesExact + 1e-9));
}

function hasIncompletePieceCut($quantity, $lengthPerPiece)
{
    return getIncompletePieceFraction($quantity, $lengthPerPiece) > 0.0001;
}

function formatIncompletePieceFraction($fraction)
{
    $fraction = (float)$fraction;
    if ($fraction <= 0) {
        return '0';
    }

    if (abs($fraction - round($fraction)) < 0.01) {
        return (string)(int)round($fraction);
    }

    return formatBasketQtyNumber($fraction, 2);
}

function getIncompletePieceCutNotice($fraction, $cutPrice = 0)
{
    $fracText = formatIncompletePieceFraction($fraction);
    if ((float)$cutPrice > 0) {
        return 'Неполная ' . $fracText . ' шт — 1 рез ('
            . number_format((float)$cutPrice, 0, '.', ' ') . ' ₽)';
    }

    return 'Неполная ' . $fracText . ' шт — 1 рез';
}

function getFreeCuttingTipText()
{
    return 'Товар режется кратно 1 метру без наценки. Длины кусков — кратно 1 метру. Заказ поштучно: целые или 0,5 шт.';
}

function isSheetProduct($width)
{
    return floatval($width) > 0;
}

function isBasicSheetProduct($productId, $iblockId = 36)
{
    if (!isSheetProduct(getPropVal($iblockId, $productId, 'SHIRINA_RASCHET'))) {
        return false;
    }

    if (isOnlyPiecesProduct(getPropVal($iblockId, $productId, 'TOLKO_SHT'))) {
        return false;
    }

    if (isHalfPiecesProduct(getPropVal($iblockId, $productId, 'TOLKO_SHT_I_0_5_SHT'))) {
        return false;
    }

    return true;
}

function getBasicSheetSurchargePercent()
{
    return 10;
}

function getBasicSheetWidthMeterSteps($lengthPerPiece, $width)
{
    $length = (float)$lengthPerPiece;
    $width = (float)$width;

    if ($length <= 0 || $width <= 0) {
        return null;
    }

    $widthMeters = max(1, (int)round($width));

    return [
        'WIDTH_METERS' => $widthMeters,
        'PIECE_STEP' => 1 / $widthMeters,
        'AREA_STEP' => $length,
        'METER_STEP' => $length / $widthMeters,
        'FULL_AREA' => $length * $width,
    ];
}

function snapBasicSheetPiecesByWidthMeter($pieces, $lengthPerPiece, $width)
{
    $steps = getBasicSheetWidthMeterSteps($lengthPerPiece, $width);
    if (!$steps) {
        return max(1, (int)round($pieces));
    }

    $pieceStep = $steps['PIECE_STEP'];
    $widthUnits = max(1, (int)round(((float)$pieces) / $pieceStep));

    return round($widthUnits * $pieceStep, 6);
}

function snapBasicSheetMetersByWidthMeter($metersQty, $lengthPerPiece, $width)
{
    $steps = getBasicSheetWidthMeterSteps($lengthPerPiece, $width);
    if (!$steps) {
        return (float)$metersQty;
    }

    $meterStep = $steps['METER_STEP'];
    $widthUnits = max(1, (int)round(((float)$metersQty) / $meterStep));

    return round($widthUnits * $meterStep, 5);
}

function getBasicSheetCuttingTipText()
{
    return 'Заказ в шт или м² кратно 1 м ширины листа. Резка по ширине — кратно 1 м. Рез пополам — стоимость одного реза. Больше резов — +10% к цене за м².';
}

function getBasicSheetPiecesTipText()
{
    return 'Штуки или м² — кратно 1 м ширины листа (например, 1,5 м² = 1 м ширины).';
}

function getBasicSheetDimensionsTipText()
{
    return 'Длина и ширина листа заданы производителем. Количество — в штуках или м², кратно 1 м ширины.';
}

function parseCutLengthsFromPlanSegment($segment)
{
    $segment = trim((string)$segment);
    if ($segment === '') {
        return [];
    }

    $lengths = [];
    foreach (preg_split('/\s*\+\s*/u', $segment) ?: [] as $part) {
        $part = trim($part);
        if (preg_match('/^(\d+)/', $part, $matches)) {
            $value = (int)$matches[1];
            if ($value > 0) {
                $lengths[] = $value;
            }
        }
    }

    return $lengths;
}

function getCutCountFromLengths(array $lengths)
{
    $count = count($lengths);

    return $count <= 1 ? 0 : $count - 1;
}

function analyzeBasicSheetCuttingPlan($planText)
{
    $result = [
        'hasComplexCut' => false,
        'hasHalfCut' => false,
    ];

    foreach (preg_split('/\R+/u', trim((string)$planText)) ?: [] as $line) {
        $line = trim((string)$line);
        if ($line === '') {
            continue;
        }

        $cutsSegment = '';
        if (preg_match('/^(\d+)\s*шт\s*\|\s*[^|]+\|\s*([^|]+)\s*\|/ui', $line, $matches)) {
            $cutsSegment = $matches[2];
        } elseif (preg_match('/^(\d+)\s*шт\s*[—\-:]\s*(.+)$/ui', $line, $matches)) {
            $cutsSegment = preg_replace('/\s*м\s*$/ui', '', trim($matches[2]));
        } elseif (preg_match('/^(\d+)\s*[-–—]\s*(.+)$/u', $line, $matches)) {
            $cutsSegment = trim($matches[2]);
        }

        $cutCount = getCutCountFromLengths(parseCutLengthsFromPlanSegment($cutsSegment));
        if ($cutCount >= 2) {
            $result['hasComplexCut'] = true;
        } elseif ($cutCount === 1) {
            $result['hasHalfCut'] = true;
        }
    }

    return $result;
}

function getBasketItemPropForPrice($productId, $quantity, $propCode)
{
    if (!\Bitrix\Main\Loader::includeModule('sale')) {
        return null;
    }

    $basket = \Bitrix\Sale\Basket::loadItemsForFUser(
        \Bitrix\Sale\Fuser::getId(),
        \Bitrix\Main\Context::getCurrent()->getSite()
    );

    $candidates = [];

    foreach ($basket as $basketItem) {
        if ((int)$basketItem->getProductId() !== (int)$productId) {
            continue;
        }

        foreach ($basketItem->getPropertyCollection() as $property) {
            if ((string)$property->getField('CODE') !== (string)$propCode) {
                continue;
            }

            $candidates[] = [
                'quantity' => (float)$basketItem->getQuantity(),
                'value' => $property->getField('VALUE'),
            ];
            break;
        }
    }

    if (!$candidates) {
        return null;
    }

    foreach ($candidates as $candidate) {
        if (abs($candidate['quantity'] - (float)$quantity) <= 0.0001) {
            return $candidate['value'];
        }
    }

    return count($candidates) === 1 ? $candidates[0]['value'] : null;
}

function refreshBasketItemCustomPrice(\Bitrix\Sale\BasketItem $basketItem)
{
    global $USER;

    if (!\Bitrix\Main\Loader::includeModule('catalog')) {
        return false;
    }

    $productId = (int)$basketItem->getProductId();
    $quantity = (float)$basketItem->getQuantity();

    if ($productId <= 0 || $quantity <= 0) {
        return false;
    }

    $optimal = \CCatalogProduct::GetOptimalPrice(
        $productId,
        $quantity,
        is_object($USER) ? $USER->GetUserGroupArray() : [],
        'N'
    );

    if (empty($optimal['RESULT_PRICE']['DISCOUNT_PRICE'])) {
        return false;
    }

    $basePrice = (float)$optimal['RESULT_PRICE']['BASE_PRICE'];
    $discountPrice = (float)$optimal['RESULT_PRICE']['DISCOUNT_PRICE'];

    $fields = [
        'PRICE' => $discountPrice,
        'BASE_PRICE' => $basePrice,
        'DISCOUNT_PRICE' => max(0, $basePrice - $discountPrice),
        'CUSTOM_PRICE' => 'N',
    ];

    if (!empty($optimal['PRICE']['NOTES'])) {
        $fields['NOTES'] = (string)$optimal['PRICE']['NOTES'];
    }

    $basketItem->setFields($fields);

    return true;
}

function formatBasketPriceNoteLabel($surchargePercent = null)
{
    $label = 'Цена за метр';

    if ($surchargePercent !== null && $surchargePercent > 0) {
        return $label . ' +' . (int)$surchargePercent . '%';
    }

    return $label;
}

function basicSheetQuantityNeedsPlus10($productId, $quantity, $iblockId = 36)
{
    if (!isBasicSheetProduct($productId, $iblockId)) {
        return false;
    }

    $length = getLengthProduct($iblockId, $productId);

    return quantityNeedsMeterSurcharge($quantity, $length);
}

function resolveBasketSurchargePercent(array $rowData)
{
    if (!empty($rowData['CUTTING_SURCHARGE_10'])) {
        return getBasicSheetSurchargePercent();
    }

    $productId = (int)($rowData['PRODUCT_ID'] ?? 0);
    $quantity = (float)($rowData['QUANTITY'] ?? 0);
    if ($productId > 0 && basicSheetQuantityNeedsPlus10($productId, $quantity)) {
        return getBasicSheetSurchargePercent();
    }

    $notes = (string)($rowData['NOTES'] ?? '');
    if (preg_match('/\+(\d+)%/u', $notes, $matches)) {
        return (int)$matches[1];
    }

    return null;
}

function applyBasketCustomPriceDisplay(array &$rowData)
{
    $productId = (int)($rowData['PRODUCT_ID'] ?? 0);
    if ($productId <= 0 || !isCustomPrice(36, $productId)) {
        return;
    }

    $surchargePercent = resolveBasketSurchargePercent($rowData);
    $rowData['NOTES'] = formatBasketPriceNoteLabel($surchargePercent);

    if ($surchargePercent === null) {
        return;
    }

    if (!\Bitrix\Main\Loader::includeModule('catalog')) {
        return;
    }

    $basePrice = fetchCatalogPriceRow($productId, 17);
    if (!$basePrice) {
        return;
    }

    if ($surchargePercent === 20) {
        $plus20 = fetchCatalogPriceRow($productId, 18);
        $price = $plus20
            ? (float)$plus20['PRICE']
            : (float)$basePrice['PRICE'] * 1.2;
    } else {
        $price = round((float)$basePrice['PRICE'] * (1 + $surchargePercent / 100), 2);
    }

    $quantity = (float)$rowData['QUANTITY'];
    $currency = (string)$rowData['CURRENCY'];

    $rowData['PRICE'] = $price;
    $rowData['PRICE_FORMATED'] = CCurrencyLang::CurrencyFormat($price, $currency, true);
    $rowData['FULL_PRICE'] = $price;
    $rowData['FULL_PRICE_FORMATED'] = $rowData['PRICE_FORMATED'];
    $rowData['DISCOUNT_PRICE'] = 0;
    $rowData['SHOW_DISCOUNT_PRICE'] = false;

    $sum = \Bitrix\Sale\PriceMaths::roundPrecision($price * $quantity);
    $rowData['SUM_PRICE'] = $sum;
    $rowData['SUM_PRICE_FORMATED'] = CCurrencyLang::CurrencyFormat($sum, $currency, true);
}

function getSheetCuttingTipText()
{
    return 'Товар режется кратно 1 метру. Заказ поштучно: целые или 0,5 шт.';
}

function getHalfPiecesCuttingLegendText($isSheet = false)
{
    return $isSheet ? 'Режется кратно 1 метру' : 'Режется кратно 1 метру без наценки';
}

function isWeightFrom500Product($value)
{
    return isOnlyPiecesProduct($value);
}

function getMinBulkWeightKg()
{
    return 500;
}

function getWeightFrom500TipText($minBulkWeight = null)
{
    $min = $minBulkWeight ?? getMinBulkWeightKg();

    return 'До ' . $min . ' кг — заказ только целыми штуками. От ' . $min . ' кг — можно указать вес с точностью до грамм.';
}

function getWeightFrom500PiecesTipText($minBulkWeight = null)
{
    $min = $minBulkWeight ?? getMinBulkWeightKg();

    return 'Вес считается из штук. Чтобы заказать от ' . $min . ' кг по весу — кликните в поле и введите значение.';
}

function getWeightFrom500BulkTipText($minBulkWeight = null)
{
    $min = $minBulkWeight ?? getMinBulkWeightKg();

    return 'Заказ по весу от ' . $min . ' кг. Метры и штуки пересчитываются автоматически.';
}

function formatBasketQtyNumber($value, $decimals = 2)
{
    if ($value === null || $value === '') {
        return '';
    }

    $formatted = number_format((float)$value, $decimals, '.', '');

    return rtrim(rtrim($formatted, '0'), '.');
}

function getBasketItemQuantityDisplay($productId, $metersQuantity)
{
    $iblockId = 36;
    $metersQty = (float)$metersQuantity;
    $lengthPerPiece = floatval(getPropVal($iblockId, $productId, 'DLINA_RASCHET'));
    $weightPerMeter = floatval(getPropVal($iblockId, $productId, '_3_VESPMSAYT'));
    $width = floatval(getPropVal($iblockId, $productId, 'SHIRINA_RASCHET'));

    $pieces = $lengthPerPiece > 0 ? $metersQty / $lengthPerPiece : null;
    $weight = $weightPerMeter > 0 ? $metersQty * $weightPerMeter : null;

    if ($width > 0) {
        $areaValue = $metersQty * $width;
        $areaUnit = 'м²';
    } else {
        $areaValue = $metersQty;
        $areaUnit = 'м';
    }

    $piecesFormatted = '';
    if ($pieces !== null) {
        $halfPieces = isHalfPiecesProduct(getPropVal($iblockId, $productId, 'TOLKO_SHT_I_0_5_SHT'));
        $isSheet = isSheetProduct($width);
        $basicSheet = isBasicSheetProduct($productId, $iblockId);
        $wholeSheetPieces = $isSheet && !$halfPieces && !$basicSheet;

        if ($basicSheet && $width > 0 && $lengthPerPiece > 0) {
            $piecesFormatted = formatBasketQtyNumber(
                snapBasicSheetPiecesByWidthMeter($pieces, $lengthPerPiece, $width),
                3
            );
        } elseif ($wholeSheetPieces) {
            $piecesFormatted = (string)max(1, (int)round($pieces));
        } elseif ($halfPieces) {
            $piecesFormatted = abs($pieces - round($pieces)) < 0.01
                ? (string)(int)round($pieces)
                : formatBasketQtyNumber($pieces, 1);
        } else {
            $piecesFormatted = abs($pieces - round($pieces)) < 0.01
                ? (string)(int)round($pieces)
                : formatBasketQtyNumber($pieces, 2);
        }
    }

    return [
        'PIECES' => $piecesFormatted,
        'AREA' => formatBasketQtyNumber($areaValue, $width > 0 ? 3 : 2),
        'AREA_UNIT' => $areaUnit,
        'WEIGHT' => $weight !== null ? formatBasketQtyNumber($weight, 3) : '',
    ];
}

function getSquareMeterSurchargePercent($sectionCode)
{
    if ($sectionCode === 'list_g_k') {
        return 10;
    }

    return null;
}

function formatCatalogPriceHeaderHtml($priceName, $xmlId, $isSquareMeter, $sectionCode = '')
{
    if (!$isSquareMeter) {
        return htmlspecialcharsbx($priceName);
    }

    $isPlusPrice = strpos($priceName, '+') !== false || $xmlId === 'PER_METER_PLUS20';
    $surcharge = $isPlusPrice ? getSquareMeterSurchargePercent($sectionCode) : null;
    $coef = $surcharge
        ? '<span class="price-header__coef">+' . $surcharge . '%</span>'
        : '';

    return '<span class="price-header">Цена за м<sup>2</sup>' . $coef . '</span>';
}