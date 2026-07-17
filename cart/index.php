<?php
require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php';
$APPLICATION->SetTitle('Корзина');
?>
<main class="main-content page-cart">
	<div class="inner-page_title-section">
		<div class="container">
			<?php
			$APPLICATION->IncludeComponent(
				'bitrix:breadcrumb',
				'breadcrumb',
				['SITE_ID' => SITE_ID],
				false
			);
			?>
			<h1>Корзина</h1>
		</div>
	</div>
	<div class="page-cart__wrap">
		<?php
		$cartDisplayMode = 'page';
		include $_SERVER['DOCUMENT_ROOT'] . '/include/sale_basket.php';
		?>
	</div>
</main>
<?php
require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php';
