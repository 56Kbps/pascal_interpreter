<?php
/*************************
 * I N T E R P R E T E R *
 *************************/


trait NodeVisitor {
	public function visit($node) {
		$method = 'visit_'.get_class($node);
		return call_user_func_array([$this, $method], [$node]);
	}
}

class Interpreter {
	use NodeVisitor;

	public $GLOBAL_SCOPE = [];

	public function __construct($parser) {
		$this->parser = $parser;
	}

	public function visit_BinOp($node) {
		switch($node->operator->type) {
			case Token::PLUS: return $this->visit($node->left) + $this->visit($node->right);
			case Token::MINUS: return $this->visit($node->left) - $this->visit($node->right);
			case Token::MUL: return $this->visit($node->left) * $this->visit($node->right);
			case Token::DIV: return $this->visit($node->left) / $this->visit($node->right);
		}		
	}

	public function visit_Num($node) {
		return $node->value;
	}

	public function visit_UnaryOp($node) {
		switch ($node->operator->type) {
			case Token::PLUS: return +$this->visit($node->right); break;
			case Token::MINUS: return -$this->visit($node->right); break;
		}
	}

	public function visit_Compound($node) {
		foreach ($node->children as $child) {
			$this->visit($child);
		}
	}

	public function visit_NoOp($node) {
		// No Operation!
	}


	public function visit_Assign($node) {
		$varName = $node->left->value;
		$this->GLOBAL_SCOPE[$varName] = $this->visit($node->right);
	}

	public function visit_Variable($node) {
		$varName = $node->value;
		if (isset($this->GLOBAL_SCOPE[$varName])) {
			return $this->GLOBAL_SCOPE[$varName];
		} else {
			/***************************************************************************************
			 * THIS ERROR SHOULD BE AN ERROR OF THE INTERPRETED PROGRAM NOT AN EXCEPTION FROM PHP  *
			 * I'll wait to see if it will be solved by the author, otherwise i'll solve it myself *
			 ***************************************************************************************/
			throw new Exception("The variable $varName does not exist!");
		}
		
	}

	public function interpret() {
		$tree = $this->parser->parse();
		return $this->visit($tree);
	}
}