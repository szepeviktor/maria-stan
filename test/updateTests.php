<?php

declare(strict_types=1);

namespace MariaStan;

use MariaStan\Parser\CodeTestParser;
use MariaStan\Parser\MariaDbParsingTest;

use function array_fill_keys;
use function explode;
use function file_put_contents;
use function strpos;

require __DIR__ . '/bootstrap.php';
require __DIR__ . '/Parser/CodeTestParser.php';
require __DIR__ . '/Parser/MariaDbParsingTest.php';

$dir = __DIR__ . '/code/Parser/MariaDbParser';

$testParser = new CodeTestParser();
$codeParsingTest = new MariaDbParsingTest();

foreach (filesInDir($dir, 'test') as $fileName => $code) {
	if (strpos($code, '@@{') !== false) {
		// Skip tests with evaluate segments
		continue;
	}

	[$name, $tests] = $testParser->parseTest($code, 2);
	$newTests = [];

	foreach ($tests as [$modeLine, [$input, $expected]]) {
		$modes = $modeLine !== null
			? array_fill_keys(explode(',', $modeLine), true)
			: [];
		$parser = $codeParsingTest->createParser();
		[, $output] = $codeParsingTest->getParseOutput($parser, $input, $modes);
		$newTests[] = [$modeLine, [$input, $output]];
	}

	$newCode = $testParser->reconstructTest($name, $newTests);
	file_put_contents($fileName, $newCode);
}

/**
 * Based on https://github.com/nikic/PHP-Parser/blob/5aae65e627f8f3cdf6b109dc6a2bddbd92e0867a/test/updateTests.php
 *
 * Original license:
 * BSD 3-Clause License
 *
 * Copyright (c) 2011, Nikita Popov
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 * 1. Redistributions of source code must retain the above copyright notice, this
 * list of conditions and the following disclaimer.
 *
 * 2. Redistributions in binary form must reproduce the above copyright notice,
 * this list of conditions and the following disclaimer in the documentation
 * and/or other materials provided with the distribution.
 *
 * 3. Neither the name of the copyright holder nor the names of its
 * contributors may be used to endorse or promote products derived from
 * this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE
 * FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
 * DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
 * SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY,
 * OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */
