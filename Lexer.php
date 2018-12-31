<?php
/*************
 * L E X E R *
 *************/


Class Token {
	public $type,$value;

	/**
	 * Constructor, receives the Type of a token and it's value
	 */
	public function __construct(int $type, $value) {
		$refl = new ReflectionClass(self::class);
		if (in_array($type, $refl->getConstants())) {
			$this->type = $type;
			$this->value = $value;
		} else {
			throw new Exception("The Token type '$type' is not valid!");
		}
	}

	/**
	 * Returns token name based on ID, if $code is an array os IDs returns an 
	 * array like string representing all token names. It is usefull for error
	 * displaying.
	 */
	public static function getTokenName($id) {
		if (is_integer($id)) {
			$refl = new ReflectionClass(self::class);
			$ids = array_flip($refl->getConstants());
			return $ids[$id];
		} else if (is_array($id)) {
			$txt = '[';
			$refl = new ReflectionClass(self::class);
			$ids = array_flip($refl->getConstants());
			foreach ($id as $c) $txt .= $ids[$c] . ', ';
			$txt = substr($txt, 0, -2);
			$txt .= ']';
			return $txt;
		}
	}

	/**
	 * Returns the ID of a token based on the name of the token.
	 */
	public static function getTokenId($name) {
		$refl = new ReflectionClass(self::class);
		$names = $refl->getConstants();
		return $names[$name];
	}

	/**
	 * Create a text representation of a Token
	 */
	public function __toString() {
		$refl = new ReflectionClass(self::class);
		$type = @array_flip($refl->getConstants());
		$type = $type[$this->type];
		$value = $this->value;
		return "Token($type, $value)";
	}

	const EOF = 0;
	const DOT = 1;
	const BEGIN = 2;
	const END = 3;
	const SEMI = 4; 
	const ASSIGN = 5; 
	const PLUS = 6; 
	const MINUS = 7; 
	const MUL = 8; 
	const DIV = 9; 
	const INTEGER = 10;
	const LPAREN = 11; 
	const RPAREN = 12; 
	const ID = 13;

	const KEYWORDS = [
		'BEGIN',
		'END',
		'DIV'
	];
}

class Lexer {
	private $code;
	private $pos;
	private $currentChar;

	public function __construct(string $code) {
		$this->code = $code;
		$this->pos = 0;
		$this->currentChar = substr($this->code, $this->pos, 1);
	}

	public function error() {
		throw new Exception("Invalid character '" . ($this->currentChar). "' at position " . ($this->pos+1) . "!");
	}

	public function advance() {
		$this->pos++;
		if($this->pos > strlen($this->code) -1) {
			$this->currentChar = null;
		} else {
			$this->currentChar = substr($this->code, $this->pos, 1);
		}
	}

	public function peek() {
		return substr($this->code, $this->pos+1, 1);
	}

	public function _id() {
		$identifier = "";
		while ($this->currentChar != null and ( ctype_alnum($this->currentChar) OR $this->currentChar == '_' ) ) {
			$identifier .= $this->currentChar;
			$this->advance();
		}

		// This line make all the interpreter case-insensitive
		$identifier = strtoupper($identifier);

		if (in_array($identifier, Token::KEYWORDS)) {
			return new Token(Token::getTokenId($identifier), $identifier);
		} else {
			return new Token(Token::ID, $identifier);
		}

	}

	public function getNextToken() {
		while ($this->currentChar !== null ) {
			// Skip whitespaces and tabs
			if (ctype_space($this->currentChar)) {
				$this->advance();
				continue;
			}			

			// Get INTEGER tokens
			if (ctype_digit($this->currentChar)) {
				$int = '';
				while (ctype_digit($this->currentChar) and $this->currentChar !== null) {
					$int .= $this->currentChar;
					$this->advance();
				}
				return new Token(Token::INTEGER, $int);
			}

			// Identifiers or reserved keywords
			if (ctype_alpha($this->currentChar) OR $this->currentChar == '_') {
				return $this->_id();
			}

			if ($this->currentChar == ':' and $this->peek() == '=') {
				$this->advance();
				$this->advance();
				return new Token(Token::ASSIGN, ':=');
			}

			// Single char Tokens
			switch ($this->currentChar) {
				case '.': $this->advance(); return new Token(Token::DOT, '.'); break;
				case ';': $this->advance(); return new Token(Token::SEMI, ';'); break;
				case '+': $this->advance(); return new Token(Token::PLUS, '+'); break;
				case '-': $this->advance(); return new Token(Token::MINUS, '-'); break;
				case '*': $this->advance(); return new Token(Token::MUL, '*'); break;
				
				// This is not integer division (disabled for now)!
				//case '/': $this->advance(); return new Token(Token::DIV, '/'); break;				
				case '(': $this->advance(); return new Token(Token::LPAREN, '('); break;
				case ')': $this->advance(); return new Token(Token::RPAREN, ')'); break;
			}

			$this->error();

		}

		return new Token(Token::EOF, null);
	}
}