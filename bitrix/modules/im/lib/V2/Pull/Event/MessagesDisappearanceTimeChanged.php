<?php

namespace Bitrix\Im\V2\Pull\Event;

use Bitrix\Im\V2\Common\ContextCustomer;
use Bitrix\Im\V2\Pull\EventType;

class MessagesDisappearanceTimeChanged extends BaseChatEvent
{
	use ContextCustomer;
	use DialogIdFiller;

	protected function getBasePullParamsInternal(): array
	{
		return [
			'chatId' => $this->chat->getId(),
			'dialogId' => $this->chat->getDialogId(),
			'messagesDisappearanceTime' => $this->chat->getDisappearingTime() ?? 0,
		];
	}

	protected function getType(): EventType
	{
		return EventType::MessagesDisappearanceTimeChanged;
	}
}