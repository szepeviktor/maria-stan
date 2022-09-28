<?php

declare(strict_types=1);

namespace MariaStan\Ast\Expr\FunctionCall;

enum FunctionCallTypeEnum: string
{
	/** @see StandardFunctionCall */
	case STANDARD = 'STANDARD';

	/** @see Window */
	case WINDOW = 'WINDOW';

	/** @see Count */
	case COUNT = 'COUNT';

	/** @see Cast */
	case CAST = 'CAST';
}
