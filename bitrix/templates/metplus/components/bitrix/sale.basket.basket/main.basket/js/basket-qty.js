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
			piecesStr = Math.abs(pieces - Math.round(pieces)) < 0.01
				? String(Math.round(pieces))
				: formatQtyNumber(pieces, 2);
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

		if (unitNode)
		{
			unitNode.textContent = display.areaUnit;
		}
	}

	BX.ready(function() {
		if (!BX.Sale || !BX.Sale.BasketComponent)
		{
			return;
		}

		var component = BX.Sale.BasketComponent;
		var originalSetQuantity = component.setQuantity;

		function getItemDataByTargetSafe(target)
		{
			if (!target || !component.getItemDataByTarget)
			{
				return null;
			}
			return component.getItemDataByTarget(target);
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

		function setQuantityByMeters(itemData, metersQty)
		{
			if (!itemData)
			{
				return;
			}
			var quantity = component.getCorrectQuantity ? component.getCorrectQuantity(itemData, metersQty) : metersQty;
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
			var nextQty = parseFloat((currentQty + (lengthPerPiece * deltaPieces)).toFixed(5));
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

		function applyPiecesInput(target)
		{
			var itemData = getItemDataByTargetSafe(target);
			if (!itemData)
			{
				return;
			}

			var pieces = parseInputNumber(target.value);
			if (pieces === null || pieces < 0)
			{
				updateBasketQtyDisplay(itemData, getCurrentQuantity(itemData));
				return;
			}

			var lengthPerPiece = parseFloat(itemData.BASKET_LENGTH_PER_PIECE) || 0;
			if (lengthPerPiece <= 0)
			{
				setQuantityByMeters(itemData, pieces);
				return;
			}

			setQuantityByMeters(itemData, pieces * lengthPerPiece);
		}

		function applyAreaInput(target)
		{
			var itemData = getItemDataByTargetSafe(target);
			if (!itemData)
			{
				return;
			}

			var area = parseInputNumber(target.value);
			if (area === null || area < 0)
			{
				updateBasketQtyDisplay(itemData, getCurrentQuantity(itemData));
				return;
			}

			var width = parseFloat(itemData.BASKET_WIDTH) || 0;
			var meters = width > 0 ? (area / width) : area;
			setQuantityByMeters(itemData, meters);
		}

		function applyWeightInput(target)
		{
			var itemData = getItemDataByTargetSafe(target);
			if (!itemData)
			{
				return;
			}

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

		component.setQuantity = function(itemData, quantity) {
			var quantityField = BX(component.ids.quantity + itemData.ID);

			originalSetQuantity.apply(this, arguments);

			if (quantityField)
			{
				quantityField.setAttribute('data-value', quantity);
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
			var target = e.target;
			if (!target || !target.getAttribute)
			{
				return;
			}

			var entity = String(target.getAttribute('data-entity') || '');
			if (entity.indexOf('basket-item-pieces-') === 0)
			{
				applyPiecesInput(target);
			}
			else if (entity.indexOf('basket-item-area-') === 0)
			{
				applyAreaInput(target);
			}
			else if (entity.indexOf('basket-item-weight-') === 0)
			{
				applyWeightInput(target);
			}
		});

		BX.bind(document, 'keydown', function(e) {
			var target = e.target;
			if (!target || !target.getAttribute || e.key !== 'Enter')
			{
				return;
			}

			var entity = String(target.getAttribute('data-entity') || '');
			if (
				entity.indexOf('basket-item-pieces-') === 0
				|| entity.indexOf('basket-item-area-') === 0
				|| entity.indexOf('basket-item-weight-') === 0
			)
			{
				e.preventDefault();
				target.blur();
			}
		});
	});
})();
