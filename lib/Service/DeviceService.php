<?php
declare(strict_types=1);
// SPDX-FileCopyrightText: Sebastian Scheibe <sebascheibe@gmail.com>
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\WOL\Service;

use Exception;

use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\MultipleObjectsReturnedException;

use OCA\WOL\Db\Device;
use OCA\WOL\Db\DeviceMapper;

class DeviceService {
	private DeviceMapper $mapper;

	public function __construct(DeviceMapper $mapper) {
		$this->mapper = $mapper;
	}

	/**
	 * @return list<Device>
	 */
	public function findAll(string $userId): array {
		return $this->mapper->findAll($userId);
	}

	/**
	 * @return never
	 */
	private function handleException(Exception $e) {
		if ($e instanceof DoesNotExistException ||
			$e instanceof MultipleObjectsReturnedException) {
			throw new DeviceNotFound($e->getMessage());
		} else {
			throw $e;
		}
	}

	public function find(int $id, string $userId): Device {
		try {
			return $this->mapper->find($id, $userId);

			// in order to be able to plug in different storage backends like files
		// for instance it is a good idea to turn storage related exceptions
		// into service related exceptions so controllers and service users
		// have to deal with only one type of exception
		} catch (Exception $e) {
			$this->handleException($e);
		}
	}

	public function create(string $title, string $mac, string $userId): Device {
		$device = new Device();
		$device->setTitle($title);
		$device->setMAC($mac);
		$device->setUserId($userId);
		return $this->mapper->insert($device);
	}

	public function update(int $id, string $title, string $mac, string $userId): Device {
		try {
			$device = $this->mapper->find($id, $userId);
			$device->setTitle($title);
			$device->setMAC($mac);
			return $this->mapper->update($device);
		} catch (Exception $e) {
			$this->handleException($e);
		}
	}

	public function delete(int $id, string $userId): Device {
		try {
			$device = $this->mapper->find($id, $userId);
			$this->mapper->delete($device);
			return $device;
		} catch (Exception $e) {
			$this->handleException($e);
		}
	}
}
