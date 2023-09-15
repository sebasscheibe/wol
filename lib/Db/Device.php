<?php
declare(strict_types=1);
// SPDX-FileCopyrightText: Sebastian Scheibe <sebascheibe@gmail.com>
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\WOL\Db;

use JsonSerializable;

use OCP\AppFramework\Db\Entity;

/**
 * @method getId(): int
 * @method getTitle(): string
 * @method setTitle(string $title): void
 * @method getMAC(): string
 * @method setMAC(string $mac): void
 * @method getUserId(): string
 * @method setUserId(string $userId): void
 */
class Device extends Entity implements JsonSerializable {
	protected string $title = '';
	protected string $mac = '';
	protected string $userId = '';

	public function jsonSerialize(): array {
		return [
			'id' => $this->id,
			'title' => $this->title,
			'content' => $this->content
		];
	}
}
