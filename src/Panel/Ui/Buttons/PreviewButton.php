<?php

namespace Kirby\Panel\Ui\Buttons;

use Kirby\Toolkit\I18n;

class PreviewButton extends ViewButton
{
	public function __construct(
		public string|null $link
	) {
		parent::__construct(
			class: 'k-preview-view-button',
			icon: 'window',
			link: $link,
			title: I18n::translate('preview')
		);
	}
}
