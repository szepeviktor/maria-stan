<?php

declare(strict_types=1);

namespace MariaStan\Parser;

use MariaStan\Ast\Expr\Column;
use MariaStan\Ast\Expr\ExprTypeEnum;
use MariaStan\Ast\Query\SelectQuery;
use MariaStan\Ast\SelectExpr\RegularExpr;
use MariaStan\Ast\SelectExpr\SelectExpr;
use MariaStan\DatabaseTestCaseHelper;
use MariaStan\Parser\Exception\ParserException;
use mysqli_sql_exception;
use PHPUnit\Framework\TestCase;

use function assert;

// phpcs:disable SlevomatCodingStandard.Exceptions.RequireNonCapturingCatch.NonCapturingCatchRequired
class MariaDbParserKeywordAsIdentifierTest extends TestCase
{
	/** @return iterable<string, array<mixed>> name => args */
	public function provideTestFieldAliasData(): iterable
	{
		foreach (TokenTypeEnum::cases() as $tokenType) {
			yield "field alias - {$tokenType->value}" => [
				'select' => "SELECT 1 {$tokenType->value}",
			];
		}
	}

	/** @dataProvider provideTestFieldAliasData */
	public function testFieldAlias(string $select): void
	{
		$parserResult = null;
		$dbField = null;
		$dbException = null;
		$parserException = null;
		$parser = new MariaDbParser();

		try {
			$dbField = $this->getFieldFromSql($select);
		} catch (mysqli_sql_exception $dbException) {
		}

		try {
			$parserResult = $parser->parseSingleQuery($select);
		} catch (ParserException $parserException) {
		}

		if ($dbException === null && $parserException !== null) {
			$this->fail("DB accepts the query, but parser fails with: {$parserException->getMessage()}");
		}

		if ($dbException !== null && $parserException === null) {
			$this->fail("Parser accepts the query, even though DB fails with: {$dbException->getMessage()}");
		}

		if ($dbException !== null) {
			// Make phpunit happy.
			$this->assertNotNull($parserException);

			return;
		}

		$this->assertNotNull($dbField);
		$this->assertNotNull($parserResult);
		$this->assertInstanceOf(SelectQuery::class, $parserResult);
		$this->assertCount(1, $parserResult->select);
		$this->assertSame($dbField->name, $this->getNameFromSelectExpr($select, $parserResult->select[0]));
	}

	/** @throws mysqli_sql_exception */
	private function getFieldFromSql(string $select): object
	{
		$db = DatabaseTestCaseHelper::getDefaultSharedConnection();
		$stmt = $db->query($select);
		$field = $stmt->fetch_field();
		$stmt->close();

		if ($field === false) {
			$this->fail('Failed to fetch field from: ' . $select);
		}

		return $field;
	}

	private function getNameFromSelectExpr(string $query, SelectExpr $selectExpr): string
	{
		$this->assertInstanceOf(RegularExpr::class, $selectExpr);

		if ($selectExpr->alias !== null) {
			return $selectExpr->alias;
		}

		$expr = $selectExpr->expr;

		switch ($expr::getExprType()) {
			case ExprTypeEnum::COLUMN:
				assert($expr instanceof Column);

				return $expr->name;
			default:
				return $expr->getStartPosition()->findSubstringToEndPosition($query, $expr->getEndPosition());
		}
	}
}
