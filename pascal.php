#!/usr/bin/php
<?php

require "Lexer.php";
require "Parser.php";
require "Interpreter.php";
require "DotTranslator.php";

// used to text the Lexer
if (in_array('--tokenize', $argv)) {
	$sourceFile = array_pop($argv);
	$code = file_get_contents($sourceFile);
	echo $code.PHP_EOL.PHP_EOL;
	$lexer = new Lexer($code);
	do {
		$token = $lexer->getNextToken();
		echo "$token\n";
	} while ($token->type != Token::EOF);

	exit;
}


// Used to test the parser
elseif (in_array('--dot', $argv)) {
	$sourceFile = array_pop($argv);
	$code = file_get_contents($sourceFile);
	$lexer = new Lexer($code);
	$parser = new Parser($lexer);
	$translator = new DotTranslator($parser);
	$translator->translate();
	exit;	
}

// Interpret the code, the source needs to be the last options always.
else {
	$sourceFile = array_pop($argv);
	try {
		$code = file_get_contents($sourceFile);
		$lexer = new Lexer($code);
		$parser = new Parser($lexer);
		$interpreter = new Interpreter($parser);
		$interpreter->interpret();
		echo "Variables at end of execution:\n";
		echo "==============================\n";
		
		// Mede o maior nome de variável
		$length = 0; foreach ($interpreter->GLOBAL_SCOPE as $var => $val) if(strlen($var) > $length) $length = strlen($var);
		
		foreach ($interpreter->GLOBAL_SCOPE as $variable => $value) {
			$variable = str_pad($variable, $length, ' ', STR_PAD_LEFT);
			echo "$variable = $value\n";
		}
		
	} catch (Exception $e) {
		echo $e->getMessage().PHP_EOL;
	}
}