<?php

// Обработчик для обновления цены при обмене с 1С

class PriceUpdater {

    private static $iblockId = "36";
    private static $priceTypeId = 16;
    private static $pricePerMeterId = 17;
    private static $pricePerMeterPlus20Id = 18;
    private static $meterMeasureId = 1;
    private static $ratioValue = 1;

    public static function recalculatePricesAfter1C($arParams, $arFields)
    {
        if (!CModule::IncludeModule("catalog") || !CModule::IncludeModule("iblock")) return;

        $elements = CIBlockElement::GetList(
            [],
            [
                "IBLOCK_ID" => self::$iblockId,
                ">=TIMESTAMP_X" => ConvertTimeStamp(time() - 3600, "FULL"),
                "ACTIVE" => "Y"
            ],
            false,
            false,
            ["ID", "IBLOCK_ID"]
        );

        while ($element = $elements->Fetch()) {
            self::syncProductPrices((int)$element['ID']);
        }
    }

    public static function syncProductPrices($productId)
    {
        $productId = (int)$productId;

        if ($productId <= 0 || !CModule::IncludeModule("catalog") || !CModule::IncludeModule("iblock")) {
            return false;
        }

        $propValues = self::getPropertyValues($productId, self::$iblockId);
        $calculatePerMeterPrice = self::calculatePerMeterPrice($productId, $propValues);

        if ($calculatePerMeterPrice <= 0) {
            return false;
        }

        self::updateMeasure($productId, self::$meterMeasureId, self::$ratioValue);
        self::updatePrice($productId, self::$pricePerMeterId, $calculatePerMeterPrice);
        self::updatePrice($productId, self::$pricePerMeterPlus20Id, self::calculatePerMeterPlus20Price($productId, $propValues));

        return true;
    }

    private static function calculatePerMeterPrice($productId, $propValues)
    {
        $basePrice = self::getPrice($productId, self::$priceTypeId);
        $coefficient = self::getCoefficientRaschet($propValues);

        if (!$basePrice) {
            return 0;
        }

        $price = (float) $basePrice["PRICE"];

        return $price * $coefficient;
    }

    private static function calculatePerMeterPlus20Price($productId, $propValues)
    {
        return 1.2 * self::calculatePerMeterPrice($productId, $propValues);
    }

    private static function getPropertyValues($elementId, $iblockId)
    {
        $propValues = [];
        $properties = CIBlockElement::GetProperty($iblockId, $elementId, [], []);

        while ($prop = $properties->Fetch()) {
            $propValues[$prop["CODE"]] = $prop["VALUE"];
        }

        return $propValues;
    }

    private static function getPrice($elementId, $priceId)
    {
        return CPrice::GetList([], [
            "PRODUCT_ID" => $elementId,
            "CATALOG_GROUP_ID" => $priceId
        ])->Fetch();
    }

    private static function updatePrice($elementId, $priceId, $newPrice)
    {
        $arFields = Array(
            "PRODUCT_ID" => $elementId,
            "CATALOG_GROUP_ID" => $priceId,
            "PRICE" => $newPrice,
            "CURRENCY" => "RUB",
        );

        if ($arr = self::getPrice($elementId, $priceId))
        {
            CPrice::Update($arr["ID"], $arFields);
        }
        else
        {
            CPrice::Add($arFields);
        }
    }

    private static function getCoefficientRaschet($propValues)
    {
        return (float) ($propValues["KOEFFITSENT_RASCHET"] ?? 0);
    }

    private static function updateMeasure($productId, $measureId, $ratioValue)
    {
        $result = \Bitrix\Catalog\ProductTable::update($productId, [
            'MEASURE' => $measureId
        ]);

        if ($result->isSuccess()) {

            $ratioRow = \Bitrix\Catalog\MeasureRatioTable::getList([
                'select' => ['ID'],
                'filter' => ['=PRODUCT_ID' => $productId],
                'limit' => 1
            ])->fetch();

            if ($ratioRow) {
                \Bitrix\Catalog\MeasureRatioTable::update($ratioRow['ID'], [
                    'RATIO' => $ratioValue
                ]);
            } else {
                \Bitrix\Catalog\MeasureRatioTable::add([
                    'PRODUCT_ID' => $productId,
                    'RATIO' => $ratioValue,
                    'IS_DEFAULT' => 'Y'
                ]);
            }
        }
    }
}
