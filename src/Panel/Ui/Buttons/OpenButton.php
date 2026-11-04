<?php

namespace Kirby\Panel\Ui\Buttons;

use Kirby\Toolkit\I18n;

class OpenButton extends ViewButton
{
	public function __construct(
		public string|null $link,
		public string|null $target = '_blank'
	) {
		parent::__construct(
			class: 'k-open-view-button',
			icon: 'open',
			link: $link,
			target: $target,
			title: I18n::translate('open')
		);
	}
}
