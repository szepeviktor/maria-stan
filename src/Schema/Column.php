<?php

declare(strict_types=1);

namespace MariaStan\Schema;

use MariaStan\Schema\DbType\DbType;

final class Column
{
	public function __construct(public string $name, public DbType $type, public bool $isNullable)
	{
	}
}
