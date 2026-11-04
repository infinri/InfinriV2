<?php

return [
	// cms classes
	'collection' => 'Infinri\Cms\Collection',
	'file'       => 'Infinri\Cms\File',
	'files'      => 'Infinri\Cms\Files',
	'find'       => 'Infinri\Cms\Find',
	'helpers'    => 'Infinri\Cms\Helpers',
	'html'       => 'Infinri\Cms\Html',
	'kirby'      => 'Infinri\Cms\App',
	'page'       => 'Infinri\Cms\Page',
	'pages'      => 'Infinri\Cms\Pages',
	'pagination' => 'Infinri\Cms\Pagination',
	'r'          => 'Infinri\Cms\R',
	'response'   => 'Infinri\Cms\Response',
	's'          => 'Infinri\Cms\S',
	'sane'       => 'Infinri\Sane\Sane',
	'site'       => 'Infinri\Cms\Site',
	'structure'  => 'Infinri\Cms\Structure',
	'url'        => 'Infinri\Cms\Url',
	'user'       => 'Infinri\Cms\User',
	'users'      => 'Infinri\Cms\Users',
	'visitor'    => 'Infinri\Cms\Visitor',

	// content classes
	'field'      => 'Infinri\Content\Field',

	// data handler
	'data'      => 'Infinri\Data\Data',
	'json'      => 'Infinri\Data\Json',
	'yaml'      => 'Infinri\Data\Yaml',

	// file classes
	'asset'      => 'Infinri\Filesystem\Asset',
	'dir'        => 'Infinri\Filesystem\Dir',
	'f'          => 'Infinri\Filesystem\F',
	'mime'       => 'Infinri\Filesystem\Mime',

	// data classes
	'database'  => 'Infinri\Database\Database',
	'db'        => 'Infinri\Database\Db',

	// exceptions
	'errorpageexception' => 'Infinri\Exception\ErrorPageException',

	// http classes
	'cookie'     => 'Infinri\Http\Cookie',
	'header'     => 'Infinri\Http\Header',
	'remote'     => 'Infinri\Http\Remote',

	// image classes
	'dimensions' => 'Infinri\Image\Dimensions',

	// panel classes
	'panel'      => 'Infinri\Panel\Panel',

	// template classes
	'snippet'    => 'Infinri\Template\Snippet',
	'slot'       => 'Infinri\Template\Slot',

	// toolkit classes
	'a'          => 'Infinri\Toolkit\A',
	'c'          => 'Infinri\Toolkit\Config',
	'config'     => 'Infinri\Toolkit\Config',
	'escape'     => 'Infinri\Toolkit\Escape',
	'i18n'       => 'Infinri\Toolkit\I18n',
	'obj'        => 'Infinri\Toolkit\Obj',
	'str'        => 'Infinri\Toolkit\Str',
	'tpl'        => 'Infinri\Toolkit\Tpl',
	'v'          => 'Infinri\Toolkit\V',
	'xml'        => 'Infinri\Toolkit\Xml',

	// Deprecated aliases:
	// Any of these might be removed at any point in the future
	'infinri\cms\asset'              => 'Infinri\Filesystem\Asset',
	'infinri\cms\content'            => 'Infinri\Content\Content',
	'infinri\cms\dir'                => 'Infinri\Filesystem\Dir',
	'infinri\cms\filename'           => 'Infinri\Filesystem\Filename',
	'infinri\cms\filefoundation'     => 'Infinri\Filesystem\IsFile',
	'infinri\cms\field'            => 'Infinri\Content\Field',
	'infinri\cms\form'               => 'Infinri\Form\Form',
	'infinri\cms\kirbytag'           => 'Infinri\Text\KirbyTag',
	'infinri\cms\kirbytags'          => 'Infinri\Text\KirbyTags',
	'infinri\cms\plugin'             => 'Infinri\Plugin\Plugin',
	'infinri\cms\pluginasset'        => 'Infinri\Plugin\Asset',
	'infinri\cms\pluginassets'       => 'Infinri\Plugin\Assets',
	'infinri\cms\template'           => 'Infinri\Template\Template',
	'infinri\form\options'           => 'Infinri\Option\Options',
	'infinri\form\optionsapi'        => 'Infinri\Option\OptionsApi',
	'infinri\form\optionsquery'      => 'Infinri\Option\OptionsQuery',
	'infinri\toolkit\dir'            => 'Infinri\Filesystem\Dir',
	'infinri\toolkit\f'              => 'Infinri\Filesystem\F',
	'infinri\toolkit\file'           => 'Infinri\Filesystem\File',
	'infinri\toolkit\mime'           => 'Infinri\Filesystem\Mime',
	'infinri\toolkit\query'          => 'Infinri\Query\Query',
];
