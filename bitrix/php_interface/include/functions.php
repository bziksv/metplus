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

/**
 * ID типа цен «1-1000» (базовая из 1С).
 */
function getBaseCatalogPriceTypeId()
{
    return 16;
}

/**
 * Цена за кг = цена 1-1000 × Коэффициент_Расчет.
 */
function getProductPricePerKg($productId, $iblockId = 36)
{
    $productId = (int)$productId;
    if ($productId <= 0) {
        return null;
    }

    $coefficient = (float)getPropVal($iblockId, $productId, 'KOEFFITSENT_RASCHET');
    if ($coefficient <= 0) {
        return null;
    }

    $baseRow = function_exists('fetchCatalogPriceRow')
        ? fetchCatalogPriceRow($productId, getBaseCatalogPriceTypeId())
        : null;

    if (!$baseRow) {
        return null;
    }

    $basePrice = (float)($baseRow['PRICE'] ?? 0);
    if ($basePrice <= 0) {
        return null;
    }

    return round($basePrice * $coefficient, 2);
}

function formatProductPricePerKg($productId, $iblockId = 36)
{
    $price = getProductPricePerKg($productId, $iblockId);
    if ($price === null) {
        return '—';
    }

    if (class_exists('CCurrencyLang')) {
        return CCurrencyLang::CurrencyFormat($price, 'RUB', true);
    }

    return number_format($price, 2, '.', ' ') . ' руб.';
}

function getLengthProduct($ID_BLOCK, $ID)
{
    return floatval(getPropVal($ID_BLOCK, $ID, 'DLINA_RASCHET'));
}

/**
 * Число для расчёта веса: пустое/нет значения → 1.
 */
function getWeightCalcFactor($value)
{
    if ($value === null || $value === '') {
        return 1.0;
    }

    $normalized = str_replace(',', '.', trim((string)$value));
    if ($normalized === '' || !is_numeric($normalized)) {
        return 1.0;
    }

    $number = (float)$normalized;

    return $number == 0.0 ? 1.0 : $number;
}

/**
 * Вес одной штуки, кг = Коэффициент_Расчет × Ширина_Расчет × Длина_Расчет
 * (нет значения → 1).
 */
function getProductPieceWeightKg($productId, $iblockId = 36)
{
    $coeff = getWeightCalcFactor(getPropVal($iblockId, $productId, 'KOEFFITSENT_RASCHET'));
    $width = getWeightCalcFactor(getPropVal($iblockId, $productId, 'SHIRINA_RASCHET'));
    $length = getWeightCalcFactor(getPropVal($iblockId, $productId, 'DLINA_RASCHET'));

    return round($coeff * $width * $length, 6);
}

/**
 * Вес погонного метра, кг/м = вес штуки / длина.
 */
function getProductWeightPerMeterKg($productId, $iblockId = 36)
{
    $pieceWeight = getProductPieceWeightKg($productId, $iblockId);
    $length = getWeightCalcFactor(getPropVal($iblockId, $productId, 'DLINA_RASCHET'));

    if ($length <= 0) {
        return $pieceWeight;
    }

    return round($pieceWeight / $length, 6);
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
    // колонка «Вес, кг» показывается во всех разделах каталога
    return true;
}

function isOnlyPiecesProduct($value)
{
    if ($value === null || $value === '') {
        return false;
    }

    $normalized = mb_strtolower(trim((string)$value));

    return in_array($normalized, ['да', 'y', 'yes', '1', 'true'], true);
}

/**
 * «ТОЛЬКО ШТ»: количество в метрах → кратно длине одной штуки.
 * Штуки всегда вверх (58.48 → 59).
 */
function snapOnlyPiecesValue($pieces)
{
    $pieces = (float)$pieces;
    if ($pieces <= 0) {
        return 1;
    }

    if (abs($pieces - round($pieces)) < 1e-6) {
        return max(1, (int)round($pieces));
    }

    return max(1, (int)ceil($pieces));
}

function snapOnlyPiecesMetersQuantity($metersQty, $lengthPerPiece)
{
    $metersQty = (float)$metersQty;
    $lengthPerPiece = (float)$lengthPerPiece;

    if ($lengthPerPiece <= 0) {
        return (float)snapOnlyPiecesValue($metersQty);
    }

    $pieces = snapOnlyPiecesValue($metersQty / $lengthPerPiece);

    return round($pieces * $lengthPerPiece, 5);
}

/**
 * «Только шт и 0,5 шт»: штуки кратно 0,5 (0.5, 1, 1.5, 2…).
 */
function snapHalfPiecesValue($pieces)
{
    $pieces = (float)$pieces;
    if ($pieces < 0.5) {
        return 0.5;
    }

    return round($pieces * 2) / 2;
}

/**
 * «Только шт и 0,5 шт»: метры → кратно 0,5 × Длина_Расчет.
 */
