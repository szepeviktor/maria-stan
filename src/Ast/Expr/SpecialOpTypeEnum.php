<?php

declare(strict_types=1);

namespace MariaStan\Ast\Expr;

enum SpecialOpTypeEnum: string
{
	/** @see Between */
	case BETWEEN = 'BETWEEN';
}
