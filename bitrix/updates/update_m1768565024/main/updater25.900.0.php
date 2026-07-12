<?php
/** @var CUpdater $updater */
/** @var CDatabase $DB */

$updater->CopyFiles('install/components', 'components');
$updater->CopyFiles('install/js', 'js');
$updater->CopyFiles('install/gadgets', 'gadgets');

if ($updater->canUpdateKernel())
{
	$filesToDelete = [
		'modules/main/install/js/main/core/core_fx.js',
		'js/main/core/core_fx.js',
		'modules/main/install/js/main/core/core_fx.min.js',
		'js/main/core/core_fx.min.js',
		'modules/main/install/js/main/core/core_fx.map.js',
		'js/main/core/core_fx.map.js',
		'modules/main/lib/cli/command/make/templates/component/langtemplate.php',
		'modules/main/install/js/main/core/core_ls.js',
		'js/main/core/core_ls.js',
		'modules/main/install/js/main/core/core_ls.min.js',
		'js/main/core/core_ls.min.js',
		'modules/main/install/js/main/core/core_ls.map.js',
		'js/main/core/core_ls.map.js',
		'modules/main/lang/de/lib/composite/debug/model/log.php',
		'modules/main/lang/de/lib/composite/internals/model/page.php',
		'modules/main/lang/de/lib/localization/culture.php',
		'modules/main/lang/de/lib/numerator/model/numerator.php',
		'modules/main/lang/de/lib/service/geoip/handler.php',
		'modules/main/lang/de/lib/user.php',
		'modules/main/lang/de/lib/userconsent/internals/agreement.php',
		'modules/main/lang/de/lib/userfield.php',
		'modules/main/lang/de/lib/userphoneauth.php',
		'modules/main/lang/en/lib/composite/debug/model/log.php',
		'modules/main/lang/en/lib/composite/internals/model/page.php',
		'modules/main/lang/en/lib/localization/culture.php',
		'modules/main/lang/en/lib/numerator/model/numerator.php',
		'modules/main/lang/en/lib/service/geoip/handler.php',
		'modules/main/lang/en/lib/user.php',
		'modules/main/lang/en/lib/userconsent/internals/agreement.php',
		'modules/main/lang/en/lib/userfield.php',
		'modules/main/lang/en/lib/userphoneauth.php',
		'modules/main/lang/kz/lib/composite/debug/model/log.php',
		'modules/main/lang/kz/lib/composite/internals/model/page.php',
		'modules/main/lang/kz/lib/localization/culture.php',
		'modules/main/lang/kz/lib/numerator/model/numerator.php',
		'modules/main/lang/kz/lib/service/geoip/handler.php',
		'modules/main/lang/kz/lib/user.php',
		'modules/main/lang/kz/lib/userconsent/internals/agreement.php',
		'modules/main/lang/kz/lib/userfield.php',
		'modules/main/lang/kz/lib/userphoneauth.php',
		'modules/main/lang/ru/lib/composite/debug/model/log.php',
		'modules/main/lang/ru/lib/composite/internals/model/page.php',
		'modules/main/lang/ru/lib/localization/culture.php',
		'modules/main/lang/ru/lib/numerator/model/numerator.php',
		'modules/main/lang/ru/lib/service/geoip/handler.php',
		'modules/main/lang/ru/lib/user.php',
		'modules/main/lang/ru/lib/userconsent/internals/agreement.php',
		'modules/main/lang/ru/lib/userfield.php',
		'modules/main/lang/ru/lib/userphoneauth.php',
	];
	foreach ($filesToDelete as $fileName)
	{
		CUpdateSystem::deleteDirFilesEx($_SERVER['DOCUMENT_ROOT'] . $updater->kernelPath . '/' . $fileName);
	}
}
