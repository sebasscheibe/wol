<?php
declare(strict_types=1);
// SPDX-FileCopyrightText: Sebastian Scheibe <sebascheibe@gmail.com>
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\WOL\AppInfo;

use OCP\AppFramework\App;

class Application extends App {
	public const APP_ID = 'wol';

	public function __construct() {
		parent::__construct(self::APP_ID);
	}
}
