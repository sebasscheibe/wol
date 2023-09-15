<?php
declare(strict_types=1);
// SPDX-FileCopyrightText: Sebastian Scheibe <sebascheibe@gmail.com>
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\WOL\Controller;

use OCA\WOL\AppInfo\Application;
use OCA\WOL\Service\DeviceService;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\DataResponse;
use OCP\IRequest;

class DeviceController extends Controller {
	private DeviceService $service;
	private ?string $userId;

	use Errors;

	public function __construct(IRequest $request,
								DeviceService $service,
								?string $userId) {
		parent::__construct(Application::APP_ID, $request);
		$this->service = $service;
		$this->userId = $userId;
	}

	/**
	 * @NoAdminRequired
	 */
	public function index(): DataResponse {
		return new DataResponse($this->service->findAll($this->userId));
	}

	/**
	 * @NoAdminRequired
	 */
	public function show(int $id): DataResponse {
		return $this->handleNotFound(function () use ($id) {
			return $this->service->find($id, $this->userId);
		});
	}

	/**
	 * @NoAdminRequired
	 */
	public function create(string $title, string $mac): DataResponse {
		return new DataResponse($this->service->create($title, $mac,
			$this->userId));
	}

	/**
	 * @NoAdminRequired
	 */
	public function update(int $id, string $title,
						   string $mac): DataResponse {
		return $this->handleNotFound(function () use ($id, $title, $mac) {
			return $this->service->update($id, $title, $mac, $this->userId);
		});
	}

	/**
	 * @NoAdminRequired
	 */
	public function destroy(int $id): DataResponse {
		return $this->handleNotFound(function () use ($id) {
			return $this->service->delete($id, $this->userId);
		});
	}
}
