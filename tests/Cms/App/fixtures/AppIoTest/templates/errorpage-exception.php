<?php

use Infinri\Exception\ErrorPageException;

throw new ErrorPageException(
	fallback: 'Exception message',
	httpCode: 403
);
