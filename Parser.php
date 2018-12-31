<?php
/***************
 * P A R S E R *
 ***************/

class AST {

	public function __construct() {
		$this->id = "ID_".uniqid();
	}

}

class BinOp extends AST {
	public function __construct($left, $operator, $right) {
		parent::__construct();
		$this->left = $left;
		$this->operator = $operator;
		$this->right = $right;
	}
}

class UnaryOp extends AST {
	public function __construct($operator, $right) {
		parent::__construct();
		$this->operator = $operator;
		$this->right = $right;
	}
}

class Num extends AST {
	public function __construct($token) {
		parent::__construct();
		$this->token = $token;
		$this->value = $token->value;
	}
}

class Compound extends AST {
	public function __construct() {
		parent::__construct();
		$this->children = [];
	}
}

class Assign extends AST {
	public function __construct($left, $operator, $right) {
		parent::__construct();
		$this->left = $left;
		$this->operator = $operator;
		$this->right = $right;
	}
}

class Variable extends AST {
	public function __construct($token) {
		parent::__construct();
		$this->token = $token;
		$this->value = $token->value;
	}
}

class NoOp extends AST {
}


class Parser {
	public static $debug = false;
	public function __construct(Lexer $lexer) {
		$this->lexer = $lexer;
		$this->currentToken = $this->lexer->getNextToken();
	}

	public function eat($type) {

		if (is_integer($type)) {
			if ($this->currentToken->type == $type) {
				if(self::$debug) echo $this->currentToken. PHP_EOL;
				$this->currentToken = $this->lexer->getNextToken();	
			} else {
				throw new Exception('SINTAX ERROR: Expected ' . Token::getTokenName($type) . ' but ' . Token::getTokenName($this->currentToken->type) . ' found!');
			}
		} else if (is_array($type)) {

			if(in_array($this->currentToken->type, $type)) {
				if(self::$debug) echo $this->currentToken. PHP_EOL;
				$this->currentToken = $this->lexer->getNextToken();	
			} else {
				throw new Exception('SINTAX ERROR: Expected ' . Token::getTokenName($type) . ' but ' . Token::getTokenName($this->currentToken->type) . ' found!');
			}

		} else {
			throw new Exception("The method ".__METHOD__." needs to receive an integer or array as argument!");
		}

	}

	public function error($type) {
		throw new Exception('SINTAX ERROR: Expected ' . Token::getTokenName($type) . ' but ' . Token::getTokenName($this->currentToken->type) . ' found!');
	}



	// program : compound_statement DOT
	public function program() {
		$node = $this->compound_statement();
		$this->eat(Token::DOT);
		return $node;
	}

	// compound_statement : BEGIN statement_list END
	public function compound_statement() {
		$this->eat(Token::BEGIN);
		$nodes = $this->statement_list();
		$this->eat(Token::END);

		$root = new Compound();
		foreach ($nodes as $node) {
			$root->children[] = $node;
		}

		return $root;
	}

	// statement_list : statement
	//                | statement SEMI statement_list
	public function statement_list() {
		$node = $this->statement();
		$results = [$node];
		while ($this->currentToken->type == Token::SEMI) {
			$this->eat(Token::SEMI);
			$results[] = $this->statement();
		}

		if ($this->currentToken->type == Token::ID) {
			throw new Exception("Unexpected token ID!");
		}

		return $results;
	}

	// statement : compound_statement
	//           | assignment_statement
	//           | empty
	public function statement() {
		if ($this->currentToken->type == Token::BEGIN) {
			$node = $this->compound_statement();
		} elseif ($this->currentToken->type == Token::ID) {
			$node = $this->assignment_statement();
		} else {
			$node = $this->empty();
		}

		return $node;
	}

	// assignment_statement : variable ASSIGN expr
	public function assignment_statement() {
		$left = $this->variable();
		$token = $this->currentToken;
		$this->eat(Token::ASSIGN);
		$right = $this->expr();
		$node = new Assign($left, $token, $right);
		return $node;
	}

	// empty :
	public function empty() {
		return new NoOp();
	}

	// expr: term ((SUM|SUB) term)*
	public function expr() {
		$node = $this->term();
		while (in_array($this->currentToken->type, [Token::PLUS, Token::MINUS])) {
			$op = $this->currentToken;
			if ($this->currentToken->type == Token::PLUS)
				$this->eat(Token::PLUS);
			elseif ($this->currentToken->type == Token::MINUS) 
				$this->eat(Token::MINUS);
			
			$node = new BinOp($node, $op, $this->term());
		}
		return $node;
	}

	// term: factor ((MUL | DIV | FDIV) factor)*
	public function term() {
		$node = $this->factor();
		while (in_array($this->currentToken->type, [Token::MUL, Token::DIV, Token::FDIV])) {
			$op = $this->currentToken;
			if ($this->currentToken->type == Token::MUL)
				$this->eat(Token::MUL);
			elseif ($this->currentToken->type == Token::DIV) 
				$this->eat(Token::DIV);
			elseif ($this->currentToken->type == Token::FDIV) 
				$this->eat(Token::FDIV);

			$node = new BinOp($node, $op, $this->factor());
		}
		return $node;
	}


	// factor : PLUS factor
	//        | MINUS factor
	//        | INTEGER
	//        | LPAREN expr RPAREN
	//        | variable	
	public function factor() {
		$token = $this->currentToken;
		if ( in_array($token->type, [Token::PLUS, Token::MINUS])) {
			$this->eat([Token::PLUS, Token::MINUS]);
			return new UnaryOp($token, $this->factor());

		} elseif ($token->type == Token::INTEGER) {
			$this->eat(Token::INTEGER);
			return new Num($token);
		
		} elseif ($token->type == Token::LPAREN) {
			$this->eat(Token::LPAREN);
			$node = $this->expr();
			$this->eat(Token::RPAREN);
			return $node;
		} else {
			$node = $this->variable();
			return $node;
		}

	}

	// variable: ID
	public function variable() {
		$node = new Variable($this->currentToken);
		$this->eat(Token::ID);
		return $node;
	}

	public function parse() {
		$node = $this->program();
		if ($this->currentToken->type != Token::EOF) {
			$this->error(Token::EOF);
		}
		return $node;
	}

}
