;(function() {
	'use strict';

	function formatQtyNumber(value, decimals)
	{
		if (value === null || value === '' || isNaN(value))
		{
			return '';
		}

		var formatted = parseFloat(value).toFixed(decimals);
		return formatted.replace(/\.?0+$/, '');
	}

	function parseInputNumber(raw)
	{
		var num = parseFloat(String(raw || '').replace(/\s/g, '').replace(',', '.'));
		return isNaN(num) ? null : num;
	}

	function getPiecesStep(itemData)
	{
		if (isBasicSheetWidthMeterItem(itemData)) {
			var steps = getBasicSheetWidthSteps(itemData);
			return steps ? steps.piecesStep : 1;
		}

		if (isWholeSheetItem(itemData)) {
			return 1;
		}

		return 1;
	}

	function isBasicSheetWidthMeterItem(itemData)
	{
		return !!(
			itemData
			&& itemData.BASIC_SHEET
			&& parseFloat(itemData.BASKET_WIDTH) > 0
			&& parseFloat(itemData.BASKET_LENGTH_PER_PIECE) > 0
		);
	}

	function getBasicSheetWidthSteps(itemData)
	{
		var length = parseFloat(itemData.BASKET_LENGTH_PER_PIECE) || 0;
		var width = parseFloat(itemData.BASKET_WIDTH) || 0;
		if (length <= 0 || width <= 0) {
			return null;
		}

		// Шаг — 1 м по длине листа (Длина_Расчет)
		var lengthMeters = Math.max(1, Math.round(length));
		var piecesStep = 1 / lengthMeters;

		return {
			lengthMeters: lengthMeters,
			widthMeters: lengthMeters,
			piecesStep: piecesStep,
			areaStep: width,
			metersStep: 1,
			minPieces: piecesStep,
			minArea: width,
			minMeters: 1
		};
	}

	function isWholeSheetItem(itemData)
	{
		if (!itemData) {
			return false;
		}

		if (itemData.BASIC_SHEET) {
			return false;
		}

		if (itemData.WHOLE_SHEET_PIECES) {
			return true;
		}

		return !!itemData.IS_SHEET && !itemData.HALF_PIECES;
	}

	function enrichItemDataFromRow(itemData, target)
	{
		if (!itemData || !target) {
			return itemData;
		}

		var row = BX.findParent(target, {attrs: {'data-entity': 'basket-item'}});
		if (!row) {
			return itemData;
		}

		var merged = BX.clone(itemData);
		if (String(row.getAttribute('data-half-pieces') || '0') === '1') {
			merged.HALF_PIECES = true;
		} else {
			merged.HALF_PIECES = false;
		}
		if (String(row.getAttribute('data-free-cutting') || '0') === '1') {
			merged.FREE_CUTTING_1M = true;
		} else {
			merged.FREE_CUTTING_1M = false;
		}
		if (String(row.getAttribute('data-basic-sheet') || '0') === '1') {
			merged.BASIC_SHEET = true;
		} else {
			merged.BASIC_SHEET = false;
		}
		if (String(row.getAttribute('data-whole-sheet-pieces') || '0') === '1') {
			merged.WHOLE_SHEET_PIECES = true;
		} else {
			merged.WHOLE_SHEET_PIECES = false;
		}
		if (String(row.getAttribute('data-is-sheet') || '0') === '1') {
			merged.IS_SHEET = true;
		}

		var lengthPerPiece = parseFloat(row.getAttribute('data-length-per-piece') || '0');
		if (lengthPerPiece > 0) {
			merged.BASKET_LENGTH_PER_PIECE = lengthPerPiece;
		}

		var width = parseFloat(row.getAttribute('data-basket-width') || row.getAttribute('data-cutting-stock') || '0');
		if (width > 0) {
			merged.BASKET_WIDTH = width;
		}

		return merged;
	}

	function isFreeMeterCuttingItem(itemData)
	{
		return !!(itemData && itemData.FREE_CUTTING_1M && !itemData.IS_SHEET);
	}

	function enrichItemDataById(itemData)
	{
		if (!itemData) {
			return itemData;
		}

		var row = document.getElementById('basket-item-' + itemData.ID);
		if (!row) {
			return itemData;
		}

		return enrichItemDataFromRow(itemData, row);
	}

	function snapPiecesValue(pieces, itemData)
	{
		if (isBasicSheetWidthMeterItem(itemData)) {
			var steps = getBasicSheetWidthSteps(itemData);
			if (!steps) {
				return pieces;
			}

			var widthUnits = Math.max(1, Math.round(pieces / steps.piecesStep));
			return parseFloat((widthUnits * steps.piecesStep).toFixed(6));
		}

		var step = getPiecesStep(itemData);
		if (pieces < 1) {
			return 1;
		}

		return Math.round(pieces);
	}

	function formatPiecesDisplay(pieces, itemData)
	{
		if (isBasicSheetWidthMeterItem(itemData)) {
			return formatQtyNumber(snapPiecesValue(pieces, itemData), 3);
		}

		if (isWholeSheetItem(itemData)) {
			return String(Math.max(1, Math.round(pieces)));
		}

		if (isFreeMeterCuttingItem(itemData)) {
			return Math.abs(pieces - Math.round(pieces)) < 0.01
				? String(Math.round(pieces))
				: formatQtyNumber(pieces, 2);
		}

		return Math.abs(pieces - Math.round(pieces)) < 0.01
			? String(Math.round(pieces))
			: formatQtyNumber(pieces, 2);
	}

	function isPipeMeterStepItem(itemData)
	{
		// прутки/трубы: метры только целыми
		return !!(itemData && !itemData.IS_SHEET && !isWholeSheetItem(itemData));
	}

	function snapMetersForItem(metersQty, itemData)
	{
		var lengthPerPiece = parseFloat(itemData.BASKET_LENGTH_PER_PIECE) || 0;
		metersQty = parseFloat(metersQty);
		if (isNaN(metersQty) || metersQty < 0)
		{
			return metersQty;
		}

		if (isFreeMeterCuttingItem(itemData))
		{
			return Math.max(1, Math.round(metersQty));
		}

		if (isBasicSheetWidthMeterItem(itemData) && lengthPerPiece > 0)
		{
			var basicSteps = getBasicSheetWidthSteps(itemData);
			var widthUnits = Math.max(1, Math.round(metersQty / basicSteps.metersStep));
			return parseFloat((widthUnits * basicSteps.metersStep).toFixed(5));
		}

		if (isWholeSheetItem(itemData) && lengthPerPiece > 0)
		{
			var wholePieces = Math.max(1, Math.round(metersQty / lengthPerPiece));
			return parseFloat((wholePieces * lengthPerPiece).toFixed(5));
		}

		if (isPipeMeterStepItem(itemData))
		{
			return Math.max(1, Math.round(metersQty));
		}

		return metersQty;
	}

	function calcBasketQtyDisplay(itemData, metersQty)
	{
		var meters = parseFloat(metersQty) || 0;
		var lengthPerPiece = parseFloat(itemData.BASKET_LENGTH_PER_PIECE) || 0;
		var weightPerMeter = parseFloat(itemData.BASKET_WEIGHT_PER_METER) || 0;
		var width = parseFloat(itemData.BASKET_WIDTH) || 0;
		var pieces = lengthPerPiece > 0 ? meters / lengthPerPiece : null;
		var weight = weightPerMeter > 0 ? meters * weightPerMeter : null;
		var areaValue;
		var areaUnit;

		if (width > 0)
		{
			areaValue = meters * width;
			areaUnit = 'м²';
		}
		else
		{
			areaValue = meters;
			areaUnit = 'м';
		}

		var piecesStr = '';
		if (pieces !== null)
		{
			piecesStr = formatPiecesDisplay(pieces, itemData);
		}

		return {
			pieces: piecesStr,
			area: formatQtyNumber(areaValue, width > 0 ? 3 : 2),
			areaUnit: areaUnit,
			weight: weight !== null ? formatQtyNumber(weight, 3) : ''
		};
	}

	function setNodeValue(node, value)
	{
		if (!node)
		{
			return;
		}

		if (document.activeElement === node)
		{
			return;
		}

		if (node.tagName === 'INPUT')
		{
			node.value = value;
		}
		else
		{
			node.textContent = value;
		}
	}

	function updateBasketQtyDisplay(itemData, metersQty)
	{
		var display = calcBasketQtyDisplay(itemData, metersQty);
		var piecesNode = document.querySelector('[data-entity="basket-item-pieces-' + itemData.ID + '"]');
		var areaNode = document.querySelector('[data-entity="basket-item-area-' + itemData.ID + '"]');
		var weightNode = document.querySelector('[data-entity="basket-item-weight-' + itemData.ID + '"]');
		var unitNode = document.querySelector('[data-entity="basket-item-area-unit-' + itemData.ID + '"]');

		setNodeValue(piecesNode, display.pieces);
		setNodeValue(areaNode, display.area);
		setNodeValue(weightNode, display.weight);
		syncDisplayPiecesAttr(itemData, display.pieces);

		if (unitNode)
		{
			unitNode.textContent = display.areaUnit;
		}
	}

	function isQtyInputEntity(entity, prefix)
	{
		return (new RegExp('^' + prefix + '-\\d+$')).test(entity);
	}

	function syncDisplayPiecesAttr(itemData, piecesValue)
	{
		var row = document.getElementById('basket-item-' + itemData.ID);
		if (row && piecesValue !== '') {
			row.setAttribute('data-display-pieces', piecesValue);
		}
	}

	function applyQtyInput(target, handler)
	{
		if (!target || !target.getAttribute) {
			return;
		}

		var entity = String(target.getAttribute('data-entity') || '');
		handler(target, entity);
	}

	function debounce(fn, delay)
	{
		var timer = null;
		return function() {
			var args = arguments;
			var ctx = this;
			clearTimeout(timer);
			timer = setTimeout(function() {
				fn.apply(ctx, args);
			}, delay);
		};
	}

	function createPerItemQtyScheduler()
	{
		var timers = {};

		function clearItemTimers(itemId, exceptKind)
		{
			['pieces', 'area', 'weight'].forEach(function(kind) {
				if (exceptKind && kind === exceptKind) {
					return;
				}
				var key = itemId + ':' + kind;
				if (timers[key]) {
					clearTimeout(timers[key]);
					delete timers[key];
				}
			});
		}

		function schedule(kind, target, fn, delay)
		{
			var itemId = target && target.getAttribute
				? String(target.getAttribute('data-entity') || '').replace(/^basket-item-(?:pieces|area|weight)-/, '')
				: '';
			if (!itemId) {
				fn(target);
				return;
			}

			clearItemTimers(itemId, kind);
			var key = itemId + ':' + kind;
			timers[key] = setTimeout(function() {
				delete timers[key];
				fn(target);
			}, delay);
		}

		return {
			clearItemTimers: clearItemTimers,
			schedule: schedule
		};
	}

	BX.ready(function() {
		if (!BX.Sale || !BX.Sale.BasketComponent)
		{
			return;
		}

		var component = BX.Sale.BasketComponent;
		var qtyScheduler = createPerItemQtyScheduler();

		function getItemDataByTargetSafe(target)
		{
			if (!target || !component.getItemDataByTarget)
			{
				return null;
			}

			return enrichItemDataFromRow(component.getItemDataByTarget(target), target);
		}

		function getCurrentQuantity(itemData)
		{
			var quantityField = BX(component.ids.quantity + itemData.ID);
			if (!quantityField)
			{
				return 0;
			}

			var dataValue = quantityField.getAttribute('data-value');
			var raw = dataValue !== null ? dataValue : quantityField.value;
			var num = parseFloat(raw);
			return isNaN(num) ? 0 : num;
		}

		function correctHalfPiecesItem(itemData)
		{
			itemData = enrichItemDataById(itemData);
			if (!itemData || itemData.SHOW_RESTORE)
			{
				return;
			}

			if (!itemData.HALF_PIECES && !isBasicSheetWidthMeterItem(itemData) && !isWholeSheetItem(itemData) && !isPipeMeterStepItem(itemData) && !isFreeMeterCuttingItem(itemData))
			{
				return;
			}

			var quantityField = BX(component.ids.quantity + itemData.ID);
			if (!quantityField)
			{
				return;
			}

			var current = getCurrentQuantity(itemData);
			var snapped = snapMetersForItem(current, itemData);
			if (Math.abs(current - snapped) < 0.0001)
			{
				updateBasketQtyDisplay(itemData, current);
				return;
			}

			setQuantityByMeters(itemData, snapped);
		}

		function correctAllHalfPiecesItems()
		{
			if (!component.items)
			{
				return;
			}

			Object.keys(component.items).forEach(function(id) {
				correctHalfPiecesItem(component.items[id]);
			});
		}

		function usesCustomLengthQuantity(itemData)
		{
			return (parseFloat(itemData && itemData.BASKET_LENGTH_PER_PIECE) || 0) > 0;
		}

		function finalizeQuantity(itemData, quantity)
		{
			// листы/прутки: шаг по длине штуки (1,5 м и т.д.), не целым метрам Bitrix
			return snapMetersForItem(quantity, itemData);
		}

		var originalGetCorrectQuantity = component.getCorrectQuantity
			? component.getCorrectQuantity.bind(component)
			: null;

		component.getCorrectQuantity = function(itemData, quantity) {
			itemData = enrichItemDataById(itemData) || itemData;
			if (usesCustomLengthQuantity(itemData)) {
				return finalizeQuantity(itemData, quantity);
			}

			return originalGetCorrectQuantity
				? originalGetCorrectQuantity(itemData, quantity)
				: parseFloat(quantity) || 0;
		};

		function setQuantityByMeters(itemData, metersQty)
		{
			if (!itemData)
			{
				return;
			}

			itemData = enrichItemDataById(itemData);
			var quantity = finalizeQuantity(itemData, metersQty);

			// пересчёт типа цены (+20%) на сервере
			if (component.actionPool) {
				component.actionPool.needFullRecalculation = true;
			}

			component.setQuantity(itemData, quantity);
		}

		function adjustByPieces(target, deltaPieces)
		{
			var itemData = getItemDataByTargetSafe(target);
			if (!itemData)
			{
				return;
			}

			var lengthPerPiece = parseFloat(itemData.BASKET_LENGTH_PER_PIECE) || 0;
			if (lengthPerPiece <= 0)
			{
				var isQuantityFloat = component.isQuantityFloat ? component.isQuantityFloat(itemData) : true;
				var measureRatio = isQuantityFloat ? parseFloat(itemData.MEASURE_RATIO) : parseInt(itemData.MEASURE_RATIO);
				var current = getCurrentQuantity(itemData);
				var next = parseFloat((current + (measureRatio * deltaPieces)).toFixed(5));
				setQuantityByMeters(itemData, next);
				return;
			}

			var currentQty = getCurrentQuantity(itemData);
			var step = getPiecesStep(itemData);

			var nextQty = parseFloat((currentQty + (lengthPerPiece * deltaPieces * step)).toFixed(5));
			setQuantityByMeters(itemData, nextQty);
		}

		function adjustByArea(target, delta)
		{
			var itemData = getItemDataByTargetSafe(target);
			if (!itemData)
			{
				return;
			}

			var lengthPerPiece = parseFloat(itemData.BASKET_LENGTH_PER_PIECE) || 0;
			var currentQty = getCurrentQuantity(itemData);
			var nextQty;

			if (isBasicSheetWidthMeterItem(itemData)) {
				var basicSteps = getBasicSheetWidthSteps(itemData);
				var width = parseFloat(itemData.BASKET_WIDTH) || 0;
				var currentArea = currentQty * width;
				var nextArea = Math.max(basicSteps.minArea, currentArea + (delta * basicSteps.areaStep));
				nextQty = nextArea / width;
				setQuantityByMeters(itemData, nextQty);
				return;
			}

			// трубы/прутки: +/− по 1 метру
			if (isPipeMeterStepItem(itemData))
			{
				nextQty = Math.max(1, Math.round(currentQty + delta));
				setQuantityByMeters(itemData, nextQty);
				return;
			}

			var step = getPiecesStep(itemData);

			if (lengthPerPiece > 0) {
				var pieces = currentQty / lengthPerPiece + (delta * step);
				pieces = snapPiecesValue(pieces, itemData);
				nextQty = pieces * lengthPerPiece;
			} else {
				var isQuantityFloat = component.isQuantityFloat ? component.isQuantityFloat(itemData) : true;
				var measureRatio = isQuantityFloat ? parseFloat(itemData.MEASURE_RATIO) : parseInt(itemData.MEASURE_RATIO);
				nextQty = parseFloat((currentQty + (measureRatio * delta * step)).toFixed(5));
			}

			setQuantityByMeters(itemData, nextQty);
		}

		function adjustByWeight(target, deltaKg)
		{
			var itemData = getItemDataByTargetSafe(target);
			if (!itemData)
			{
				return;
			}

			var weightPerMeter = parseFloat(itemData.BASKET_WEIGHT_PER_METER) || 0;
			if (weightPerMeter <= 0)
			{
				return;
			}

			var deltaMeters = deltaKg / weightPerMeter;
			var currentQty = getCurrentQuantity(itemData);
			var nextQty = parseFloat((currentQty + deltaMeters).toFixed(5));
			setQuantityByMeters(itemData, nextQty);
		}

		function applyPiecesInput(target, options)
		{
			options = options || {};
			var itemData = getItemDataByTargetSafe(target);
			if (!itemData)
			{
				return;
			}

			qtyScheduler.clearItemTimers(itemData.ID);

			var raw = String(target.value || '').trim();
			if (raw === '')
			{
				if (!options.force && document.activeElement === target)
				{
					return;
				}

				updateBasketQtyDisplay(itemData, getCurrentQuantity(itemData));
				return;
			}

			var pieces = parseInputNumber(raw);
			if (pieces === null || pieces < 0)
			{
				if (!options.force && document.activeElement === target)
				{
					return;
				}

				updateBasketQtyDisplay(itemData, getCurrentQuantity(itemData));
				return;
			}

			pieces = snapPiecesValue(pieces, itemData);

			var lengthPerPiece = parseFloat(itemData.BASKET_LENGTH_PER_PIECE) || 0;
			if (lengthPerPiece <= 0)
			{
				setQuantityByMeters(itemData, pieces);
				if (options.force) {
					target.value = formatPiecesDisplay(pieces, itemData);
				}
				return;
			}

			setQuantityByMeters(itemData, pieces * lengthPerPiece);
			if (options.force) {
				target.value = formatPiecesDisplay(pieces, itemData);
			}
		}

		function applyAreaInput(target, options)
		{
			options = options || {};
			var itemData = getItemDataByTargetSafe(target);
			if (!itemData)
			{
				return;
			}

			qtyScheduler.clearItemTimers(itemData.ID);

			var raw = String(target.value || '').trim();
			if (raw === '')
			{
				if (!options.force && document.activeElement === target)
				{
					return;
				}

				updateBasketQtyDisplay(itemData, getCurrentQuantity(itemData));
				return;
			}

			var area = parseInputNumber(raw);
			if (area === null || area < 0)
			{
				if (!options.force && document.activeElement === target)
				{
					return;
				}

				updateBasketQtyDisplay(itemData, getCurrentQuantity(itemData));
				return;
			}

			var width = parseFloat(itemData.BASKET_WIDTH) || 0;
			var meters = width > 0 ? (area / width) : area;
			meters = snapMetersForItem(meters, itemData);
			setQuantityByMeters(itemData, meters);
			if (options.force) {
				var display = calcBasketQtyDisplay(itemData, meters);
				target.value = display.area;
			}
		}

		function adjustByWeight(target, deltaKg)
		{
			var itemData = getItemDataByTargetSafe(target);
			if (!itemData)
			{
				return;
			}

			var weightPerMeter = parseFloat(itemData.BASKET_WEIGHT_PER_METER) || 0;
			if (weightPerMeter <= 0)
			{
				return;
			}

			var deltaMeters = deltaKg / weightPerMeter;
			var currentQty = getCurrentQuantity(itemData);
			var nextQty = parseFloat((currentQty + deltaMeters).toFixed(5));
			setQuantityByMeters(itemData, nextQty);
		}

		function applyWeightInput(target)
		{
			var itemData = getItemDataByTargetSafe(target);
			if (!itemData)
			{
				return;
			}

			qtyScheduler.clearItemTimers(itemData.ID);

			var weight = parseInputNumber(target.value);
			if (weight === null || weight < 0)
			{
				updateBasketQtyDisplay(itemData, getCurrentQuantity(itemData));
				return;
			}

			var weightPerMeter = parseFloat(itemData.BASKET_WEIGHT_PER_METER) || 0;
			if (weightPerMeter <= 0)
			{
				return;
			}

			setQuantityByMeters(itemData, weight / weightPerMeter);
		}

		var debouncedApplyPiecesInput = function(target) {
			qtyScheduler.schedule('pieces', target, function(node) {
				applyPiecesInput(node);
			}, 250);
		};
		var debouncedApplyAreaInput = function(target) {
			qtyScheduler.schedule('area', target, function(node) {
				applyAreaInput(node);
			}, 250);
		};
		var debouncedApplyWeightInput = debounce(function(target) {
			applyWeightInput(target);
		}, 250);

		var originalApplyBasketResult = component.applyBasketResult.bind(component);
		component.applyBasketResult = function(result) {
			originalApplyBasketResult(result);
			BX.defer(correctAllHalfPiecesItems);
		};

		component.quantityPlus = function(target) {
			if (!BX.type.isDomNode(target)) {
				target = BX.proxy_context;
				if (component.clearQuantityInterval) {
					component.clearQuantityInterval();
				}
			}
			adjustByArea(target, 1);
		};

		component.quantityMinus = function(target) {
			target = BX.type.isDomNode(target) ? target : BX.proxy_context;
			adjustByArea(target, -1);
		};

		component.setQuantity = function(itemData, quantity) {
			var quantityField = BX(component.ids.quantity + itemData.ID);
			if (!quantityField)
			{
				return;
			}

			itemData = enrichItemDataFromRow(itemData, quantityField) || enrichItemDataById(itemData);
			quantity = finalizeQuantity(itemData, quantity);

			var currentQuantity = parseFloat(quantityField.getAttribute('data-value'));
			if (isNaN(currentQuantity))
			{
				currentQuantity = parseFloat(itemData.QUANTITY) || 0;
			}

			quantityField.value = quantity;
			quantityField.setAttribute('data-value', quantity);

			if (Math.abs(currentQuantity - quantity) >= 0.0001)
			{
				if (typeof component.animatePriceByQuantity === 'function')
				{
					component.animatePriceByQuantity(itemData, quantity);
				}
				if (component.actionPool && typeof component.actionPool.changeQuantity === 'function')
				{
					component.actionPool.changeQuantity(itemData.ID, quantity, currentQuantity);
				}
			}

			var item = this.items[itemData.ID] || itemData;
			item.QUANTITY = quantity;
			updateBasketQtyDisplay(item, quantity);
		};

		BX.bind(document, 'click', function(e) {
			var target = e.target;
			if (!target || !target.getAttribute)
			{
				return;
			}

			var entity = target.getAttribute('data-entity');
			if (!entity)
			{
				return;
			}

			if (entity === 'basket-item-pieces-minus')
			{
				e.preventDefault();
				adjustByPieces(target, -1);
			}
			else if (entity === 'basket-item-pieces-plus')
			{
				e.preventDefault();
				adjustByPieces(target, +1);
			}
			else if (entity === 'basket-item-weight-minus')
			{
				e.preventDefault();
				adjustByWeight(target, -1);
			}
			else if (entity === 'basket-item-weight-plus')
			{
				e.preventDefault();
				adjustByWeight(target, +1);
			}
		});

		BX.bind(document, 'change', function(e) {
			applyQtyInput(e.target, function(target, entity) {
				if (isQtyInputEntity(entity, 'basket-item-pieces')) {
					applyPiecesInput(target, { force: true });
				}
				else if (isQtyInputEntity(entity, 'basket-item-area')) {
					applyAreaInput(target, { force: true });
				}
				else if (isQtyInputEntity(entity, 'basket-item-weight')) {
					applyWeightInput(target);
				}
			});
		});

		BX.bind(document, 'blur', function(e) {
			applyQtyInput(e.target, function(target, entity) {
				if (isQtyInputEntity(entity, 'basket-item-pieces')) {
					applyPiecesInput(target, { force: true });
				}
				else if (isQtyInputEntity(entity, 'basket-item-area')) {
					applyAreaInput(target, { force: true });
				}
			});
		}, true);

		BX.bind(document, 'input', function(e) {
			applyQtyInput(e.target, function(target, entity) {
				if (isQtyInputEntity(entity, 'basket-item-pieces')) {
					debouncedApplyPiecesInput(target);
				}
				else if (isQtyInputEntity(entity, 'basket-item-area')) {
					debouncedApplyAreaInput(target);
				}
				else if (isQtyInputEntity(entity, 'basket-item-weight')) {
					debouncedApplyWeightInput(target);
				}
			});
		});

		BX.bind(document, 'keydown', function(e) {
			var target = e.target;
			if (!target || !target.getAttribute || e.key !== 'Enter')
			{
				return;
			}

			var entity = String(target.getAttribute('data-entity') || '');
			if (
				isQtyInputEntity(entity, 'basket-item-pieces')
				|| isQtyInputEntity(entity, 'basket-item-area')
				|| isQtyInputEntity(entity, 'basket-item-weight')
			)
			{
				e.preventDefault();
				target.blur();
			}
		});
		BX.defer(correctAllHalfPiecesItems);
	});
})();