function snapHalfPiecesMetersQuantity($metersQty, $lengthPerPiece)
{
    $metersQty = (float)$metersQty;
    $lengthPerPiece = (float)$lengthPerPiece;

    if ($lengthPerPiece <= 0) {
        return snapHalfPiecesValue($metersQty);
    }

    $pieces = snapHalfPiecesValue($metersQty / $lengthPerPiece);

    return round($pieces * $lengthPerPiece, 5);
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

/**
 * Делит метраж: база (целые шаги) + кусок (остаток под наценку).
 */
function splitQuantityForPieceSurcharge($quantity, $stepMeters)
{
    $step = (float)$stepMeters;
    $qty = (float)$quantity;

    if ($step <= 0 || $qty <= 0) {
        return [
            'base_meters' => max(0, $qty),
            'surcharge_meters' => 0.0,
            'base_steps' => 0,
        ];
    }

    $steps = (int)floor(($qty / $step) + 1e-9);
    $baseMeters = round($steps * $step, 6);
    $surchargeMeters = round(max(0, $qty - $baseMeters), 6);

    if ($surchargeMeters <= 0.0001) {
        $surchargeMeters = 0.0;
        $baseMeters = $qty;
    }

    return [
        'base_meters' => $baseMeters,
        'surcharge_meters' => $surchargeMeters,
        'base_steps' => $steps,
    ];
}

/**
 * Средневзвешенная цена за метр: наценка только на кусок.
 */
function blendMeterPriceWithPieceSurcharge($basePrice, $surchargePrice, $baseMeters, $surchargeMeters)
{
    $basePrice = (float)$basePrice;
    $surchargePrice = (float)$surchargePrice;
    $baseMeters = (float)$baseMeters;
    $surchargeMeters = (float)$surchargeMeters;
    $total = $baseMeters + $surchargeMeters;

    if ($total <= 0) {
        return $basePrice;
    }

    if ($surchargeMeters <= 0.0001) {
        return round($basePrice, 2);
    }

    if ($baseMeters <= 0.0001) {
        return round($surchargePrice, 2);
    }

    return round((($baseMeters * $basePrice) + ($surchargeMeters * $surchargePrice)) / $total, 2);
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
    return 'Товар режется кратно 0,1 м без наценки. Длины кусков — кратно 0,1 м (например 1.2 3.5).';
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

    // Листы заказываем кратно 1 м длины, даже если в 1С стоит «Только шт и 0,5 шт»
    if (isOnlyPiecesProduct(getPropVal($iblockId, $productId, 'TOLKO_SHT'))) {
        return false;
    }

    return true;
}

/**
 * Лист с флагом «Только шт и 0,5 шт»: без любых +10% (неполная / сложная резка), только оплата резов.
 */
function basicSheetSkipsIncompletePieceSurcharge($productId, $iblockId = 36)
{
    if (!isBasicSheetProduct($productId, $iblockId)) {
        return false;
    }

    return isHalfPiecesProduct(getPropVal($iblockId, $productId, 'TOLKO_SHT_I_0_5_SHT'));
}

/** @deprecated alias — то же, что basicSheetSkipsIncompletePieceSurcharge */
function basicSheetSkipsAllPieceSurcharges($productId, $iblockId = 36)
{
    return basicSheetSkipsIncompletePieceSurcharge($productId, $iblockId);
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

    // Шаг заказа — 1 м по Длина_Расчет
    $lengthMeters = max(1, (int)round($length));

    return [
        'LENGTH_METERS' => $lengthMeters,
        'WIDTH_METERS' => $lengthMeters, // совместимость со старым ключом (число шагов по 1 м)
        'PIECE_STEP' => 1 / $lengthMeters,
        'AREA_STEP' => $width, // 1 м длины × ширина = м²
        'METER_STEP' => 1.0,
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
    $lengthUnits = max(1, (int)round(((float)$pieces) / $pieceStep));

    return round($lengthUnits * $pieceStep, 6);
}

function snapBasicSheetMetersByWidthMeter($metersQty, $lengthPerPiece, $width)
{
    $steps = getBasicSheetWidthMeterSteps($lengthPerPiece, $width);
    if (!$steps) {
        return (float)$metersQty;
    }

    $meterStep = $steps['METER_STEP'];
    $lengthUnits = max(1, (int)round(((float)$metersQty) / $meterStep));

    return round($lengthUnits * $meterStep, 5);
}

function getBasicSheetCuttingTipText()
{
    return 'Заказ в шт или м² — кратно 1 м длины. Резка по длине — кратно 0,1 м. '
        . 'Резы всегда оплачиваются по тарифу. '
        . 'Рез пополам (2 куска) — 1 рез. '
        . 'Больше резов — дополнительно +10% только на резанные куски. '
        . 'Неполная штука — +10% на кусок и 1 рез, целые без резки — по обычной цене.';
}

function getBasicSheetHalfPiecesCuttingTipText()
{
    return 'Заказ в шт или м² — кратно 1 м длины. Резка по длине — кратно 0,1 м. '
        . 'Резы всегда оплачиваются по тарифу. '
        . 'Наценок (+10%) нет ни за неполную штуку, ни за сложную резку — только стоимость резов.';
}

function getBasicSheetPiecesTipText()
{
    return 'Штуки или м² — кратно 1 м длины (например, при длине 6 м шаг = 1/6 шт ≈ ширина листа в м²).';
}

function getBasicSheetDimensionsTipText()
{
    return 'Длина и ширина листа заданы производителем. Количество — в штуках или м², кратно 1 м длины.';
}

function snapCutLengthMeters($meters)
{
    $meters = (float)$meters;
    if ($meters <= 0) {
        return 0.0;
    }

    return round($meters / 0.1) * 0.1;
}

function parseCutLengthsFromPlanSegment($segment)
{
    $segment = trim((string)$segment);
    if ($segment === '') {
        return [];
    }

    $lengths = [];
    foreach (preg_split('/\s*\+\s*/u', $segment) ?: [] as $part) {
        $part = trim(str_replace(',', '.', $part));
        if (preg_match('/^(\d+(?:\.\d+)?)/', $part, $matches)) {
            $value = snapCutLengthMeters($matches[1]);
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

function analyzeBasicSheetCuttingPlan($planText, $productId = null)
{
    $result = [
        'hasComplexCut' => false,
        'hasHalfCut' => false,
        'hasIncompletePlan' => false,
        'complexPieces' => 0,
        'simplePieces' => 0,
        'totalCuts' => 0,
        'complexCuts' => 0,
        'simpleCuts' => 0,
        'incompletePlanCuts' => 0,
        'incompletePlanCutsFee' => 0.0,
        'cutsFee' => 0.0,
    ];

    $priceByCode = [];
    $defaultCutPrice = 0.0;
    if ($productId) {
        foreach (getProductCuttingServices((int)$productId) as $service) {
            $code = (string)($service['CODE'] ?? '');
            $price = (float)($service['VALUE'] ?? 0);
            if ($code === '') {
                continue;
            }
            $priceByCode[$code] = $price;
            if ($defaultCutPrice <= 0 && $price > 0) {
                $defaultCutPrice = $price;
            }
        }
    }

    foreach (preg_split('/\R+/u', trim((string)$planText)) ?: [] as $line) {
        $line = trim((string)$line);
        if ($line === '') {
            continue;
        }

        $qty = 0;
        $typeCode = '';
        $cutsSegment = '';
        $isIncompleteLine = false;

        if (preg_match('/^неполн\w*\s*\|\s*([^|]+)\|\s*([^|]+)\s*\|/ui', $line, $matches)) {
            $isIncompleteLine = true;
            $qty = 1;
            $typeCode = trim($matches[1]);
            $cutsSegment = $matches[2];
        } elseif (preg_match('/^(\d+)\s*шт\s*\|\s*([^|]+)\|\s*([^|]+)\s*\|/ui', $line, $matches)) {
            $qty = (int)$matches[1];
            $typeCode = trim($matches[2]);
            $cutsSegment = $matches[3];
        } elseif (preg_match('/^(\d+)\s*шт\s*[—\-:]\s*(.+)$/ui', $line, $matches)) {
            $qty = (int)$matches[1];
            $cutsSegment = preg_replace('/\s*м\s*$/ui', '', trim($matches[2]));
        } elseif (preg_match('/^(\d+)\s*[-–—]\s*(.+)$/u', $line, $matches)) {
            $qty = (int)$matches[1];
            $cutsSegment = trim($matches[2]);
        }

        if ($qty <= 0) {
            continue;
        }

        $cutCount = getCutCountFromLengths(parseCutLengthsFromPlanSegment($cutsSegment));
        if ($cutCount <= 0) {
            continue;
        }

        $cutPrice = $priceByCode[$typeCode] ?? $defaultCutPrice;
        $lineFee = $cutCount * $qty * $cutPrice;
        $result['cutsFee'] += $lineFee;
        $result['totalCuts'] += $cutCount * $qty;

        if ($isIncompleteLine) {
            $result['hasIncompletePlan'] = true;
            $result['incompletePlanCuts'] += $cutCount * $qty;
            $result['incompletePlanCutsFee'] += $lineFee;
            continue;
        }

        if ($cutCount >= 2) {
            $result['hasComplexCut'] = true;
            $result['complexPieces'] += $qty;
            $result['complexCuts'] += $cutCount * $qty;
        } else {
            $result['hasHalfCut'] = true;
            $result['simplePieces'] += $qty;
            $result['simpleCuts'] += $qty;
        }
    }

    $result['cutsFee'] = round((float)$result['cutsFee'], 2);
    $result['incompletePlanCutsFee'] = round((float)$result['incompletePlanCutsFee'], 2);

    return $result;
}

/**
 * Детальная разбивка металла/резки для листа (целые / резанные / неполная / резы).
 */
function buildBasicSheetPositionBreakdown($productId, $quantity, $iblockId = 36, $planText = null)
{
    $length = getLengthProduct($iblockId, $productId);
    $quantity = (float)$quantity;
    if ($length <= 0 || $quantity <= 0 || !isBasicSheetProduct($productId, $iblockId)) {
        return null;
    }

    $basePriceRow = fetchCatalogPriceRow($productId, 17);
    if (!$basePriceRow) {
        return null;
    }

    $baseUnit = (float)$basePriceRow['PRICE'];
    $percent = getBasicSheetSurchargePercent();
    $surchargeUnit = round($baseUnit * (1 + $percent / 100), 2);
    $skipAllSurcharge = basicSheetSkipsIncompletePieceSurcharge($productId, $iblockId);

    if ($planText === null) {
        $planText = (string)(getBasketItemPropForPrice($productId, $quantity, 'CUTTING_PLAN_TEXT') ?? '');
        $enabled = (string)(getBasketItemPropForPrice($productId, $quantity, 'CUTTING_ENABLED') ?? '');
        if ($enabled !== 'Y') {
            $planText = '';
        }
    }

    $analysis = analyzeBasicSheetCuttingPlan($planText, $productId);
    $split = splitQuantityForPieceSurcharge($quantity, $length);

    $incompleteRaw = (float)$split['surcharge_meters'];
    $incompleteMeters = $skipAllSurcharge ? 0.0 : $incompleteRaw;
    $incompleteAtBaseMeters = $skipAllSurcharge ? $incompleteRaw : 0.0;
    $wholeMetersAvailable = round(max(0, $quantity - $incompleteRaw), 6);

    $complexPieces = min((int)$analysis['complexPieces'], (int)floor(($wholeMetersAvailable / $length) + 1e-9));
    $complexMeters = round($complexPieces * $length, 6);
    $simplePieces = min((int)$analysis['simplePieces'], max(0, (int)floor(($wholeMetersAvailable - $complexMeters) / $length + 1e-9)));
    $simpleMeters = round($simplePieces * $length, 6);

    $uncutMeters = round(max(0, $wholeMetersAvailable - $complexMeters - $simpleMeters), 6);
    $uncutPieces = $length > 0 ? $uncutMeters / $length : 0;
    $incompletePieces = $length > 0 ? $incompleteMeters / $length : 0;
    $incompleteAtBasePieces = $length > 0 ? $incompleteAtBaseMeters / $length : 0;

    $uncutSum = round($uncutMeters * $baseUnit, 2);
    $simpleSum = round($simpleMeters * $baseUnit, 2);
    // Флаг 0,5 шт: без +10% даже на сложную резку. Иначе +10% на резанные.
    $complexUnit = $skipAllSurcharge ? $baseUnit : $surchargeUnit;
    $complexSum = round($complexMeters * $complexUnit, 2);
    $incompleteSum = round($incompleteMeters * $surchargeUnit, 2);
    $incompleteAtBaseSum = round($incompleteAtBaseMeters * $baseUnit, 2);

    $metalSum = round($uncutSum + $simpleSum + $complexSum + $incompleteSum + $incompleteAtBaseSum, 2);
    $metalMeters = round($uncutMeters + $simpleMeters + $complexMeters + $incompleteMeters + $incompleteAtBaseMeters, 6);
    $blended = $metalMeters > 0.0001 ? round($metalSum / $metalMeters, 2) : $baseUnit;

    $lines = [];
    if ($uncutMeters > 0.0001) {
        $lines[] = [
            'KIND' => 'uncut',
            'LABEL' => 'Целые без резки',
            'TEXT' => formatIncompletePieceFraction($uncutPieces) . ' шт × ' . formatBasketMoney($length) . ' м = '
                . formatBasketMoney($uncutMeters) . ' м × ' . formatBasketMoney($baseUnit) . ' ₽ = '
                . formatBasketMoney($uncutSum) . ' ₽',
            'SUM' => $uncutSum,
        ];
    }
    if ($simpleMeters > 0.0001) {
        $cuts = (int)$analysis['simpleCuts'];
        $lines[] = [
            'KIND' => 'simple_cut',
            'LABEL' => 'Резанные (1 рез)',
            'TEXT' => formatIncompletePieceFraction($simplePieces) . ' шт × ' . formatBasketMoney($length) . ' м = '
                . formatBasketMoney($simpleMeters) . ' м × ' . formatBasketMoney($baseUnit) . ' ₽ = '
                . formatBasketMoney($simpleSum) . ' ₽'
                . ($cuts > 0 ? ' · резов: ' . $cuts : ''),
            'SUM' => $simpleSum,
        ];
    }
    if ($complexMeters > 0.0001) {
        $cuts = (int)$analysis['complexCuts'];
        if ($skipAllSurcharge) {
            $lines[] = [
                'KIND' => 'complex_cut',
                'LABEL' => 'Резанные',
                'TEXT' => formatIncompletePieceFraction($complexPieces) . ' шт × ' . formatBasketMoney($length) . ' м = '
                    . formatBasketMoney($complexMeters) . ' м × ' . formatBasketMoney($baseUnit) . ' ₽ = '
                    . formatBasketMoney($complexSum) . ' ₽'
                    . ($cuts > 0 ? ' · резов: ' . $cuts . ' (без наценки)' : ' (без наценки)'),
                'SUM' => $complexSum,
            ];
        } else {
            $lines[] = [
                'KIND' => 'complex_cut',
                'LABEL' => 'Резанные (сложная резка)',
                'TEXT' => formatIncompletePieceFraction($complexPieces) . ' шт × ' . formatBasketMoney($length) . ' м = '
                    . formatBasketMoney($complexMeters) . ' м × ' . formatBasketMoney($surchargeUnit) . ' ₽ (+'
                    . $percent . '%) = ' . formatBasketMoney($complexSum) . ' ₽'
                    . ($cuts > 0 ? ' · резов: ' . $cuts : ''),
                'SUM' => $complexSum,
            ];
        }
    }
    if ($incompleteMeters > 0.0001) {
        $lines[] = [
            'KIND' => 'incomplete',
            'LABEL' => 'Неполная штука',
            'TEXT' => formatIncompletePieceFraction($incompletePieces) . ' шт = '
                . formatBasketMoney($incompleteMeters) . ' м × ' . formatBasketMoney($surchargeUnit) . ' ₽ (+'
                . $percent . '%) = ' . formatBasketMoney($incompleteSum) . ' ₽',
            'SUM' => $incompleteSum,
        ];
    }
    if ($incompleteAtBaseMeters > 0.0001) {
        $lines[] = [
            'KIND' => 'incomplete_free',
            'LABEL' => 'Неполная (без наценки)',
            'TEXT' => formatIncompletePieceFraction($incompleteAtBasePieces) . ' шт = '
                . formatBasketMoney($incompleteAtBaseMeters) . ' м × ' . formatBasketMoney($baseUnit) . ' ₽ = '
                . formatBasketMoney($incompleteAtBaseSum) . ' ₽',
            'SUM' => $incompleteAtBaseSum,
        ];
    }

    $complexCuts = (int)($analysis['complexCuts'] ?? 0);
    $simpleCuts = (int)($analysis['simpleCuts'] ?? 0);
    $totalCuts = (int)($analysis['totalCuts'] ?? 0);
    $cutsFee = (float)($analysis['cutsFee'] ?? 0);

    // Неполная штука = 1 авто-рез, если нет партии «неполная» в плане
    $incompleteCutFee = 0.0;
    $incompleteCutCount = 0;
    $hasIncompletePlan = !empty($analysis['hasIncompletePlan']);
    if ($incompleteRaw > 0.0001 && !$hasIncompletePlan) {
        $incompleteCutCount = 1;
        $defaultCutPrice = 0.0;
        foreach (getProductCuttingServices((int)$productId) as $service) {
            $price = (float)($service['VALUE'] ?? 0);
            if ($price > 0) {
                $defaultCutPrice = $price;
                break;
            }
        }
        $incompleteCutFee = $defaultCutPrice;
        $cutsFee = round($cutsFee + $incompleteCutFee, 2);
        $totalCuts += $incompleteCutCount;
    }

    if ($totalCuts > 0 || $cutsFee > 0.0001) {
        $cutsText = formatBasketMoney($cutsFee) . ' ₽';
        if ($totalCuts > 0) {
            $cutsText .= ' (' . $totalCuts . ' рез.)';
        }
        if ($hasIncompletePlan && (int)($analysis['incompletePlanCuts'] ?? 0) > 0) {
            $cutsText .= ' · неполная по плану: ' . (int)$analysis['incompletePlanCuts'] . ' рез.';
        } elseif ($incompleteCutCount > 0) {
            $cutsText .= ' · в т.ч. 1 рез за неполную '
                . formatIncompletePieceFraction($length > 0 ? $incompleteRaw / $length : 0) . ' шт';
        }
        if ($complexCuts > 0 && !$skipAllSurcharge) {
            $cutsText .= ' · плюс +' . $percent . '% на резанные куски';
        }
        $lines[] = [
            'KIND' => 'cuts_fee',
            'LABEL' => 'Оплата резов',
            'TEXT' => $cutsText,
            'SUM' => $cutsFee,
        ];
    }

    $grandTotal = round($metalSum + $cutsFee, 2);

    $lines[] = [
        'KIND' => 'metal_total',
        'LABEL' => 'Металл',
        'TEXT' => 'Металл: ' . formatBasketMoney($metalSum) . ' ₽ · средняя '
            . formatBasketMoney($blended) . ' ₽/м',
        'SUM' => $metalSum,
    ];
    if ($cutsFee > 0.0001) {
        $lines[] = [
            'KIND' => 'grand_total',
            'LABEL' => 'С резкой',
            'TEXT' => 'С резкой: ' . formatBasketMoney($grandTotal) . ' ₽',
            'SUM' => $grandTotal,
        ];
    }

    $hasSurcharge = !$skipAllSurcharge && ($complexMeters > 0.0001 || $incompleteMeters > 0.0001);

    return [
        'PERCENT' => $percent,
        'BASE_UNIT' => $baseUnit,
        'SURCHARGE_UNIT' => $surchargeUnit,
        'BLENDED_PRICE' => $blended,
        'METAL_SUM' => $metalSum,
        'CUTS_FEE' => $cutsFee,
        'GRAND_TOTAL' => $grandTotal,
        'UNCUT_METERS' => $uncutMeters,
        'SIMPLE_METERS' => $simpleMeters,
        'COMPLEX_METERS' => $complexMeters,
        'INCOMPLETE_METERS' => $incompleteMeters,
        'COMPLEX_PIECES' => $complexPieces,
        'SIMPLE_PIECES' => $simplePieces,
        'UNCUT_PIECES' => $uncutPieces,
        'INCOMPLETE_PIECES' => $incompletePieces,
        'ANALYSIS' => $analysis,
        'LINES' => $lines,
        'HAS_SURCHARGE' => $hasSurcharge,
        'NOTE' => $hasSurcharge
            ? ('Разбивка цены · средняя ' . formatBasketMoney($blended) . ' ₽/м')
            : 'Цена за метр',
    ];
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

/**
 * Текст/данные разбивки: целые по обычной цене, кусок с наценкой.
 */
function buildPieceSurchargeBreakdown(array $split, $lengthPerPiece, $percent, $baseUnitPrice, $surchargeUnitPrice, $blendedPrice)
{
    $length = (float)$lengthPerPiece;
    $baseMeters = (float)($split['base_meters'] ?? 0);
    $surchargeMeters = (float)($split['surcharge_meters'] ?? 0);
    $percent = (int)$percent;

    $basePieces = $length > 0 ? $baseMeters / $length : 0;
    $surchargePieces = $length > 0 ? $surchargeMeters / $length : 0;

    $basePiecesText = formatIncompletePieceFraction($basePieces);
    // для целых шагов 0.5 показывать как 0.5
    if ($length > 0 && abs($basePieces - round($basePieces * 2) / 2) < 0.001) {
        $halfSteps = round($basePieces * 2) / 2;
        $basePiecesText = (abs($halfSteps - (int)$halfSteps) < 0.001)
            ? (string)(int)$halfSteps
            : rtrim(rtrim(number_format($halfSteps, 1, '.', ''), '0'), '.');
    }

    $surchargePiecesText = formatIncompletePieceFraction($surchargePieces);

    $lines = [];
    if ($baseMeters > 0.0001) {
        $lines[] = [
            'TEXT' => $basePiecesText . ' шт — обычная цена ('
                . formatBasketMoney($baseUnitPrice) . '/м)',
        ];
    }
    if ($surchargeMeters > 0.0001) {
        $lines[] = [
            'TEXT' => 'Кусок ' . $surchargePiecesText . ' шт — +' . $percent . '% ('
                . formatBasketMoney($surchargeUnitPrice) . '/м)',
        ];
    }

    $note = 'Наценка +' . $percent . '% только на кусок';
    if ($blendedPrice > 0) {
        $note .= ' · средняя ' . formatBasketMoney($blendedPrice) . '/м';
    }

    $plainLines = [];
    foreach ($lines as $line) {
        $plainLines[] = $line['TEXT'];
    }

    return [
        'PERCENT' => $percent,
        'NOTE' => $note,
        'LINES' => $lines,
        'BASE_METERS' => $baseMeters,
        'SURCHARGE_METERS' => $surchargeMeters,
        'BASE_PIECES_TEXT' => $basePiecesText,
        'SURCHARGE_PIECES_TEXT' => $surchargePiecesText,
        'BASE_UNIT_PRICE' => (float)$baseUnitPrice,
        'SURCHARGE_UNIT_PRICE' => (float)$surchargeUnitPrice,
        'BLENDED_PRICE' => (float)$blendedPrice,
        'HTML' => implode("\n", array_merge([$note], $plainLines)),
    ];
}

function formatPieceSurchargePriceNote(array $split, $lengthPerPiece, $percent, $baseUnitPrice, $surchargeUnitPrice, $blendedPrice)
{
    $breakdown = buildPieceSurchargeBreakdown(
        $split,
        $lengthPerPiece,
        $percent,
        $baseUnitPrice,
        $surchargeUnitPrice,
        $blendedPrice
    );

    return $breakdown['HTML'];
}

function formatBasketMoney($value)
{
    return number_format((float)$value, 2, '.', ' ');
}

function basicSheetQuantityNeedsPlus10($productId, $quantity, $iblockId = 36)
{
    if (!isBasicSheetProduct($productId, $iblockId)) {
        return false;
    }

    if (basicSheetSkipsIncompletePieceSurcharge($productId, $iblockId)) {
        return false;
    }

    $length = getLengthProduct($iblockId, $productId);

    return quantityNeedsMeterSurcharge($quantity, $length);
}

/**
 * Данные наценки за кусок для позиции корзины (или null).
 */
function resolveBasketPieceSurchargeData(array $rowData)
{
    $productId = (int)($rowData['PRODUCT_ID'] ?? 0);
    $quantity = (float)($rowData['QUANTITY'] ?? 0);

    if ($productId <= 0 || $quantity <= 0 || !isCustomPrice(36, $productId)) {
        return null;
    }

    $length = getLengthProduct(36, $productId);
    if ($length <= 0) {
        return null;
    }

    $basePriceRow = fetchCatalogPriceRow($productId, 17);
    if (!$basePriceRow) {
        return null;
    }

    $baseUnit = (float)$basePriceRow['PRICE'];

    if (isBasicSheetProduct($productId)) {
        $breakdown = buildBasicSheetPositionBreakdown($productId, $quantity);
        if (!$breakdown || empty($breakdown['HAS_SURCHARGE'])) {
            return null;
        }

        $displayLines = [];
        foreach ($breakdown['LINES'] as $line) {
            if (($line['KIND'] ?? '') === 'metal_total') {
                continue;
            }
            $displayLines[] = [
                'TEXT' => ($line['LABEL'] ?? '') . ': ' . ($line['TEXT'] ?? ''),
            ];
        }
        $displayLines[] = [
            'TEXT' => $breakdown['LINES'][count($breakdown['LINES']) - 1]['TEXT'] ?? '',
        ];

        return [
            'PERCENT' => (int)$breakdown['PERCENT'],
            'NOTE' => (string)$breakdown['NOTE'],
            'LINES' => $displayLines,
            'BASE_METERS' => (float)$breakdown['UNCUT_METERS'] + (float)$breakdown['SIMPLE_METERS'],
            'SURCHARGE_METERS' => (float)$breakdown['COMPLEX_METERS'] + (float)$breakdown['INCOMPLETE_METERS'],
            'BASE_PIECES_TEXT' => formatIncompletePieceFraction((float)$breakdown['UNCUT_PIECES'] + (float)$breakdown['SIMPLE_PIECES']),
            'SURCHARGE_PIECES_TEXT' => formatIncompletePieceFraction((float)$breakdown['COMPLEX_PIECES'] + (float)$breakdown['INCOMPLETE_PIECES']),
            'BASE_UNIT_PRICE' => (float)$breakdown['BASE_UNIT'],
            'SURCHARGE_UNIT_PRICE' => (float)$breakdown['SURCHARGE_UNIT'],
            'BLENDED_PRICE' => (float)$breakdown['BLENDED_PRICE'],
            'HTML' => implode("\n", array_merge(
                [(string)$breakdown['NOTE']],
                array_map(static function ($l) {
                    return (string)($l['TEXT'] ?? '');
                }, $displayLines)
            )),
            'DETAIL' => $breakdown,
        ];
    }

    if (productAllowsFreeMeterCutting($productId)) {
        return null;
    }

    $half = $length / 2;
    if (!quantityNeedsMeterSurcharge($quantity, $half)) {
        return null;
    }

    $split = splitQuantityForPieceSurcharge($quantity, $half);
    if ($split['surcharge_meters'] <= 0.0001) {
        return null;
    }

    $plus20 = fetchCatalogPriceRow($productId, 18);
    $surchargeUnit = $plus20 ? (float)$plus20['PRICE'] : round($baseUnit * 1.2, 2);
    $blended = blendMeterPriceWithPieceSurcharge(
        $baseUnit,
        $surchargeUnit,
        $split['base_meters'],
        $split['surcharge_meters']
    );

    return buildPieceSurchargeBreakdown($split, $length, 20, $baseUnit, $surchargeUnit, $blended);
}

function resolveBasketSurchargePercent(array $rowData)
{
    $pieceData = resolveBasketPieceSurchargeData($rowData);
    if ($pieceData) {
        return (int)$pieceData['PERCENT'];
    }

    if (!empty($rowData['CUTTING_SURCHARGE_10'])) {
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

    if (!\Bitrix\Main\Loader::includeModule('catalog')) {
        return;
    }

    $currency = (string)$rowData['CURRENCY'];
    $quantity = (float)$rowData['QUANTITY'];

    if (isBasicSheetProduct($productId)) {
        $planText = !empty($rowData['CUTTING_ENABLED'])
            ? (string)($rowData['CUTTING_PLAN_TEXT'] ?? '')
            : '';
        $breakdown = buildBasicSheetPositionBreakdown($productId, $quantity, 36, $planText);
        if ($breakdown) {
            $blended = (float)$breakdown['BLENDED_PRICE'];
            $cutsFee = (float)($breakdown['CUTS_FEE'] ?? 0);
            $grandTotal = (float)($breakdown['GRAND_TOTAL'] ?? 0);
            // сумма позиции = металл + наценки + резы (как шаг «Итого»)
            $sum = $grandTotal > 0
                ? \Bitrix\Sale\PriceMaths::roundPrecision($grandTotal)
                : \Bitrix\Sale\PriceMaths::roundPrecision($blended * $quantity);
            $price = $quantity > 0.0001 ? round($sum / $quantity, 2) : $blended;
            $rowData['NOTES'] = $cutsFee > 0.0001
                ? ('С резкой · ' . formatBasketMoney($price) . ' ₽/м')
                : (string)$breakdown['NOTE'];
            $rowData['PRICE'] = $price;
            $rowData['PRICE_FORMATED'] = CCurrencyLang::CurrencyFormat($price, $currency, true);
            $rowData['FULL_PRICE'] = $price;
            $rowData['FULL_PRICE_FORMATED'] = $rowData['PRICE_FORMATED'];
            $rowData['DISCOUNT_PRICE'] = 0;
            $rowData['SHOW_DISCOUNT_PRICE'] = false;

            $rowData['SUM_PRICE'] = $sum;
            $rowData['SUM_PRICE_FORMATED'] = CCurrencyLang::CurrencyFormat($sum, $currency, true);

            $hasDetail = !empty($breakdown['HAS_SURCHARGE']) || ((float)($breakdown['CUTS_FEE'] ?? 0) > 0.0001);
            if ($hasDetail) {
                $displayLines = [];
                foreach ($breakdown['LINES'] as $line) {
                    $kind = (string)($line['KIND'] ?? '');
                    if (in_array($kind, ['metal_total', 'grand_total'], true)) {
                        $displayLines[] = ['TEXT' => (string)($line['TEXT'] ?? '')];
                        continue;
                    }
                    $displayLines[] = [
                        'TEXT' => ($line['LABEL'] ?? '') . ': ' . ($line['TEXT'] ?? ''),
                    ];
                }
                $rowData['SURCHARGE_BREAKDOWN'] = $breakdown;
                $rowData['SURCHARGE_BREAKDOWN_LINES'] = $displayLines;
                $rowData['HAS_PIECE_SURCHARGE'] = true;
            } else {
                $rowData['HAS_PIECE_SURCHARGE'] = false;
                $rowData['SURCHARGE_BREAKDOWN_LINES'] = [];
            }

            return;
        }
    }

    $pieceData = resolveBasketPieceSurchargeData($rowData);

    if ($pieceData) {
        $price = (float)$pieceData['BLENDED_PRICE'];
        $rowData['NOTES'] = $pieceData['NOTE'];
        $rowData['SURCHARGE_BREAKDOWN'] = $pieceData;
        $rowData['SURCHARGE_BREAKDOWN_LINES'] = $pieceData['LINES'];
        $rowData['HAS_PIECE_SURCHARGE'] = true;

        $rowData['PRICE'] = $price;
        $rowData['PRICE_FORMATED'] = CCurrencyLang::CurrencyFormat($price, $currency, true);
        $rowData['FULL_PRICE'] = $price;
        $rowData['FULL_PRICE_FORMATED'] = $rowData['PRICE_FORMATED'];
        $rowData['DISCOUNT_PRICE'] = 0;
        $rowData['SHOW_DISCOUNT_PRICE'] = false;

        $sum = \Bitrix\Sale\PriceMaths::roundPrecision($price * $quantity);
        $rowData['SUM_PRICE'] = $sum;
        $rowData['SUM_PRICE_FORMATED'] = CCurrencyLang::CurrencyFormat($sum, $currency, true);

        return;
    }

    $rowData['HAS_PIECE_SURCHARGE'] = false;
    $rowData['SURCHARGE_BREAKDOWN_LINES'] = [];
    $rowData['NOTES'] = formatBasketPriceNoteLabel(null);
}

function getSheetCuttingTipText()
{
    return 'Заказ в шт или м² — кратно 1 м длины. Без наценки за неполную штуку — только стоимость резов.';
}

function getHalfPiecesCuttingLegendText($isSheet = false)
{
    return $isSheet
        ? 'Кратно 1 м длины · без наценки за кусок · только резы'
        : 'Режется кратно 0,1 м без наценки';
}

function isWeightFrom500Product($value)
{
    return isOnlyPiecesProduct($value);
}

/** Порог «заказ по весу» из свойств товара: 500 / 1000 / null. */
function getProductMinBulkWeightKg(array $properties)
{
    $min = null;

    if (isWeightFrom500Product($properties['SHT_M_VES_OT500_KG']['VALUE'] ?? '')) {
        $min = 500;
    }

    if (isWeightFrom500Product($properties['SHT_M_VES_OT_1000_KG']['VALUE'] ?? '')) {
        $min = max((int)$min, 1000);
    }

    return $min;
}

function getMinBulkWeightKg()
{
    return 500;
}

function getWeightFrom500TipText($minBulkWeight = null)
{
    $min = (int)($minBulkWeight ?? getMinBulkWeightKg());

    return 'До ' . $min . ' кг — заказ штуками или метрами. От ' . $min . ' кг — можно указать вес с точностью до грамм.';
}

function getWeightFrom500PiecesTipText($minBulkWeight = null)
{
    $min = (int)($minBulkWeight ?? getMinBulkWeightKg());

    return 'Вес считается из штук или метров. Чтобы заказать от ' . $min . ' кг по весу — кликните в поле веса и введите значение.';
}

function getWeightFrom500BulkTipText($minBulkWeight = null)
{
    $min = (int)($minBulkWeight ?? getMinBulkWeightKg());

    return 'Заказ по весу от ' . $min . ' кг. Метры и штуки пересчитываются автоматически.';
}

function formatBulkWeightBadgeLabel($minBulkWeight)
{
    return (int)$minBulkWeight . '+';
}

function getBulkWeightLegendText($minBulkWeight)
{
    $min = (int)$minBulkWeight;

    return 'От ' . $min . ' кг можно заказать по весу';
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
    $weightPerMeter = getProductWeightPerMeterKg($productId, $iblockId);
    $width = floatval(getPropVal($iblockId, $productId, 'SHIRINA_RASCHET'));
    $onlyPieces = isOnlyPiecesProduct(getPropVal($iblockId, $productId, 'TOLKO_SHT'));
    $halfPieces = isHalfPiecesProduct(getPropVal($iblockId, $productId, 'TOLKO_SHT_I_0_5_SHT'));
    $basicSheet = isBasicSheetProduct($productId, $iblockId);

    if ($onlyPieces && $lengthPerPiece > 0) {
        $metersQty = snapOnlyPiecesMetersQuantity($metersQty, $lengthPerPiece);
    } elseif ($halfPieces && $lengthPerPiece > 0) {
        $metersQty = snapHalfPiecesMetersQuantity($metersQty, $lengthPerPiece);
    }

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
        $isSheet = isSheetProduct($width);
        $wholeSheetPieces = $isSheet && !$halfPieces && !$basicSheet;

        if ($onlyPieces) {
            $piecesFormatted = (string)max(1, (int)round($pieces));
        } elseif ($halfPieces) {
            $snappedPieces = snapHalfPiecesValue($pieces);
            $piecesFormatted = abs($snappedPieces - round($snappedPieces)) < 0.01
                ? (string)(int)round($snappedPieces)
                : formatBasketQtyNumber($snappedPieces, 1);
        } elseif ($basicSheet && $width > 0 && $lengthPerPiece > 0) {
            $piecesFormatted = formatBasketQtyNumber(
                snapBasicSheetPiecesByWidthMeter($pieces, $lengthPerPiece, $width),
                3
            );
        } elseif ($wholeSheetPieces) {
            $piecesFormatted = (string)max(1, (int)round($pieces));
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

function formatCatalogColumnHeaderHtml($title, $formula = '')
{
    $title = trim((string)$title);
    $formula = trim((string)$formula);
    $html = '<span class="product-table_th-title">' . $title . '</span>';
    if ($formula !== '') {
        $html .= '<span class="product-table_th-formula">' . htmlspecialcharsbx($formula) . '</span>';
    }

    return $html;
}

function formatCatalogPriceHeaderHtml($priceName, $xmlId, $isSquareMeter, $sectionCode = '')
{
    $xmlId = (string)$xmlId;
    $priceName = (string)$priceName;
    $isPlusPrice = $xmlId === 'PER_METER_PLUS20' || strpos($priceName, '+') !== false;

    if ($xmlId === 'PRICE_PER_KG') {
        return formatCatalogColumnHeaderHtml('Цена за кг', '1–1000 × Коэффициент_Расчет');
    }

    if ($xmlId === 'PER_METER') {
        if ($isSquareMeter) {
            return '<span class="product-table_th-title"><span class="price-header">Цена за м<sup>2</sup></span></span>'
                . '<span class="product-table_th-formula">' . htmlspecialcharsbx('1–1000 × Коэффициент_Расчет') . '</span>';
        }

        return formatCatalogColumnHeaderHtml(
            htmlspecialcharsbx($priceName !== '' ? $priceName : 'Цена за метр'),
            '1–1000 × Коэффициент_Расчет'
        );
    }

    if ($isPlusPrice) {
        $surcharge = $isSquareMeter ? getSquareMeterSurchargePercent($sectionCode) : 20;
        if ($isSquareMeter) {
            $coef = $surcharge
                ? '<span class="price-header__coef">+' . $surcharge . '%</span>'
                : '';
            $formula = $surcharge
                ? ('цена за м² × 1,' . (int)$surcharge)
                : 'цена за м² × 1,2';

            return '<span class="product-table_th-title"><span class="price-header">Цена за м<sup>2</sup>' . $coef . '</span></span>'
                . '<span class="product-table_th-formula">' . htmlspecialcharsbx($formula) . '</span>';
        }

        return formatCatalogColumnHeaderHtml(
            htmlspecialcharsbx($priceName !== '' ? $priceName : 'Цена за метр +20%'),
            'цена за метр × 1,2'
        );
    }

    if ($isSquareMeter) {
        return '<span class="product-table_th-title"><span class="price-header">Цена за м<sup>2</sup></span></span>';
    }

    return formatCatalogColumnHeaderHtml(htmlspecialcharsbx($priceName));
}