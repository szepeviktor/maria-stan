<?php

declare(strict_types=1);

namespace MariaStan\Parser;

use MariaStan\Ast\Expr\BinaryOp;
use MariaStan\Ast\Expr\BinaryOpTypeEnum;
use MariaStan\Ast\Expr\Expr;
use MariaStan\Ast\Expr\ExprTypeEnum;
use MariaStan\Ast\Expr\LiteralInt;
use MariaStan\Ast\Expr\UnaryOp;
use MariaStan\Ast\Expr\UnaryOpTypeEnum;
use MariaStan\Ast\Query\SelectQuery;
use MariaStan\Ast\SelectExpr\RegularExpr;
use MariaStan\DatabaseTestCase;
use RuntimeException;

use function assert;

class MariaDbParserOperatorTest extends DatabaseTestCase
{
	/** @return iterable<string, array<mixed>> */
	public function provideTestData(): iterable
	{
		$expressions = [
			'1 + 2 * 3',
			'!1 + 2',
			'+2 * -3',
			'10 DIV 2 + 1 MOD -2 * -1',
			'0 AND 1 OR 1',
			'0 XOR 0 OR 1',
			'0 & 1 | 1 ^ 0',
			'NOT 1 - 1',
			'!1 - 1',
		];

		foreach ($expressions as $expr) {
			yield $expr => [
				'select' => "SELECT {$expr}",
			];
		}
	}

	/** @dataProvider provideTestData */
	public function test(string $select): void
	{
		$dbValue = $this->getValueFromSql($select);
		$parser = new MariaDbParser();
		$selectQuery = $parser->parseSingleQuery($select);
		$this->assertInstanceOf(SelectQuery::class, $selectQuery);
		$this->assertCount(1, $selectQuery->select);
		$firstSelect = $selectQuery->select[0];
		$this->assertInstanceOf(RegularExpr::class, $firstSelect);
		$astValue = $this->getValueFromAstExpression($firstSelect->expr);
		$this->assertSame($dbValue, $astValue);
	}

	private function getValueFromSql(string $select): mixed
	{
		$db = $this->getDefaultSharedConnection();
		$stmt = $db->query($select);
		$val = $stmt->fetch_column(0);
		$stmt->close();

		return $val;
	}

	private function getValueFromAstExpression(Expr $expr): mixed
	{
		switch ($expr::getExprType()) {
			case ExprTypeEnum::BINARY_OP:
				assert($expr instanceof BinaryOp);
				$left = $this->getValueFromAstExpression($expr->left);
				$right = $this->getValueFromAstExpression($expr->right);

				return match ($expr->operation) {
					BinaryOpTypeEnum::PLUS => $left + $right,
					BinaryOpTypeEnum::MINUS => $left - $right,
					BinaryOpTypeEnum::DIVISION => $left / $right,
					BinaryOpTypeEnum::INT_DIVISION => (int) ($left / $right),
					BinaryOpTypeEnum::MULTIPLICATION => $left * $right,
					BinaryOpTypeEnum::MODULO => $left % $right,
					BinaryOpTypeEnum::LOGIC_AND => $left && $right ? 1 : 0,
					BinaryOpTypeEnum::LOGIC_OR => $left || $right ? 1 : 0,
					BinaryOpTypeEnum::LOGIC_XOR => $left xor $right ? 1 : 0,
					BinaryOpTypeEnum::BITWISE_OR => ((int) $left) | ((int) $right),
					BinaryOpTypeEnum::BITWISE_AND => ((int) $left) & ((int) $right),
					BinaryOpTypeEnum::BITWISE_XOR => ((int) $left) ^ ((int) $right),
					BinaryOpTypeEnum::SHIFT_LEFT => ((int) $left) << ((int) $right),
					BinaryOpTypeEnum::SHIFT_RIGHT => ((int) $left) >> ((int) $right),
					default => throw new RuntimeException("{$expr->operation->value} is not implemented yet."),
				};
			case ExprTypeEnum::UNARY_OP:
				assert($expr instanceof UnaryOp);
				$inner = $this->getValueFromAstExpression($expr->expr);

				return match ($expr->operation) {
					UnaryOpTypeEnum::PLUS => $inner,
					UnaryOpTypeEnum::MINUS => (-1) * $inner,
					UnaryOpTypeEnum::LOGIC_NOT => $inner ? 0 : 1,
					UnaryOpTypeEnum::BITWISE_NOT => ~ ((int) $inner),
					default => throw new RuntimeException("{$expr->operation->value} is not implemented yet."),
				};
			case ExprTypeEnum::LITERAL_INT:
				assert($expr instanceof LiteralInt);

				return $expr->value;
			default:
				$this->fail("Unexpected expression type {$expr::getExprType()->value}");
		}
	}
}
