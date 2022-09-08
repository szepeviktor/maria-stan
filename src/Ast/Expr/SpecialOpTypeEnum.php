<?php

declare(strict_types=1);

namespace MariaStan\Ast\Expr;

enum SpecialOpTypeEnum: string
{
	/** @see Between */
	case BETWEEN = 'BETWEEN';

	/** @see Is */
	case IS = 'IS';

	/** @see In */
	case IN = 'IN';

	/** @see Interval */
	case INTERVAL = 'INTERVAL';

	/** @see Like */
	case LIKE = 'LIKE';
}
