<?php
declare(strict_types=1);
// SPDX-FileCopyrightText: Sebastian Scheibe <sebascheibe@gmail.com>
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\WOL\Tests\Unit\Controller;

use OCA\WOL\Controller\DeviceApiController;

class DeviceApiControllerTest extends DeviceControllerTest {
	public function setUp(): void {
		parent::setUp();
		$this->controller = new DeviceApiController($this->request, $this->service, $this->userId);
	}
}
