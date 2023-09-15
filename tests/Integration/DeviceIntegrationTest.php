<?php
declare(strict_types=1);
// SPDX-FileCopyrightText: Sebastian Scheibe <sebascheibe@gmail.com>
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\WOL\Tests\Integration\Controller;

use OCP\AppFramework\App;
use OCP\IRequest;
use PHPUnit\Framework\TestCase;

use OCA\WOL\Db\Device;
use OCA\WOL\Db\DeviceMapper;
use OCA\WOL\Controller\DeviceController;

class DeviceIntegrationTest extends TestCase {
	private DeviceController $controller;
	private QBMapper $mapper;
	private string $userId = 'john';

	public function setUp(): void {
		$app = new App('wol');
		$container = $app->getContainer();

		// only replace the user id
		$container->registerService('userId', function () {
			return $this->userId;
		});

		// we do not care about the request but the controller needs it
		$container->registerService(IRequest::class, function () {
			return $this->createMock(IRequest::class);
		});

		$this->controller = $container->get(DeviceController::class);
		$this->mapper = $container->get(DeviceMapper::class);
	}

	public function testUpdate(): void {
		// create a new device that should be updated
		$device = new Device();
		$device->setTitle('old_title');
		$device->setMAC('old_mac');
		$device->setUserId($this->userId);

		$id = $this->mapper->insert($device)->getId();

		// fromRow does not set the fields as updated
		$updatedDevice = Device::fromRow([
			'id' => $id,
			'user_id' => $this->userId
		]);
		$updatedDevice->setMAC('mac');
		$updatedDevice->setTitle('title');

		$result = $this->controller->update($id, 'title', 'mac');

		$this->assertEquals($updatedDevice, $result->getData());

		// clean up
		$this->mapper->delete($result->getData());
	}
}
