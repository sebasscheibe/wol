<?php
declare(strict_types=1);
// SPDX-FileCopyrightText: Sebastian Scheibe <sebascheibe@gmail.com>
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\WOL\Tests\Unit\Controller;

use PHPUnit\Framework\TestCase;

use OCP\AppFramework\Http;
use OCP\IRequest;

use OCA\WOL\Service\DeviceNotFound;
use OCA\WOL\Service\DeviceService;
use OCA\WOL\Controller\DeviceController;

class DeviceControllerTest extends TestCase {
	protected DeviceController $controller;
	protected string $userId = 'john';
	protected $service;
	protected $request;

	public function setUp(): void {
		$this->request = $this->getMockBuilder(IRequest::class)->getMock();
		$this->service = $this->getMockBuilder(DeviceService::class)
			->disableOriginalConstructor()
			->getMock();
		$this->controller = new DeviceController($this->request, $this->service, $this->userId);
	}

	public function testUpdate(): void {
		$device = 'just check if this value is returned correctly';
		$this->service->expects($this->once())
			->method('update')
			->with($this->equalTo(3),
					$this->equalTo('title'),
					$this->equalTo('mac'),
				   $this->equalTo($this->userId))
			->will($this->returnValue($device));

		$result = $this->controller->update(3, 'title', 'mac');

		$this->assertEquals($device, $result->getData());
	}


	public function testUpdateNotFound(): void {
		// test the correct status code if no device is found
		$this->service->expects($this->once())
			->method('update')
			->will($this->throwException(new DeviceNotFound()));

		$result = $this->controller->update(3, 'title', 'mac');

		$this->assertEquals(Http::STATUS_NOT_FOUND, $result->getStatus());
	}
}
