<?php

namespace Bitrix\Im\V2\Chat\Fields;

class ChatFields
{
	private static array $instance = [];

	public function __construct(protected int $chatId)
	{}

	public static function getInstance(int $chatId): self
	{
		if (self::$instance[$chatId] === null)
		{
			self::$instance[$chatId] = new self($chatId);
		}

		return self::$instance[$chatId];
	}

	public function getField(Field $field): BaseField
	{
		return FieldFactory::getInstance()->getField($field, $this->chatId);
	}

	public function getOptionalParamsToRest(array $option = []): array
	{
		return $this->getParamsInternal(Field::getOptionalParams(), $option);
	}

	protected function getParamsInternal(array $fields, $option): array
	{
		$result = [];
		foreach ($fields as $field)
		{
			$result[] = $this->getField($field)->toRestFormat($option);
		}

		return array_merge(...$result);
	}
}
