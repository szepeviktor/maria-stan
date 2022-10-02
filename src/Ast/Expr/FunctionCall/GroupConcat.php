<?php

declare(strict_types=1);

namespace MariaStan\Ast\Expr\FunctionCall;

use MariaStan\Ast\Expr\Expr;
use MariaStan\Ast\ExprWithDirection;
use MariaStan\Ast\Limit;
use MariaStan\Ast\OrderBy;
use MariaStan\Parser\Position;

use function array_filter;
use function array_map;
use function array_merge;

final class GroupConcat extends BaseFunctionCall
{
	/** @param non-empty-array<Expr> $expressions */
	public function __construct(
		Position $startPosition,
		Position $endPosition,
		public readonly array $expressions,
		public readonly ?OrderBy $orderBy = null,
		public readonly string $separator = ',',
		public readonly ?Limit $limit = null,
		public readonly bool $isDistinct = false,
	) {
		parent::__construct($startPosition, $endPosition);
	}

	public function getFunctionName(): string
	{
		return 'GROUP_CONCAT';
	}

	/** @inheritDoc */
	public function getArguments(): array
	{
		return array_merge(
			$this->expressions,
			array_map(
				static fn (ExprWithDirection $e) => $e->expr,
				$this->orderBy?->expressions ?? [],
			),
			array_filter([
				$this->limit?->count,
				$this->limit?->offset,
			]),
		);
	}

	public static function getFunctionCallType(): FunctionCallTypeEnum
	{
		return FunctionCallTypeEnum::GROUP_CONCAT;
	}
}
