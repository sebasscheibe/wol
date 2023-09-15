<?php
declare(strict_types=1);
// SPDX-FileCopyrightText: Sebastian Scheibe <sebascheibe@gmail.com>
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\WOL\Tests\Unit\Service;

use OCA\WOL\Service\DeviceNotFound;
use PHPUnit\Framework\TestCase;

use OCP\AppFramework\Db\DoesNotExistException;

use OCA\WOL\Db\Device;
use OCA\WOL\Service\DeviceService;
use OCA\WOL\Db\DeviceMapper;

class DeviceServiceTest extends TestCase {
	private DeviceService $service;
	private string $userId = 'john';
	private $mapper;

	public function setUp(): void {
		$this->mapper = $this->getMockBuilder(DeviceMapper::class)
			->disableOriginalConstructor()
			->getMock();
		$this->service = new DeviceService($this->mapper);
	}

	public function testUpdate(): void {
		// the existing device
		$device = Device::fromRow([
			'id' => 3,
			'title' => 'yo',
			'mac' => '1234567890AB'
		]);
		$this->mapper->expects($this->once())
			->method('find')
			->with($this->equalTo(3))
			->will($this->returnValue($device));

		// the device when updated
		$updatedDevice = Device::fromRow(['id' => 3]);
		$updatedDevice->setTitle('title');
		$updatedDevice->setMac('mac');
		$this->mapper->expects($this->once())
			->method('update')
			->with($this->equalTo($updatedDevice))
			->will($this->returnValue($updatedDevice));

		$result = $this->service->update(3, 'title', 'mac', $this->userId);

		$this->assertEquals($updatedDevice, $result);
	}

	public function testUpdateNotFound(): void {
		$this->expectException(DeviceNotFound::class);
		// test the correct status code if no device is found
		$this->mapper->expects($this->once())
			->method('find')
			->with($this->equalTo(3))
			->will($this->throwException(new DoesNotExistException('')));

		$this->service->update(3, 'title', 'mac', $this->userId);
	}
}
