<?php

declare(strict_types=1);

use JBSNewMedia\MarkdownSuite\Configuration;
use JBSNewMedia\MarkdownSuite\MarkdownSuite;

if (file_exists('./debuglib.inc.php')) {
	include './debuglib.inc.php';
}

$configureFile = './configure.inc.php';
$configure = [];
if (!file_exists($configureFile)) {
    die('Please configure the application by copying the "configure.example.inc.php" file to "configure.inc.php" and adjust the settings.');
}
include $configureFile;

$autoloadFile = './vendor/autoload.php';
if (!file_exists($autoloadFile)) {
    die('Please run "composer install" in the root directory before running this script.');
}
include $autoloadFile;

$path = (isset($_GET['path'])?$_GET['path']:'');

$configure = new Configuration($configure);

$markdownSuite = new MarkdownSuite();
$markdownSuite->scanDirectory($configure->getProjectDir());
if ($markdownSuite->isAllowedFile($path, $configure->getProjectAllowedFileExtensions())) {
	$markdownSuite->sendFile($path);
}
$markdownSuite->setPath($path);
#$markdownSuite->dd();

?>
<!doctype html>
<html lang="<?php echo $configure->getProjectLang()?>">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<base href="<?php echo $configure->getProjectUrl()?>">
	<title><?php echo $configure->getProjectTitle()?></title>
	<meta name="description" content="<?php echo $configure->getProjectDescription()?>">
	<link rel="canonical" href="<?php echo $configure->getProjectUrl()?>">
	<link rel="icon" href="favicon.ico" type="image/x-icon">
	<script src="vendor/twbs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
	<script src="vendor/jbsnewmedia/markdownsuite/public/assets/js/markdownsuite.js?v=<?php echo MarkdownSuite::getVersion()?>"></script>
	<script src="vendor/avalynx/avalynx-lightbox/dist/js/avalynx-lightbox.js"></script>
	<link href="vendor/twbs/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
	<link href="vendor/jbsnewmedia/markdownsuite/public/assets/css/markdownsuite.css?v=<?php echo MarkdownSuite::getVersion()?>" rel="stylesheet">
</head>
<body class="bg-white">

<nav class="navbar navbar-expand-lg fixed-top bg-white border-bottom shadow-sm">
	<div class="container">
		<a class="navbar-brand d-flex align-items-center text-dark text-decoration-none" href="<?php echo $configure->getProjectUrl()?>">
			<img src="<?php echo $configure->getProjectIcon()?>" alt="<?php echo $configure->getProjectIconTitle()?>" width="30" height="24" class="me-2" title="<?php echo $configure->getProjectIconTitle()?>">
			<span class="fs-4"><?php echo $configure->getProjectSubtitle()?></span>
		</a>
		<button class="navbar-toggler rounded-0" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasNavbar" aria-controls="offcanvasNavbar" aria-expanded="false" aria-label="Toggle navigation">
			<span class="navbar-toggler-icon"></span>
		</button>
	</div>
</nav>

<div class="offcanvas offcanvas-start" tabindex="-1" id="offcanvasNavbar" aria-labelledby="offcanvasNavbarLabel">
	<div class="offcanvas-header border-bottom shadow-sm" style="height:60px;">
		<a class="navbar-brand d-flex align-items-center text-dark text-decoration-none">
			<img src="<?php echo $configure->getProjectIcon()?>" alt="<?php echo $configure->getProjectIconTitle()?>" width="30" height="24" class="me-2" title="<?php echo $configure->getProjectIconTitle()?>">
			<span class="fs-4"><?php echo $configure->getProjectSubtitle()?></span>
		</a>
		<button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
	</div>
	<div class="offcanvas-body">
		<ul class="accordion list-unstyled fs-6" id="navigation_start_offcanvas">

			<?php foreach ($markdownSuite->getSuiteData() as $key1 => $details1): ?>

				<?php if($details1['content_sub'] !== []): ?>

					<li class="mb-1 w-100">
						<a class="btn btn-toggle align-items-center rounded-0<?php if ($details1['active'] !== true):?> collapsed<?php endif;?> w-100" data-bs-toggle="collapse" data-bs-target="#<?php echo $key1?>-collapse-offcanvas" aria-expanded="<?php if ($details1['active'] === true):?>true<?php else:?>false<?php endif?>" aria-controls="<?php echo $key1?>-collapse-offcanvas"><?php echo $details1['header']?></a>
						<div class="accordion-collapse collapse<?php if ($details1['active'] === true):?> show<?php endif;?>" id="<?php echo $key1?>-collapse-offcanvas" data-bs-parent="#navigation_start_offcanvas">
							<ul class="btn-toggle-nav list-unstyled fw-normal pb-1 small">

								<?php foreach ($details1['content_sub'] as $key2 => $details2): ?>

									<li class="collapse-item">
										<a href="<?php echo $key1 . '/'. $key2 ?>" class="collapse-item<?php if($details2['active'] === true):?> active<?php endif;?> link-dark w-100"><?php echo $details2['header']?></a>
									</li>

								<?php endforeach; ?>

							</ul>
						</div>
					</li>

				<?php endif; ?>

			<?php endforeach; ?>

		</ul>
	</div>
</div>

