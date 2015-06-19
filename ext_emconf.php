<?php

/***************************************************************
 * Extension Manager/Repository config file for ext "ezqueries".
 ***************************************************************/

$EM_CONF[$_EXTKEY] = array(
	'title' => 'EasyQueries',
	'description' => 'ezqueries.florianrohland.de',
	'category' => 'plugin',
	'author' => 'Florian Rohland',
	'author_email' => 'info@florianrohland.de',
	'shy' => '',
	'dependencies' => 'cms,extbase,fluid,adodb',
	'conflicts' => '',
	'priority' => '',
	'module' => '',
	'state' => 'beta',
	'internal' => '',
	'uploadfolder' => 0,
	'createDirs' => '',
	'modify_tables' => '',
	'clearCacheOnLoad' => 0,
	'lockType' => '',
	'version' => '1.0.0',
	'constraints' => array(
		'depends' => array(
			'cms' => '',
			'extbase' => '',
			'fluid' => '',
			'adodb' => '',
			'typo3' => '6.2.0',
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
	'suggests' => array(
	),
	'_md5_values_when_last_written' => 'a:372:{s:12:"ext_icon.gif";s:4:"ce82";s:17:"ext_localconf.php";s:4:"9034";s:14:"ext_tables.php";s:4:"3d7e";s:14:"ext_tables.sql";s:4:"57ec";s:49:"Classes/Controller/RecordManagementController.php";s:4:"6d6a";s:35:"Classes/Domain/Model/Conditions.php";s:4:"7e4f";s:31:"Classes/Domain/Model/Record.php";s:4:"319f";s:41:"Classes/Domain/Model/RecordManagement.php";s:4:"2b14";s:30:"Classes/Domain/Model/Table.php";s:4:"bbc2";s:56:"Classes/Domain/Repository/RecordManagementRepository.php";s:4:"0c8e";s:37:"Classes/Utility/ConversionUtility.php";s:4:"ce33";s:33:"Classes/Utility/FilterUtility.php";s:4:"9424";s:35:"Classes/Utility/FlexFormUtility.php";s:4:"9b24";s:31:"Classes/Utility/FormUtility.php";s:4:"756d";s:35:"Classes/Utility/LanguageUtility.php";s:4:"6596";s:35:"Classes/Utility/TemplateUtility.php";s:4:"1ea7";s:33:"Classes/Utility/UploadUtility.php";s:4:"87af";s:30:"Classes/Utility/URLUtility.php";s:4:"5342";s:32:"Classes/Utility/ValueUtility.php";s:4:"00a8";s:33:"Classes/Utility/WizardUtility.php";s:4:"2650";s:48:"Classes/ViewHelpers/DetailTemplateViewHelper.php";s:4:"b120";s:46:"Classes/ViewHelpers/EditTemplateViewHelper.php";s:4:"bb44";s:38:"Classes/ViewHelpers/EditViewHelper.php";s:4:"e20f";s:37:"Classes/ViewHelpers/ForViewHelper.php";s:4:"1c42";s:38:"Classes/ViewHelpers/LinkViewHelper.php";s:4:"e14e";s:46:"Classes/ViewHelpers/ListTemplateViewHelper.php";s:4:"19e8";s:45:"Classes/ViewHelpers/NewTemplateViewHelper.php";s:4:"c1bc";s:37:"Classes/ViewHelpers/NewViewHelper.php";s:4:"dbd6";s:45:"Classes/ViewHelpers/PageBrowserViewHelper.php";s:4:"c633";s:47:"Classes/ViewHelpers/RecordBrowserViewHelper.php";s:4:"a2f3";s:42:"Classes/ViewHelpers/RelationViewHelper.php";s:4:"f58a";s:45:"Classes/ViewHelpers/RenderValueViewHelper.php";s:4:"eaef";s:48:"Classes/ViewHelpers/SearchTemplateViewHelper.php";s:4:"59b2";s:40:"Classes/ViewHelpers/SearchViewHelper.php";s:4:"1826";s:45:"Classes/ViewHelpers/SortColumnsViewHelper.php";s:4:"a7f5";s:52:"Configuration/FlexForms/flexform_ezqueriesplugin.xml";s:4:"01e0";s:38:"Configuration/TCA/RecordManagement.php";s:4:"ac60";s:38:"Configuration/TypoScript/constants.txt";s:4:"0c0e";s:34:"Configuration/TypoScript/setup.txt";s:4:"dc49";s:40:"Resources/Private/Language/locallang.xml";s:4:"ac79";s:87:"Resources/Private/Language/locallang_csh_tx_ezqueries_domain_model_recordmanagement.xml";s:4:"3893";s:43:"Resources/Private/Language/locallang_db.xml";s:4:"3625";s:38:"Resources/Private/Layouts/Default.html";s:4:"e200";s:42:"Resources/Private/Partials/FormErrors.html";s:4:"ec7e";s:59:"Resources/Private/Partials/RecordManagement/FormFields.html";s:4:"d41d";s:59:"Resources/Private/Partials/RecordManagement/Properties.html";s:4:"c613";s:56:"Resources/Private/Templates/RecordManagement/Delete.html";s:4:"4f6a";s:56:"Resources/Private/Templates/RecordManagement/Detail.html";s:4:"1338";s:54:"Resources/Private/Templates/RecordManagement/Edit.html";s:4:"aff5";s:55:"Resources/Private/Templates/RecordManagement/Empty.html";s:4:"3bbb";s:55:"Resources/Private/Templates/RecordManagement/Error.html";s:4:"deb0";s:54:"Resources/Private/Templates/RecordManagement/List.html";s:4:"0e6b";s:53:"Resources/Private/Templates/RecordManagement/New.html";s:4:"99dc";s:58:"Resources/Private/Templates/RecordManagement/Relation.html";s:4:"c086";s:56:"Resources/Private/Templates/RecordManagement/Search.html";s:4:"ee38";s:37:"Resources/Public/CSS/tx_ezqueries.css";s:4:"b964";s:47:"Resources/Public/CSS/tx_ezqueries_templates.css";s:4:"d423";s:58:"Resources/Public/CSS/jquery-ui/jquery-ui-1.8.20.custom.css";s:4:"80ea";s:68:"Resources/Public/CSS/jquery-ui/images/ui-bg_flat_0_aaaaaa_40x100.png";s:4:"2a44";s:69:"Resources/Public/CSS/jquery-ui/images/ui-bg_flat_75_ffffff_40x100.png";s:4:"8692";s:69:"Resources/Public/CSS/jquery-ui/images/ui-bg_glass_55_fbf9ee_1x400.png";s:4:"f8f4";s:69:"Resources/Public/CSS/jquery-ui/images/ui-bg_glass_65_ffffff_1x400.png";s:4:"e5a8";s:69:"Resources/Public/CSS/jquery-ui/images/ui-bg_glass_75_dadada_1x400.png";s:4:"c12c";s:69:"Resources/Public/CSS/jquery-ui/images/ui-bg_glass_75_e6e6e6_1x400.png";s:4:"f425";s:69:"Resources/Public/CSS/jquery-ui/images/ui-bg_glass_95_fef1ec_1x400.png";s:4:"5a3b";s:78:"Resources/Public/CSS/jquery-ui/images/ui-bg_highlight-soft_75_cccccc_1x100.png";s:4:"72c5";s:65:"Resources/Public/CSS/jquery-ui/images/ui-icons_222222_256x240.png";s:4:"ebe6";s:65:"Resources/Public/CSS/jquery-ui/images/ui-icons_2e83ff_256x240.png";s:4:"2b99";s:65:"Resources/Public/CSS/jquery-ui/images/ui-icons_454545_256x240.png";s:4:"119d";s:65:"Resources/Public/CSS/jquery-ui/images/ui-icons_888888_256x240.png";s:4:"9c46";s:65:"Resources/Public/CSS/jquery-ui/images/ui-icons_cd0a0a_256x240.png";s:4:"3e45";s:31:"Resources/Public/Icons/help.gif";s:4:"2cb6";s:31:"Resources/Public/Icons/help.png";s:4:"a3f0";s:36:"Resources/Public/Icons/help_icon.png";s:4:"3ad5";s:41:"Resources/Public/Icons/help_icon_blue.png";s:4:"16c6";s:35:"Resources/Public/Icons/relation.gif";s:4:"e615";s:69:"Resources/Public/Icons/tx_ezqueries_domain_model_recordmanagement.gif";s:4:"1103";s:39:"Resources/Public/Images/ajax-loader.gif";s:4:"394b";s:31:"Resources/Public/Images/asc.png";s:4:"6f85";s:30:"Resources/Public/Images/bg.jpg";s:4:"a87c";s:37:"Resources/Public/Images/bg_header.jpg";s:4:"ee6b";s:39:"Resources/Public/Images/delete_icon.png";s:4:"824e";s:32:"Resources/Public/Images/desc.png";s:4:"9557";s:42:"Resources/Public/Images/loading-screen.png";s:4:"b71a";s:35:"Resources/Public/Images/loading.gif";s:4:"2da0";s:30:"Resources/Public/JS/default.js";s:4:"9def";s:39:"Resources/Public/JS/jquery-1.7.2.min.js";s:4:"b8d6";s:50:"Resources/Public/JS/jquery-ui-1.8.20.custom.min.js";s:4:"925c";s:42:"Resources/Public/JS/jquery.fileuploader.js";s:4:"4c67";s:39:"Resources/Public/JS/jquery.functions.js";s:4:"9ac8";s:39:"Resources/Public/JS/jquery.utilities.js";s:4:"4e34";s:57:"Resources/Public/JS/jquery.validate.additional-methods.js";s:4:"9c3a";s:61:"Resources/Public/JS/jquery.validate.additional-methods.min.js";s:4:"4c6c";s:38:"Resources/Public/JS/jquery.validate.js";s:4:"71e5";s:42:"Resources/Public/JS/jquery.validate.min.js";s:4:"4558";s:59:"Resources/Public/JS/localization/jquery.ui.datepicker-de.js";s:4:"ae34";s:59:"Resources/Public/JS/localization/jquery.ui.datepicker-en.js";s:4:"741c";s:47:"Resources/Public/JS/localization/messages_de.js";s:4:"a814";s:46:"Resources/Public/JS/tiny_mce/jquery.tinymce.js";s:4:"7514";s:40:"Resources/Public/JS/tiny_mce/license.txt";s:4:"0571";s:40:"Resources/Public/JS/tiny_mce/tiny_mce.js";s:4:"1e5c";s:46:"Resources/Public/JS/tiny_mce/tiny_mce_popup.js";s:4:"554b";s:44:"Resources/Public/JS/tiny_mce/tiny_mce_src.js";s:4:"6ecf";s:40:"Resources/Public/JS/tiny_mce/langs/de.js";s:4:"dc5a";s:40:"Resources/Public/JS/tiny_mce/langs/en.js";s:4:"eff3";s:59:"Resources/Public/JS/tiny_mce/plugins/advhr/editor_plugin.js";s:4:"d0a0";s:63:"Resources/Public/JS/tiny_mce/plugins/advhr/editor_plugin_src.js";s:4:"a7fd";s:51:"Resources/Public/JS/tiny_mce/plugins/advhr/rule.htm";s:4:"492e";s:56:"Resources/Public/JS/tiny_mce/plugins/advhr/css/advhr.css";s:4:"15df";s:53:"Resources/Public/JS/tiny_mce/plugins/advhr/js/rule.js";s:4:"ef46";s:58:"Resources/Public/JS/tiny_mce/plugins/advhr/langs/de_dlg.js";s:4:"f620";s:58:"Resources/Public/JS/tiny_mce/plugins/advhr/langs/en_dlg.js";s:4:"af62";s:62:"Resources/Public/JS/tiny_mce/plugins/advimage/editor_plugin.js";s:4:"8af1";s:66:"Resources/Public/JS/tiny_mce/plugins/advimage/editor_plugin_src.js";s:4:"958a";s:55:"Resources/Public/JS/tiny_mce/plugins/advimage/image.htm";s:4:"0d20";s:62:"Resources/Public/JS/tiny_mce/plugins/advimage/css/advimage.css";s:4:"1ccd";s:60:"Resources/Public/JS/tiny_mce/plugins/advimage/img/sample.gif";s:4:"b9c7";s:57:"Resources/Public/JS/tiny_mce/plugins/advimage/js/image.js";s:4:"d071";s:61:"Resources/Public/JS/tiny_mce/plugins/advimage/langs/de_dlg.js";s:4:"6135";s:61:"Resources/Public/JS/tiny_mce/plugins/advimage/langs/en_dlg.js";s:4:"6f80";s:61:"Resources/Public/JS/tiny_mce/plugins/advlink/editor_plugin.js";s:4:"5e44";s:65:"Resources/Public/JS/tiny_mce/plugins/advlink/editor_plugin_src.js";s:4:"4104";s:53:"Resources/Public/JS/tiny_mce/plugins/advlink/link.htm";s:4:"206e";s:60:"Resources/Public/JS/tiny_mce/plugins/advlink/css/advlink.css";s:4:"aaf2";s:58:"Resources/Public/JS/tiny_mce/plugins/advlink/js/advlink.js";s:4:"80dd";s:60:"Resources/Public/JS/tiny_mce/plugins/advlink/langs/de_dlg.js";s:4:"cd94";s:60:"Resources/Public/JS/tiny_mce/plugins/advlink/langs/en_dlg.js";s:4:"8da3";s:61:"Resources/Public/JS/tiny_mce/plugins/advlist/editor_plugin.js";s:4:"5f1c";s:65:"Resources/Public/JS/tiny_mce/plugins/advlist/editor_plugin_src.js";s:4:"d451";s:62:"Resources/Public/JS/tiny_mce/plugins/autolink/editor_plugin.js";s:4:"5091";s:66:"Resources/Public/JS/tiny_mce/plugins/autolink/editor_plugin_src.js";s:4:"f03a";s:64:"Resources/Public/JS/tiny_mce/plugins/autoresize/editor_plugin.js";s:4:"adf5";s:68:"Resources/Public/JS/tiny_mce/plugins/autoresize/editor_plugin_src.js";s:4:"4d1c";s:62:"Resources/Public/JS/tiny_mce/plugins/autosave/editor_plugin.js";s:4:"ae4c";s:66:"Resources/Public/JS/tiny_mce/plugins/autosave/editor_plugin_src.js";s:4:"6177";s:60:"Resources/Public/JS/tiny_mce/plugins/bbcode/editor_plugin.js";s:4:"3174";s:64:"Resources/Public/JS/tiny_mce/plugins/bbcode/editor_plugin_src.js";s:4:"8424";s:65:"Resources/Public/JS/tiny_mce/plugins/contextmenu/editor_plugin.js";s:4:"5a0f";s:69:"Resources/Public/JS/tiny_mce/plugins/contextmenu/editor_plugin_src.js";s:4:"b923";s:68:"Resources/Public/JS/tiny_mce/plugins/directionality/editor_plugin.js";s:4:"e2d9";s:72:"Resources/Public/JS/tiny_mce/plugins/directionality/editor_plugin_src.js";s:4:"14b7";s:62:"Resources/Public/JS/tiny_mce/plugins/emotions/editor_plugin.js";s:4:"98cb";s:66:"Resources/Public/JS/tiny_mce/plugins/emotions/editor_plugin_src.js";s:4:"4cbc";s:58:"Resources/Public/JS/tiny_mce/plugins/emotions/emotions.htm";s:4:"9378";s:65:"Resources/Public/JS/tiny_mce/plugins/emotions/img/smiley-cool.gif";s:4:"e26e";s:64:"Resources/Public/JS/tiny_mce/plugins/emotions/img/smiley-cry.gif";s:4:"e72b";s:71:"Resources/Public/JS/tiny_mce/plugins/emotions/img/smiley-embarassed.gif";s:4:"d591";s:74:"Resources/Public/JS/tiny_mce/plugins/emotions/img/smiley-foot-in-mouth.gif";s:4:"c12d";s:66:"Resources/Public/JS/tiny_mce/plugins/emotions/img/smiley-frown.gif";s:4:"5993";s:69:"Resources/Public/JS/tiny_mce/plugins/emotions/img/smiley-innocent.gif";s:4:"ec04";s:65:"Resources/Public/JS/tiny_mce/plugins/emotions/img/smiley-kiss.gif";s:4:"4ae8";s:69:"Resources/Public/JS/tiny_mce/plugins/emotions/img/smiley-laughing.gif";s:4:"c37f";s:72:"Resources/Public/JS/tiny_mce/plugins/emotions/img/smiley-money-mouth.gif";s:4:"11c1";s:67:"Resources/Public/JS/tiny_mce/plugins/emotions/img/smiley-sealed.gif";s:4:"bb82";s:66:"Resources/Public/JS/tiny_mce/plugins/emotions/img/smiley-smile.gif";s:4:"2968";s:70:"Resources/Public/JS/tiny_mce/plugins/emotions/img/smiley-surprised.gif";s:4:"2e13";s:71:"Resources/Public/JS/tiny_mce/plugins/emotions/img/smiley-tongue-out.gif";s:4:"5ec3";s:70:"Resources/Public/JS/tiny_mce/plugins/emotions/img/smiley-undecided.gif";s:4:"3c0c";s:65:"Resources/Public/JS/tiny_mce/plugins/emotions/img/smiley-wink.gif";s:4:"8972";s:65:"Resources/Public/JS/tiny_mce/plugins/emotions/img/smiley-yell.gif";s:4:"19bb";s:60:"Resources/Public/JS/tiny_mce/plugins/emotions/js/emotions.js";s:4:"85ef";s:61:"Resources/Public/JS/tiny_mce/plugins/emotions/langs/de_dlg.js";s:4:"27f1";s:61:"Resources/Public/JS/tiny_mce/plugins/emotions/langs/en_dlg.js";s:4:"62c0";s:55:"Resources/Public/JS/tiny_mce/plugins/example/dialog.htm";s:4:"e617";s:61:"Resources/Public/JS/tiny_mce/plugins/example/editor_plugin.js";s:4:"e0a1";s:65:"Resources/Public/JS/tiny_mce/plugins/example/editor_plugin_src.js";s:4:"3fcf";s:60:"Resources/Public/JS/tiny_mce/plugins/example/img/example.gif";s:4:"6036";s:57:"Resources/Public/JS/tiny_mce/plugins/example/js/dialog.js";s:4:"8324";s:56:"Resources/Public/JS/tiny_mce/plugins/example/langs/en.js";s:4:"78c8";s:60:"Resources/Public/JS/tiny_mce/plugins/example/langs/en_dlg.js";s:4:"7aec";s:72:"Resources/Public/JS/tiny_mce/plugins/example_dependency/editor_plugin.js";s:4:"405d";s:76:"Resources/Public/JS/tiny_mce/plugins/example_dependency/editor_plugin_src.js";s:4:"4738";s:62:"Resources/Public/JS/tiny_mce/plugins/fullpage/editor_plugin.js";s:4:"5dfb";s:66:"Resources/Public/JS/tiny_mce/plugins/fullpage/editor_plugin_src.js";s:4:"636a";s:58:"Resources/Public/JS/tiny_mce/plugins/fullpage/fullpage.htm";s:4:"8c58";s:62:"Resources/Public/JS/tiny_mce/plugins/fullpage/css/fullpage.css";s:4:"2ac6";s:60:"Resources/Public/JS/tiny_mce/plugins/fullpage/js/fullpage.js";s:4:"a791";s:61:"Resources/Public/JS/tiny_mce/plugins/fullpage/langs/de_dlg.js";s:4:"138d";s:61:"Resources/Public/JS/tiny_mce/plugins/fullpage/langs/en_dlg.js";s:4:"963f";s:64:"Resources/Public/JS/tiny_mce/plugins/fullscreen/editor_plugin.js";s:4:"10a8";s:68:"Resources/Public/JS/tiny_mce/plugins/fullscreen/editor_plugin_src.js";s:4:"aa97";s:62:"Resources/Public/JS/tiny_mce/plugins/fullscreen/fullscreen.htm";s:4:"cbca";s:61:"Resources/Public/JS/tiny_mce/plugins/iespell/editor_plugin.js";s:4:"2252";s:65:"Resources/Public/JS/tiny_mce/plugins/iespell/editor_plugin_src.js";s:4:"311e";s:66:"Resources/Public/JS/tiny_mce/plugins/inlinepopups/editor_plugin.js";s:4:"cbfe";s:70:"Resources/Public/JS/tiny_mce/plugins/inlinepopups/editor_plugin_src.js";s:4:"4b1b";s:62:"Resources/Public/JS/tiny_mce/plugins/inlinepopups/template.htm";s:4:"3d7e";s:78:"Resources/Public/JS/tiny_mce/plugins/inlinepopups/skins/clearlooks2/window.css";s:4:"f715";s:81:"Resources/Public/JS/tiny_mce/plugins/inlinepopups/skins/clearlooks2/img/alert.gif";s:4:"568d";s:82:"Resources/Public/JS/tiny_mce/plugins/inlinepopups/skins/clearlooks2/img/button.gif";s:4:"19f8";s:83:"Resources/Public/JS/tiny_mce/plugins/inlinepopups/skins/clearlooks2/img/buttons.gif";s:4:"1743";s:83:"Resources/Public/JS/tiny_mce/plugins/inlinepopups/skins/clearlooks2/img/confirm.gif";s:4:"1bc3";s:83:"Resources/Public/JS/tiny_mce/plugins/inlinepopups/skins/clearlooks2/img/corners.gif";s:4:"5529";s:86:"Resources/Public/JS/tiny_mce/plugins/inlinepopups/skins/clearlooks2/img/horizontal.gif";s:4:"0365";s:84:"Resources/Public/JS/tiny_mce/plugins/inlinepopups/skins/clearlooks2/img/vertical.gif";s:4:"0261";s:68:"Resources/Public/JS/tiny_mce/plugins/insertdatetime/editor_plugin.js";s:4:"d990";s:72:"Resources/Public/JS/tiny_mce/plugins/insertdatetime/editor_plugin_src.js";s:4:"32a2";s:59:"Resources/Public/JS/tiny_mce/plugins/layer/editor_plugin.js";s:4:"4e5f";s:63:"Resources/Public/JS/tiny_mce/plugins/layer/editor_plugin_src.js";s:4:"3754";s:66:"Resources/Public/JS/tiny_mce/plugins/legacyoutput/editor_plugin.js";s:4:"b732";s:70:"Resources/Public/JS/tiny_mce/plugins/legacyoutput/editor_plugin_src.js";s:4:"8a0a";s:59:"Resources/Public/JS/tiny_mce/plugins/lists/editor_plugin.js";s:4:"46ba";s:63:"Resources/Public/JS/tiny_mce/plugins/lists/editor_plugin_src.js";s:4:"04e1";s:59:"Resources/Public/JS/tiny_mce/plugins/media/editor_plugin.js";s:4:"5ad7";s:63:"Resources/Public/JS/tiny_mce/plugins/media/editor_plugin_src.js";s:4:"1d03";s:52:"Resources/Public/JS/tiny_mce/plugins/media/media.htm";s:4:"be58";s:58:"Resources/Public/JS/tiny_mce/plugins/media/moxieplayer.swf";s:4:"9217";s:56:"Resources/Public/JS/tiny_mce/plugins/media/css/media.css";s:4:"f211";s:54:"Resources/Public/JS/tiny_mce/plugins/media/js/embed.js";s:4:"39eb";s:54:"Resources/Public/JS/tiny_mce/plugins/media/js/media.js";s:4:"4f35";s:58:"Resources/Public/JS/tiny_mce/plugins/media/langs/de_dlg.js";s:4:"7b71";s:58:"Resources/Public/JS/tiny_mce/plugins/media/langs/en_dlg.js";s:4:"9523";s:65:"Resources/Public/JS/tiny_mce/plugins/nonbreaking/editor_plugin.js";s:4:"232f";s:69:"Resources/Public/JS/tiny_mce/plugins/nonbreaking/editor_plugin_src.js";s:4:"fefb";s:65:"Resources/Public/JS/tiny_mce/plugins/noneditable/editor_plugin.js";s:4:"b332";s:69:"Resources/Public/JS/tiny_mce/plugins/noneditable/editor_plugin_src.js";s:4:"273d";s:63:"Resources/Public/JS/tiny_mce/plugins/pagebreak/editor_plugin.js";s:4:"8be3";s:67:"Resources/Public/JS/tiny_mce/plugins/pagebreak/editor_plugin_src.js";s:4:"c2d4";s:59:"Resources/Public/JS/tiny_mce/plugins/paste/editor_plugin.js";s:4:"e7e3";s:63:"Resources/Public/JS/tiny_mce/plugins/paste/editor_plugin_src.js";s:4:"03ee";s:56:"Resources/Public/JS/tiny_mce/plugins/paste/pastetext.htm";s:4:"8f21";s:56:"Resources/Public/JS/tiny_mce/plugins/paste/pasteword.htm";s:4:"8b94";s:58:"Resources/Public/JS/tiny_mce/plugins/paste/js/pastetext.js";s:4:"d6e4";s:58:"Resources/Public/JS/tiny_mce/plugins/paste/js/pasteword.js";s:4:"1125";s:58:"Resources/Public/JS/tiny_mce/plugins/paste/langs/de_dlg.js";s:4:"11c1";s:58:"Resources/Public/JS/tiny_mce/plugins/paste/langs/en_dlg.js";s:4:"6ea2";s:61:"Resources/Public/JS/tiny_mce/plugins/preview/editor_plugin.js";s:4:"9252";s:65:"Resources/Public/JS/tiny_mce/plugins/preview/editor_plugin_src.js";s:4:"6f9c";s:57:"Resources/Public/JS/tiny_mce/plugins/preview/example.html";s:4:"9b92";s:57:"Resources/Public/JS/tiny_mce/plugins/preview/preview.html";s:4:"bb02";s:62:"Resources/Public/JS/tiny_mce/plugins/preview/jscripts/embed.js";s:4:"39eb";s:59:"Resources/Public/JS/tiny_mce/plugins/print/editor_plugin.js";s:4:"53eb";s:63:"Resources/Public/JS/tiny_mce/plugins/print/editor_plugin_src.js";s:4:"f115";s:58:"Resources/Public/JS/tiny_mce/plugins/save/editor_plugin.js";s:4:"307a";s:62:"Resources/Public/JS/tiny_mce/plugins/save/editor_plugin_src.js";s:4:"4dcb";s:67:"Resources/Public/JS/tiny_mce/plugins/searchreplace/editor_plugin.js";s:4:"ed4f";s:71:"Resources/Public/JS/tiny_mce/plugins/searchreplace/editor_plugin_src.js";s:4:"7292";s:68:"Resources/Public/JS/tiny_mce/plugins/searchreplace/searchreplace.htm";s:4:"4d01";s:72:"Resources/Public/JS/tiny_mce/plugins/searchreplace/css/searchreplace.css";s:4:"ad0a";s:70:"Resources/Public/JS/tiny_mce/plugins/searchreplace/js/searchreplace.js";s:4:"9adf";s:66:"Resources/Public/JS/tiny_mce/plugins/searchreplace/langs/de_dlg.js";s:4:"7052";s:66:"Resources/Public/JS/tiny_mce/plugins/searchreplace/langs/en_dlg.js";s:4:"fbd4";s:66:"Resources/Public/JS/tiny_mce/plugins/spellchecker/editor_plugin.js";s:4:"750c";s:70:"Resources/Public/JS/tiny_mce/plugins/spellchecker/editor_plugin_src.js";s:4:"05bf";s:65:"Resources/Public/JS/tiny_mce/plugins/spellchecker/css/content.css";s:4:"ac0c";s:63:"Resources/Public/JS/tiny_mce/plugins/spellchecker/img/wline.gif";s:4:"c136";s:59:"Resources/Public/JS/tiny_mce/plugins/style/editor_plugin.js";s:4:"f9bc";s:63:"Resources/Public/JS/tiny_mce/plugins/style/editor_plugin_src.js";s:4:"2ec5";s:52:"Resources/Public/JS/tiny_mce/plugins/style/props.htm";s:4:"32f3";s:53:"Resources/Public/JS/tiny_mce/plugins/style/readme.txt";s:4:"ced5";s:56:"Resources/Public/JS/tiny_mce/plugins/style/css/props.css";s:4:"c2d2";s:54:"Resources/Public/JS/tiny_mce/plugins/style/js/props.js";s:4:"f361";s:58:"Resources/Public/JS/tiny_mce/plugins/style/langs/de_dlg.js";s:4:"10ff";s:58:"Resources/Public/JS/tiny_mce/plugins/style/langs/en_dlg.js";s:4:"62a8";s:62:"Resources/Public/JS/tiny_mce/plugins/tabfocus/editor_plugin.js";s:4:"d3e5";s:66:"Resources/Public/JS/tiny_mce/plugins/tabfocus/editor_plugin_src.js";s:4:"1288";s:51:"Resources/Public/JS/tiny_mce/plugins/table/cell.htm";s:4:"f427";s:59:"Resources/Public/JS/tiny_mce/plugins/table/editor_plugin.js";s:4:"d8fb";s:63:"Resources/Public/JS/tiny_mce/plugins/table/editor_plugin_src.js";s:4:"2e3c";s:58:"Resources/Public/JS/tiny_mce/plugins/table/merge_cells.htm";s:4:"f939";s:50:"Resources/Public/JS/tiny_mce/plugins/table/row.htm";s:4:"7ad9";s:52:"Resources/Public/JS/tiny_mce/plugins/table/table.htm";s:4:"88fb";s:55:"Resources/Public/JS/tiny_mce/plugins/table/css/cell.css";s:4:"5639";s:54:"Resources/Public/JS/tiny_mce/plugins/table/css/row.css";s:4:"81a7";s:56:"Resources/Public/JS/tiny_mce/plugins/table/css/table.css";s:4:"f5e6";s:53:"Resources/Public/JS/tiny_mce/plugins/table/js/cell.js";s:4:"8b0d";s:60:"Resources/Public/JS/tiny_mce/plugins/table/js/merge_cells.js";s:4:"3650";s:52:"Resources/Public/JS/tiny_mce/plugins/table/js/row.js";s:4:"d1cf";s:54:"Resources/Public/JS/tiny_mce/plugins/table/js/table.js";s:4:"b4dc";s:58:"Resources/Public/JS/tiny_mce/plugins/table/langs/de_dlg.js";s:4:"8cf6";s:58:"Resources/Public/JS/tiny_mce/plugins/table/langs/en_dlg.js";s:4:"ee34";s:55:"Resources/Public/JS/tiny_mce/plugins/template/blank.htm";s:4:"9553";s:62:"Resources/Public/JS/tiny_mce/plugins/template/editor_plugin.js";s:4:"70cb";s:66:"Resources/Public/JS/tiny_mce/plugins/template/editor_plugin_src.js";s:4:"336a";s:58:"Resources/Public/JS/tiny_mce/plugins/template/template.htm";s:4:"b55e";s:62:"Resources/Public/JS/tiny_mce/plugins/template/css/template.css";s:4:"5b2c";s:60:"Resources/Public/JS/tiny_mce/plugins/template/js/template.js";s:4:"75ab";s:61:"Resources/Public/JS/tiny_mce/plugins/template/langs/de_dlg.js";s:4:"49ce";s:61:"Resources/Public/JS/tiny_mce/plugins/template/langs/en_dlg.js";s:4:"1ce0";s:66:"Resources/Public/JS/tiny_mce/plugins/visualblocks/editor_plugin.js";s:4:"592e";s:70:"Resources/Public/JS/tiny_mce/plugins/visualblocks/editor_plugin_src.js";s:4:"6d1a";s:70:"Resources/Public/JS/tiny_mce/plugins/visualblocks/css/visualblocks.css";s:4:"1fe4";s:65:"Resources/Public/JS/tiny_mce/plugins/visualchars/editor_plugin.js";s:4:"e494";s:69:"Resources/Public/JS/tiny_mce/plugins/visualchars/editor_plugin_src.js";s:4:"f285";s:63:"Resources/Public/JS/tiny_mce/plugins/wordcount/editor_plugin.js";s:4:"4d94";s:67:"Resources/Public/JS/tiny_mce/plugins/wordcount/editor_plugin_src.js";s:4:"1a06";s:56:"Resources/Public/JS/tiny_mce/plugins/xhtmlxtras/abbr.htm";s:4:"e514";s:59:"Resources/Public/JS/tiny_mce/plugins/xhtmlxtras/acronym.htm";s:4:"ec63";s:62:"Resources/Public/JS/tiny_mce/plugins/xhtmlxtras/attributes.htm";s:4:"cf54";s:56:"Resources/Public/JS/tiny_mce/plugins/xhtmlxtras/cite.htm";s:4:"1468";s:55:"Resources/Public/JS/tiny_mce/plugins/xhtmlxtras/del.htm";s:4:"81bb";s:64:"Resources/Public/JS/tiny_mce/plugins/xhtmlxtras/editor_plugin.js";s:4:"c9f9";s:68:"Resources/Public/JS/tiny_mce/plugins/xhtmlxtras/editor_plugin_src.js";s:4:"b8b8";s:55:"Resources/Public/JS/tiny_mce/plugins/xhtmlxtras/ins.htm";s:4:"47e7";s:66:"Resources/Public/JS/tiny_mce/plugins/xhtmlxtras/css/attributes.css";s:4:"abc1";s:61:"Resources/Public/JS/tiny_mce/plugins/xhtmlxtras/css/popup.css";s:4:"ed53";s:58:"Resources/Public/JS/tiny_mce/plugins/xhtmlxtras/js/abbr.js";s:4:"d91a";s:61:"Resources/Public/JS/tiny_mce/plugins/xhtmlxtras/js/acronym.js";s:4:"0a19";s:64:"Resources/Public/JS/tiny_mce/plugins/xhtmlxtras/js/attributes.js";s:4:"e75f";s:58:"Resources/Public/JS/tiny_mce/plugins/xhtmlxtras/js/cite.js";s:4:"ba39";s:57:"Resources/Public/JS/tiny_mce/plugins/xhtmlxtras/js/del.js";s:4:"be96";s:68:"Resources/Public/JS/tiny_mce/plugins/xhtmlxtras/js/element_common.js";s:4:"b817";s:57:"Resources/Public/JS/tiny_mce/plugins/xhtmlxtras/js/ins.js";s:4:"6148";s:63:"Resources/Public/JS/tiny_mce/plugins/xhtmlxtras/langs/de_dlg.js";s:4:"9936";s:63:"Resources/Public/JS/tiny_mce/plugins/xhtmlxtras/langs/en_dlg.js";s:4:"45db";s:54:"Resources/Public/JS/tiny_mce/themes/advanced/about.htm";s:4:"ff13";s:55:"Resources/Public/JS/tiny_mce/themes/advanced/anchor.htm";s:4:"79d4";s:56:"Resources/Public/JS/tiny_mce/themes/advanced/charmap.htm";s:4:"be71";s:61:"Resources/Public/JS/tiny_mce/themes/advanced/color_picker.htm";s:4:"8db4";s:63:"Resources/Public/JS/tiny_mce/themes/advanced/editor_template.js";s:4:"a2b3";s:67:"Resources/Public/JS/tiny_mce/themes/advanced/editor_template_src.js";s:4:"1fa3";s:54:"Resources/Public/JS/tiny_mce/themes/advanced/image.htm";s:4:"2cb9";s:53:"Resources/Public/JS/tiny_mce/themes/advanced/link.htm";s:4:"65c5";s:58:"Resources/Public/JS/tiny_mce/themes/advanced/shortcuts.htm";s:4:"2bae";s:62:"Resources/Public/JS/tiny_mce/themes/advanced/source_editor.htm";s:4:"42f3";s:64:"Resources/Public/JS/tiny_mce/themes/advanced/img/colorpicker.jpg";s:4:"9bcc";s:58:"Resources/Public/JS/tiny_mce/themes/advanced/img/flash.gif";s:4:"33ad";s:58:"Resources/Public/JS/tiny_mce/themes/advanced/img/icons.gif";s:4:"75ad";s:59:"Resources/Public/JS/tiny_mce/themes/advanced/img/iframe.gif";s:4:"a1af";s:62:"Resources/Public/JS/tiny_mce/themes/advanced/img/pagebreak.gif";s:4:"4887";s:62:"Resources/Public/JS/tiny_mce/themes/advanced/img/quicktime.gif";s:4:"61da";s:62:"Resources/Public/JS/tiny_mce/themes/advanced/img/realmedia.gif";s:4:"b973";s:62:"Resources/Public/JS/tiny_mce/themes/advanced/img/shockwave.gif";s:4:"1ce7";s:58:"Resources/Public/JS/tiny_mce/themes/advanced/img/trans.gif";s:4:"12bf";s:58:"Resources/Public/JS/tiny_mce/themes/advanced/img/video.gif";s:4:"f85c";s:65:"Resources/Public/JS/tiny_mce/themes/advanced/img/windowsmedia.gif";s:4:"c327";s:56:"Resources/Public/JS/tiny_mce/themes/advanced/js/about.js";s:4:"606c";s:57:"Resources/Public/JS/tiny_mce/themes/advanced/js/anchor.js";s:4:"da6a";s:58:"Resources/Public/JS/tiny_mce/themes/advanced/js/charmap.js";s:4:"02f4";s:63:"Resources/Public/JS/tiny_mce/themes/advanced/js/color_picker.js";s:4:"cfc0";s:56:"Resources/Public/JS/tiny_mce/themes/advanced/js/image.js";s:4:"75df";s:55:"Resources/Public/JS/tiny_mce/themes/advanced/js/link.js";s:4:"7194";s:64:"Resources/Public/JS/tiny_mce/themes/advanced/js/source_editor.js";s:4:"4cbd";s:56:"Resources/Public/JS/tiny_mce/themes/advanced/langs/de.js";s:4:"0526";s:60:"Resources/Public/JS/tiny_mce/themes/advanced/langs/de_dlg.js";s:4:"3fe9";s:56:"Resources/Public/JS/tiny_mce/themes/advanced/langs/en.js";s:4:"58c8";s:60:"Resources/Public/JS/tiny_mce/themes/advanced/langs/en_dlg.js";s:4:"3a2c";s:70:"Resources/Public/JS/tiny_mce/themes/advanced/skins/default/content.css";s:4:"1779";s:69:"Resources/Public/JS/tiny_mce/themes/advanced/skins/default/dialog.css";s:4:"ab94";s:65:"Resources/Public/JS/tiny_mce/themes/advanced/skins/default/ui.css";s:4:"823d";s:74:"Resources/Public/JS/tiny_mce/themes/advanced/skins/default/img/buttons.png";s:4:"33b2";s:72:"Resources/Public/JS/tiny_mce/themes/advanced/skins/default/img/items.gif";s:4:"d201";s:77:"Resources/Public/JS/tiny_mce/themes/advanced/skins/default/img/menu_arrow.gif";s:4:"e217";s:77:"Resources/Public/JS/tiny_mce/themes/advanced/skins/default/img/menu_check.gif";s:4:"c7d0";s:75:"Resources/Public/JS/tiny_mce/themes/advanced/skins/default/img/progress.gif";s:4:"50c5";s:71:"Resources/Public/JS/tiny_mce/themes/advanced/skins/default/img/tabs.gif";s:4:"6473";s:75:"Resources/Public/JS/tiny_mce/themes/advanced/skins/highcontrast/content.css";s:4:"8294";s:74:"Resources/Public/JS/tiny_mce/themes/advanced/skins/highcontrast/dialog.css";s:4:"f6e9";s:70:"Resources/Public/JS/tiny_mce/themes/advanced/skins/highcontrast/ui.css";s:4:"1bdb";s:67:"Resources/Public/JS/tiny_mce/themes/advanced/skins/o2k7/content.css";s:4:"c696";s:66:"Resources/Public/JS/tiny_mce/themes/advanced/skins/o2k7/dialog.css";s:4:"661f";s:62:"Resources/Public/JS/tiny_mce/themes/advanced/skins/o2k7/ui.css";s:4:"a108";s:68:"Resources/Public/JS/tiny_mce/themes/advanced/skins/o2k7/ui_black.css";s:4:"ba8d";s:69:"Resources/Public/JS/tiny_mce/themes/advanced/skins/o2k7/ui_silver.css";s:4:"b42c";s:73:"Resources/Public/JS/tiny_mce/themes/advanced/skins/o2k7/img/button_bg.png";s:4:"36fd";s:79:"Resources/Public/JS/tiny_mce/themes/advanced/skins/o2k7/img/button_bg_black.png";s:4:"9645";s:80:"Resources/Public/JS/tiny_mce/themes/advanced/skins/o2k7/img/button_bg_silver.png";s:4:"15fb";s:61:"Resources/Public/JS/tiny_mce/themes/simple/editor_template.js";s:4:"3ac3";s:65:"Resources/Public/JS/tiny_mce/themes/simple/editor_template_src.js";s:4:"409e";s:56:"Resources/Public/JS/tiny_mce/themes/simple/img/icons.gif";s:4:"273d";s:54:"Resources/Public/JS/tiny_mce/themes/simple/langs/de.js";s:4:"fa14";s:54:"Resources/Public/JS/tiny_mce/themes/simple/langs/en.js";s:4:"50dc";s:68:"Resources/Public/JS/tiny_mce/themes/simple/skins/default/content.css";s:4:"0f70";s:63:"Resources/Public/JS/tiny_mce/themes/simple/skins/default/ui.css";s:4:"c46c";s:65:"Resources/Public/JS/tiny_mce/themes/simple/skins/o2k7/content.css";s:4:"eb7a";s:60:"Resources/Public/JS/tiny_mce/themes/simple/skins/o2k7/ui.css";s:4:"f0ec";s:71:"Resources/Public/JS/tiny_mce/themes/simple/skins/o2k7/img/button_bg.png";s:4:"405c";s:54:"Resources/Public/JS/tiny_mce/utils/editable_selects.js";s:4:"e760";s:48:"Resources/Public/JS/tiny_mce/utils/form_utils.js";s:4:"579b";s:44:"Resources/Public/JS/tiny_mce/utils/mctabs.js";s:4:"51b9";s:46:"Resources/Public/JS/tiny_mce/utils/validate.js";s:4:"bfba";s:43:"Tests/Domain/Model/RecordManagementTest.php";s:4:"eb03";}',
);

?>