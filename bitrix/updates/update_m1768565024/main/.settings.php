<?php
return [
	'rest' => [
		'value' => [
			'defaultNamespace' => '\\Bitrix\\Main\\Rest\\V3\\Controller',
		]
	],
	'controllers' => [
		'value' => [
			'defaultNamespace' => '\\Bitrix\\Main\\Controller',
			'namespaces' => [
				'\\Bitrix\\Main\\Controller' => 'api',
			],
			'restIntegration' => [
				'enabled' => true,
				'hideModuleScope' => true,
				'scopes' => [
					'userfieldconfig',
				],
			],
		],
		'readonly' => true,
	],
	'console' => [
		'value' => [
			'commands' => [
				// orm
				\Bitrix\Main\Cli\Command\Orm\AnnotateCommand::class,

				// make
				\Bitrix\Main\Cli\Command\Make\ComponentCommand::class,
				\Bitrix\Main\Cli\Command\Make\ControllerCommand::class,
				\Bitrix\Main\Cli\Command\Make\TabletCommand::class,
				\Bitrix\Main\Cli\Command\Make\EntityCommand::class,
				\Bitrix\Main\Cli\Command\Make\ModuleCommand::class,
				\Bitrix\Main\Cli\Command\Make\RequestCommand::class,
				\Bitrix\Main\Cli\Command\Make\ServiceCommand::class,
				\Bitrix\Main\Cli\Command\Make\EventCommand::class,
				\Bitrix\Main\Cli\Command\Make\EventHandlerCommand::class,
				\Bitrix\Main\Cli\Command\Make\MessageCommand::class,
				\Bitrix\Main\Cli\Command\Make\MessageHandlerCommand::class,
				\Bitrix\Main\Cli\Command\Make\AgentCommand::class,

				// dev
				\Bitrix\Main\Cli\Command\Dev\LocatorCodesCommand::class,
				\Bitrix\Main\Cli\Command\Dev\ModuleSkeletonCommand::class,

				// update
				\Bitrix\Main\Cli\Command\Update\ModulesCommand::class,
				\Bitrix\Main\Cli\Command\Update\LanguagesCommand::class,
				\Bitrix\Main\Cli\Command\Update\VersionsCommand::class,

				// other
				\Bitrix\Main\Cli\Command\Messenger\ConsumeMessagesCommand::class,
			],
		],
		'readonly' => true,
	],
	'services' => [
		'value' => [
			'main.validation.service' => [
				'className' => \Bitrix\Main\Validation\ValidationService::class,
			],
		],
	],
];