<main class="container">
	<div class="row">
		<div class="col-12 col-lg-3 col-xl-2 d-none d-lg-block">
			<strong class="text-muted fs-6 d-block h6 w-100 my-2 px-2 pb-2 border-bottom nav-header"><?php echo $configure->getProjectMainTitleStart()?></strong>
			<ul class="accordion ps-0 list-unstyled fs-6" id="navigation_start">

				<?php foreach ($markdownSuite->getSuiteData() as $key1 => $details1): ?>

					<?php if($details1['content_sub'] !== []): ?>

						<li class="mb-1 w-100">
							<a class="btn btn-toggle align-items-center rounded-0<?php if ($details1['active'] !== true):?> collapsed<?php endif;?> w-100" data-bs-toggle="collapse" data-bs-target="#<?php echo $key1?>-collapse" aria-expanded="<?php if ($details1['active'] === true):?>true<?php else:?>false<?php endif?>" aria-controls="<?php echo $key1?>-collapse"><?php echo $details1['header']?></a>
							<div class="accordion-collapse collapse<?php if ($details1['active'] === true):?> show<?php endif;?>" id="<?php echo $key1?>-collapse" data-bs-parent="#navigation_start">
								<ul class="btn-toggle-nav list-unstyled fw-normal pb-1 small">

									<?php foreach ($details1['content_sub'] as $key2 => $details2): ?>

										<li class="collapse-item">
											<a href="<?php echo $key1 . '/'. $key2 ?>" class="collapse-item<?php if($details2['active'] === true):?> active<?php endif;?> link-dark w-100"><?php echo $details2['header']?></a>
										</li>

									<?php endforeach; ?>

								</ul>
							</div>
						</li>

					<?php endif; ?>

				<?php endforeach; ?>

			</ul>
		</div>

		<div class="col-12 col-lg-6 col-xl-8">
			<div data-bs-spy="scroll" data-bs-target="#navigation_end" data-bs-smooth-scroll="true" tabindex="0">

				<div class="pb-3">
					<?php $part=$markdownSuite->getSuiteDataPart(); ?>
					<?php echo $part['content_parsed']?>
				</div>

				<?php foreach ($markdownSuite->getContentData() as $key => $details): ?>

					<div id="main-<?php echo $details['anchor']?>" class="pb-3">
						<?php echo $details['content_parsed']?>
					</div>

					<?php if ($details['content_sub'] !== []): ?>

						<?php foreach ($details['content_sub'] as $key2 => $details2): ?>

							<div id="main-<?php echo $details['anchor']?>-<?php echo $details2['anchor']?>" class="pb-3">
								<?php echo $details2['content_parsed']?>
							</div>

						<?php endforeach; ?>

					<?php endif; ?>

				<?php endforeach; ?>

				<div id="main-footer-end" class="py-5"></div>

			</div>
		</div>

		<div class="col-12 col-lg-3 col-xl-2">
			<strong class="text-muted fs-6 d-block h6 my-2 px-2 pb-2 border-bottom nav-header"><?php echo $configure->getProjectMainTitleEnd()?></strong>
			<nav id="navigation_end" class="fs-7 px-2 ">
				<nav class="nav flex-column">

					<?php foreach ($markdownSuite->getContentData() as $key => $details): ?>

						<a class="nav-link link-secondary mb-1" href="<?php echo $part['key']?>#main-<?php echo $details['anchor']?>"><?php echo $details['header']?></a>

						<?php if ($details['content_sub'] !== []): ?>

							<nav class="nav nav-pi2lls flex-column">

								<?php foreach ($details['content_sub'] as $key2 => $details2): ?>

									<a class="nav-link link-secondary ms-3 mb-1" href="<?php echo $part['key']?>#main-<?php echo $details['anchor']?>-<?php echo $details2['anchor']?>"><?php echo $details2['header']?></a>

								<?php endforeach; ?>

							</nav>

						<?php endif; ?>

					<?php endforeach; ?>

				</nav>
			</nav>
		</div>
	</div>
</main>

<footer class="text-muted border-top bg-white shadow-sm fixed-bottom">
	<div class="container my-2 fs-7">
		<div class="row">
			<div class="col-12 col-md-6">
				<?php echo $configure->getProjectFooterStart()?>
			</div>
			<div class="col-12 col-md-6 pt-2 pt-md-0 text-md-end">
				<?php echo $configure->getProjectFooterEnd()?>
			</div>
		</div>
	</div>
</footer>

<button type="button" class="btn btn-secondary btn-floating btn-lg rounded-0" id="btnbacktotop">
	<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><!--!Font Awesome Free 6.6.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M246.6 41.4c-12.5-12.5-32.8-12.5-45.3 0l-160 160c-12.5 12.5-12.5 32.8 0 45.3s32.8 12.5 45.3 0L224 109.3 361.4 246.6c12.5 12.5 32.8 12.5 45.3 0s12.5-32.8 0-45.3l-160-160zm160 352l-160-160c-12.5-12.5-32.8-12.5-45.3 0l-160 160c-12.5 12.5-12.5 32.8 0 45.3s32.8 12.5 45.3 0L224 301.3 361.4 438.6c12.5 12.5 32.8 12.5 45.3 0s12.5-32.8 0-45.3z"/></svg>
</button>

<script>
	new AvalynxLightbox('.responsive-img');
</script>

</body>
</html>

