<?php
// source: /Users/BugsBunny/WWW/_bugs-nette-skel/app/templates/@layout.latte

// prolog Latte\Macros\CoreMacros
list($_b, $_g, $_l) = $template->initialize('0077087881', 'html')
;
// prolog Latte\Macros\BlockMacros
//
// block head
//
if (!function_exists($_b->blocks['head'][] = '_lbf35753c32a_head')) { function _lbf35753c32a_head($_b, $_args) { foreach ($_args as $__k => $__v) $$__k = $__v
;
}}

//
// block scripts
//
if (!function_exists($_b->blocks['scripts'][] = '_lbc3a643a4b1_scripts')) { function _lbc3a643a4b1_scripts($_b, $_args) { foreach ($_args as $__k => $__v) $$__k = $__v
?>	    <script type="text/javascript" src="<?php echo Latte\Runtime\Filters::escapeHtml(Latte\Runtime\Filters::safeUrl($basePath), ENT_COMPAT) ?>/js/vendor/jquery.js"></script>
	    <script type="text/javascript" src="<?php echo Latte\Runtime\Filters::escapeHtml(Latte\Runtime\Filters::safeUrl($basePath), ENT_COMPAT) ?>/js/vendor/jquery.easings.min.js"></script>
	    <script type="text/javascript" src="<?php echo Latte\Runtime\Filters::escapeHtml(Latte\Runtime\Filters::safeUrl($basePath), ENT_COMPAT) ?>/js/vendor/jquery.slimscroll.min.js"></script>
	    <script type="text/javascript" src="<?php echo Latte\Runtime\Filters::escapeHtml(Latte\Runtime\Filters::safeUrl($basePath), ENT_COMPAT) ?>/js/vendor/jquery.fullPage.min.js"></script>
	    <script type="text/javascript" src="<?php echo Latte\Runtime\Filters::escapeHtml(Latte\Runtime\Filters::safeUrl($basePath), ENT_COMPAT) ?>/js/vendor/jquery.slimscroll.min.js"></script>
	    <script type="text/javascript" src="<?php echo Latte\Runtime\Filters::escapeHtml(Latte\Runtime\Filters::safeUrl($basePath), ENT_COMPAT) ?>/js/foundation.min.js"></script>

		<script type="text/javascript" src="<?php echo Latte\Runtime\Filters::escapeHtml(Latte\Runtime\Filters::safeUrl($basePath), ENT_COMPAT) ?>/js/netteForms.js"></script>
		<script type="text/javascript" src="<?php echo Latte\Runtime\Filters::escapeHtml(Latte\Runtime\Filters::safeUrl($basePath), ENT_COMPAT) ?>/js/main.js"></script>
		<script type="text/javascript">
			$(document).foundation();

			// if (!Modernizr.touch)
			// if (!Modernizr.touch && false)
			// {
			//   var head = document.getElementsByTagName('head')[0],
			//       link = document.createElement('link');
			//   link.rel = 'stylesheet';
			//   link.type = 'text/css';
			//   link.href = 'css/jquery.fullPage.css';
			//   head.appendChild(link);

			//   $('#fullpage').fullpage({
			//     scrollingSpeed: 700,
			//     easing: 'easeInQuart',
			//     verticalCentered: true,
			//     resize: false,
			//     anchors: [ ]
			//   });
			// }

			// $('#fullpage').slimScroll({
			//   height: '1000px !important'
			// });
		</script>
<?php
}}

//
// end of blocks
//

// template extending

$_l->extends = empty($_g->extended) && isset($_control) && $_control instanceof Nette\Application\UI\Presenter ? $_control->findLayoutTemplateFile() : NULL; $_g->extended = TRUE;

if ($_l->extends) { ob_start();}

// prolog Nette\Bridges\ApplicationLatte\UIMacros

// snippets support
if (empty($_l->extends) && !empty($_control->snippetMode)) {
	return Nette\Bridges\ApplicationLatte\UIMacros::renderSnippets($_control, $_b, get_defined_vars());
}

//
// main template
//
?>
<!doctype html>
<html class="no-js" lang="en">
	<head>
		<meta charset="utf-8">
	    <meta http-equiv="X-UA-Compatible" content="IE=edge">
	    <meta name="viewport" content="width=device-width, initial-scale=1.0">
	    <meta name="keywords" content="">
	    <meta name="description" content="">

		<title></title>

		<link rel="shortcut icon" href="favicon.ico">

	    <link rel="stylesheet" type="text/css" href="<?php echo Latte\Runtime\Filters::escapeHtml(Latte\Runtime\Filters::safeUrl($basePath), ENT_COMPAT) ?>/css/foundation.css">
	    <link rel="stylesheet" type="text/css" href="<?php echo Latte\Runtime\Filters::escapeHtml(Latte\Runtime\Filters::safeUrl($basePath), ENT_COMPAT) ?>/css/screen.css">
	    <link rel="stylesheet" type="text/css" href="<?php echo Latte\Runtime\Filters::escapeHtml(Latte\Runtime\Filters::safeUrl($basePath), ENT_COMPAT) ?>/css/tablet.css">
	    <link rel="stylesheet" type="text/css" href="<?php echo Latte\Runtime\Filters::escapeHtml(Latte\Runtime\Filters::safeUrl($basePath), ENT_COMPAT) ?>/css/desktop.css">

	    <!--[if IE]>
	    <link rel="stylesheet" type="text/css" href="<?php echo Latte\Runtime\Filters::escapeHtmlComment($basePath) ?>/css/ie.css" />
	    <![endif]-->
	    <!--[if (lte IE 8)]>
	    <link rel="stylesheet" type="text/css" href="<?php echo Latte\Runtime\Filters::escapeHtmlComment($basePath) ?>/css/ie8-.css">
	    <![endif]-->

    	    			<?php if ($_l->extends) { ob_end_clean(); return $template->renderChildTemplate($_l->extends, get_defined_vars()); }
call_user_func(reset($_b->blocks['head']), $_b, get_defined_vars())  ?>

	</head>

	<body>
		<script> document.documentElement.className+=' js' </script>

<?php $iterations = 0; foreach ($flashes as $flash) { ?>		<div class="flash <?php echo Latte\Runtime\Filters::escapeHtml($flash->type, ENT_COMPAT) ?>
"><?php echo Latte\Runtime\Filters::escapeHtml($flash->message, ENT_NOQUOTES) ?></div>
<?php $iterations++; } ?>

<?php Latte\Macros\BlockMacros::callBlock($_b, 'content', $template->getParameters()) ?>

<?php call_user_func(reset($_b->blocks['scripts']), $_b, get_defined_vars())  ?>
	</body>
</html>
