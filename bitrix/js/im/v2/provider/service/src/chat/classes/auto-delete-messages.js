import { Runtime } from 'main.core';

import { Core } from 'im.v2.application.core';
import { Logger } from 'im.v2.lib.logger';
import { RestMethod } from 'im.v2.const';
import { runAction } from 'im.v2.lib.rest';

import type { Store } from 'ui.vue3.vuex';

export class AutoDeleteMessagesService
{
	#store: Store;

	#sendDurationRequestDebounced: Function;

	constructor()
	{
		this.#store = Core.getStore();

		const DEBOUNCE_TIME = 500;
		this.#sendDurationRequestDebounced = Runtime.debounce(this.#sendDurationRequest, DEBOUNCE_TIME);
	}

	setDuration(dialogId: string, hours: number): void
	{
		Logger.warn('AutoDeleteMessagesService: setDuration', dialogId, hours);

		const previousDuration = this.#store.getters['chats/get'](dialogId).disappearDuration;

		this.#store.dispatch('chats/setDisappearingDuration', {
			dialogId,
			duration: hours,
		});

		this.#sendDurationRequestDebounced({ dialogId, hours, previousDuration });
	}

	#sendDurationRequest(queryParams: { dialogId: string, hours: number, previousDuration: number }): Promise
	{
		const { dialogId, hours, previousDuration } = queryParams;

		return runAction(RestMethod.imV2ChatSetDisappearingDuration, {
			data: { dialogId, hours },
		}).catch((error) => {
			console.error('AutoDeleteMessagesService: Error setting disappearing duration', error);

			this.#store.dispatch('chats/setDisappearingDuration', {
				dialogId,
				duration: previousDuration,
			});
		});
	}
}
