<?php

declare(strict_types=1);

namespace MariaStan\Parser;

use function array_combine;
use function array_map;
use function array_search;
use function array_slice;
use function assert;

enum TokenTypeEnum: string
{
	case SINGLE_CHAR = 'SINGLE_CHAR';
	case IDENTIFIER = 'IDENTIFIER';

	case LITERAL_STRING = 'STRING_LITERAL';
	case LITERAL_INT = 'INT_LITERAL';
	case LITERAL_FLOAT = 'FLOAT_LITERAL';
	case LITERAL_BIN = 'BIN_LITERAL';
	case LITERAL_HEX = 'HEX_LITERAL';

	case OP_COLON_ASSIGN = ':=';
	case OP_SHIFT_LEFT = '<<';
	case OP_SHIFT_RIGHT = '>>';

	// also covers <>
	case OP_NE = '!=';
	case OP_LTE = '<=';
	case OP_GTE = '>=';
	case OP_NULL_SAFE = '<=>';
	case OP_LOGIC_AND = '&&';
	case OP_LOGIC_OR = '||';

	// Everything after the END_OF_INPUT case needs to be a keyword.
	case END_OF_INPUT = 'END_OF_INPUT';

	// https://mariadb.com/kb/en/reserved-words/
	case ACCESSIBLE = 'ACCESSIBLE';
	case ACTION = 'ACTION';
	case ADD = 'ADD';
	case ALL = 'ALL';
	case ALTER = 'ALTER';
	case ANALYZE = 'ANALYZE';
	case AND = 'AND';
	case AS = 'AS';
	case ASC = 'ASC';
	case ASENSITIVE = 'ASENSITIVE';
	case BEFORE = 'BEFORE';
	case BETWEEN = 'BETWEEN';
	case BIGINT = 'BIGINT';
	case BINARY = 'BINARY';
	case BIT = 'BIT';
	case BLOB = 'BLOB';
	case BOTH = 'BOTH';
	case BY = 'BY';
	case CALL = 'CALL';
	case CASCADE = 'CASCADE';
	case CASE = 'CASE';
	case CHANGE = 'CHANGE';
	case CHAR = 'CHAR';
	case CHARACTER = 'CHARACTER';
	case CHECK = 'CHECK';
	case COLLATE = 'COLLATE';
	case COLUMN = 'COLUMN';
	case CONDITION = 'CONDITION';
	case CONSTRAINT = 'CONSTRAINT';
	case CONTINUE = 'CONTINUE';
	case CONVERT = 'CONVERT';
	case CREATE = 'CREATE';
	case CROSS = 'CROSS';
	case CURRENT = 'CURRENT';
	case CURRENT_DATE = 'CURRENT_DATE';
	case CURRENT_ROLE = 'CURRENT_ROLE';
	case CURRENT_TIME = 'CURRENT_TIME';
	case CURRENT_TIMESTAMP = 'CURRENT_TIMESTAMP';
	case CURRENT_USER = 'CURRENT_USER';
	case CURSOR = 'CURSOR';
	case CYCLE = 'CYCLE';
	case DATABASE = 'DATABASE';
	case DATABASES = 'DATABASES';
	case DATE = 'DATE';
	case DATETIME = 'DATETIME';
	case DAY_HOUR = 'DAY_HOUR';
	case DAY_MICROSECOND = 'DAY_MICROSECOND';
	case DAY_MINUTE = 'DAY_MINUTE';
	case DAY_SECOND = 'DAY_SECOND';
	case DEC = 'DEC';
	case DECIMAL = 'DECIMAL';
	case DECLARE = 'DECLARE';
	case DEFAULT = 'DEFAULT';
	case DELAYED = 'DELAYED';
	case DELETE = 'DELETE';
	case DELETE_DOMAIN_ID = 'DELETE_DOMAIN_ID';
	case DESC = 'DESC';
	case DESCRIBE = 'DESCRIBE';
	case DETERMINISTIC = 'DETERMINISTIC';
	case DISTINCT = 'DISTINCT';
	case DISTINCTROW = 'DISTINCTROW';
	case DIV = 'DIV';
	case DO_DOMAIN_IDS = 'DO_DOMAIN_IDS';
	case DOUBLE = 'DOUBLE';
	case DROP = 'DROP';
	case DUAL = 'DUAL';
	case DUPLICATE = 'DUPLICATE';
	case EACH = 'EACH';
	case ELSE = 'ELSE';
	case ELSEIF = 'ELSEIF';
	case ENCLOSED = 'ENCLOSED';
	case END = 'END';
	case ENUM = 'ENUM';
	case ESCAPE = 'ESCAPE';
	case ESCAPED = 'ESCAPED';
	case EXCEPT = 'EXCEPT';
	case EXISTS = 'EXISTS';
	case EXIT = 'EXIT';
	case EXPLAIN = 'EXPLAIN';
	case FALSE = 'FALSE';
	case FETCH = 'FETCH';
	case FLOAT = 'FLOAT';
	case FLOAT4 = 'FLOAT4';
	case FLOAT8 = 'FLOAT8';
	case FOLLOWING = 'FOLLOWING';
	case FOR = 'FOR';
	case FORCE = 'FORCE';
	case FOREIGN = 'FOREIGN';
	case FROM = 'FROM';
	case FULLTEXT = 'FULLTEXT';
	case GENERAL = 'GENERAL';
	case GRANT = 'GRANT';
	case GROUP = 'GROUP';
	case HAVING = 'HAVING';
	case HIGH_PRIORITY = 'HIGH_PRIORITY';
	case HOUR_MICROSECOND = 'HOUR_MICROSECOND';
	case HOUR_MINUTE = 'HOUR_MINUTE';
	case HOUR_SECOND = 'HOUR_SECOND';
	case IF = 'IF';
	case IGNORE = 'IGNORE';
	case IGNORE_DOMAIN_IDS = 'IGNORE_DOMAIN_IDS';
	case IGNORE_SERVER_IDS = 'IGNORE_SERVER_IDS';
	case IN = 'IN';
	case INDEX = 'INDEX';
	case INFILE = 'INFILE';
	case INNER = 'INNER';
	case INOUT = 'INOUT';
	case INSENSITIVE = 'INSENSITIVE';
	case INSERT = 'INSERT';
	case INT = 'INT';
	case INT1 = 'INT1';
	case INT2 = 'INT2';
	case INT3 = 'INT3';
	case INT4 = 'INT4';
	case INT8 = 'INT8';
	case INTEGER = 'INTEGER';
	case INTERSECT = 'INTERSECT';
	case INTERVAL = 'INTERVAL';
	case INTO = 'INTO';
	case IS = 'IS';
	case ITERATE = 'ITERATE';
	case JOIN = 'JOIN';
	case KEY = 'KEY';
	case KEYS = 'KEYS';
	case KILL = 'KILL';
	case LEADING = 'LEADING';
	case LEAVE = 'LEAVE';
	case LEFT = 'LEFT';
	case LIKE = 'LIKE';
	case LIMIT = 'LIMIT';
	case LINEAR = 'LINEAR';
	case LINES = 'LINES';
	case LOAD = 'LOAD';
	case LOCALTIME = 'LOCALTIME';
	case LOCALTIMESTAMP = 'LOCALTIMESTAMP';
	case LOCK = 'LOCK';
	case LOCKED = 'LOCKED';
	case LONG = 'LONG';
	case LONGBLOB = 'LONGBLOB';
	case LONGTEXT = 'LONGTEXT';
	case LOOP = 'LOOP';
	case LOW_PRIORITY = 'LOW_PRIORITY';
	case MASTER_HEARTBEAT_PERIOD = 'MASTER_HEARTBEAT_PERIOD';
	case MASTER_SSL_VERIFY_SERVER_CERT = 'MASTER_SSL_VERIFY_SERVER_CERT';
	case MATCH = 'MATCH';
	case MAXVALUE = 'MAXVALUE';
	case MEDIUMBLOB = 'MEDIUMBLOB';
	case MEDIUMINT = 'MEDIUMINT';
	case MEDIUMTEXT = 'MEDIUMTEXT';
	case MIDDLEINT = 'MIDDLEINT';
	case MINUTE_MICROSECOND = 'MINUTE_MICROSECOND';
	case MINUTE_SECOND = 'MINUTE_SECOND';
	case MOD = 'MOD';
	case MODE = 'MODE';
	case MODIFIES = 'MODIFIES';
	case NATURAL = 'NATURAL';
	case NO = 'NO';
	case NOT = 'NOT';
	case NOWAIT = 'NOWAIT';
	case NO_WRITE_TO_BINLOG = 'NO_WRITE_TO_BINLOG';
	case NULL = 'NULL';
	case NUMERIC = 'NUMERIC';
	case OFFSET = 'OFFSET';
	case ON = 'ON';
	case OPTIMIZE = 'OPTIMIZE';
	case OPTION = 'OPTION';
	case OPTIONALLY = 'OPTIONALLY';
	case OR = 'OR';
	case ORDER = 'ORDER';
	case OUT = 'OUT';
	case OUTER = 'OUTER';
	case OUTFILE = 'OUTFILE';
	case OVER = 'OVER';
	case PAGE_CHECKSUM = 'PAGE_CHECKSUM';
	case PARSE_VCOL_EXPR = 'PARSE_VCOL_EXPR';
	case PARTITION = 'PARTITION';
	case POSITION = 'POSITION';
	case PRECEDING = 'PRECEDING';
	case PRECISION = 'PRECISION';
	case PRIMARY = 'PRIMARY';
	case PROCEDURE = 'PROCEDURE';
	case PURGE = 'PURGE';
	case RANGE = 'RANGE';
	case READ = 'READ';
	case READS = 'READS';
	case READ_WRITE = 'READ_WRITE';
	case REAL = 'REAL';
	case RECURSIVE = 'RECURSIVE';
	case REF_SYSTEM_ID = 'REF_SYSTEM_ID';
	case REFERENCES = 'REFERENCES';
	case REGEXP = 'REGEXP';
	case RELEASE = 'RELEASE';
	case RENAME = 'RENAME';
	case REPEAT = 'REPEAT';
	case REPLACE = 'REPLACE';
	case REQUIRE = 'REQUIRE';
	case RESIGNAL = 'RESIGNAL';
	case RESTRICT = 'RESTRICT';
	case RETURN = 'RETURN';
	case RETURNING = 'RETURNING';
	case REVOKE = 'REVOKE';
	case RIGHT = 'RIGHT';
	case RLIKE = 'RLIKE';
	case ROLLUP = 'ROLLUP';
	case ROW = 'ROW';
	case ROWS = 'ROWS';
	case SCHEMA = 'SCHEMA';
	case SCHEMAS = 'SCHEMAS';
	case SECOND_MICROSECOND = 'SECOND_MICROSECOND';
	case SELECT = 'SELECT';
	case SENSITIVE = 'SENSITIVE';
	case SEPARATOR = 'SEPARATOR';
	case SET = 'SET';
	case SHARE = 'SHARE';
	case SHOW = 'SHOW';
	case SIGNAL = 'SIGNAL';
	case SIGNED = 'SIGNED';
	case SKIP = 'SKIP';
	case SLOW = 'SLOW';
	case SMALLINT = 'SMALLINT';
	case SPATIAL = 'SPATIAL';
	case SPECIFIC = 'SPECIFIC';
	case SQL = 'SQL';
	case SQLEXCEPTION = 'SQLEXCEPTION';
	case SQLSTATE = 'SQLSTATE';
	case SQLWARNING = 'SQLWARNING';
	case SQL_BIG_RESULT = 'SQL_BIG_RESULT';
	case SQL_CALC_FOUND_ROWS = 'SQL_CALC_FOUND_ROWS';
	case SQL_SMALL_RESULT = 'SQL_SMALL_RESULT';
	case SSL = 'SSL';
	case STARTING = 'STARTING';
	case STATS_AUTO_RECALC = 'STATS_AUTO_RECALC';
	case STATS_PERSISTENT = 'STATS_PERSISTENT';
	case STATS_SAMPLE_PAGES = 'STATS_SAMPLE_PAGES';
	case STRAIGHT_JOIN = 'STRAIGHT_JOIN';
	case TABLE = 'TABLE';
	case TERMINATED = 'TERMINATED';
	case TEXT = 'TEXT';
	case THEN = 'THEN';
	case TIME = 'TIME';
	case TIMESTAMP = 'TIMESTAMP';
	case TINYBLOB = 'TINYBLOB';
	case TINYINT = 'TINYINT';
	case TINYTEXT = 'TINYTEXT';
	case TO = 'TO';
	case TRAILING = 'TRAILING';
	case TRIGGER = 'TRIGGER';
	case TRUE = 'TRUE';
	case TRUNCATE = 'TRUNCATE';
	case UNBOUNDED = 'UNBOUNDED';
	case UNDO = 'UNDO';
	case UNION = 'UNION';
	case UNIQUE = 'UNIQUE';
	case UNKNOWN = 'UNKNOWN';
	case UNLOCK = 'UNLOCK';
	case UNSIGNED = 'UNSIGNED';
	case UPDATE = 'UPDATE';
	case USAGE = 'USAGE';
	case USE = 'USE';
	case USING = 'USING';
	case UTC_DATE = 'UTC_DATE';
	case UTC_TIME = 'UTC_TIME';
	case UTC_TIMESTAMP = 'UTC_TIMESTAMP';
	case VALUE = 'VALUE';
	case VALUES = 'VALUES';
	case VARBINARY = 'VARBINARY';
	case VARCHAR = 'VARCHAR';
	case VARCHARACTER = 'VARCHARACTER';
	case VARYING = 'VARYING';
	case WAIT = 'WAIT';
	case WHEN = 'WHEN';
	case WHERE = 'WHERE';
	case WHILE = 'WHILE';
	case WINDOW = 'WINDOW';
	case WITH = 'WITH';
	case WRITE = 'WRITE';
	case XOR = 'XOR';
	case YEAR_MONTH = 'YEAR_MONTH';
	case ZEROFILL = 'ZEROFILL';

	/** @return array<value-of<self>, self> upper-case keyword => enum type */
	public static function getKeywordsMap(): array
	{
		static $result = null;

		if ($result === null) {
			/** @phpstan-var array<int, TokenTypeEnum> $result Analysis is needlessly slow without this. */
			$result = self::cases();
			$eoiIdx = array_search(self::END_OF_INPUT, $result, true);
			assert($eoiIdx !== false);
			$result = array_slice($result, $eoiIdx + 1);
			$result = array_combine(
				array_map(static fn (self $e) => $e->value, $result),
				$result,
			);
		}

		return $result;
	}
}
