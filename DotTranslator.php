<?php

class DotTranslator {
	public static $obj = [];

	public function visit($node) {
		$method = 'visit_'.get_class($node);
		return call_user_func_array([$this, $method], [$node]);
	}

	public function visit_BinOp($node) {
		self::$obj[$node->id] = $node;
		
		echo $node->id, "[label=\"".$node->operator->value."\"]\n";
		//echo $node->id, " -- ", $node->operator->id,"\n";
		echo $node->id, " -- { ", $node->left->id, " ", $node->right->id, " }\n";
		$this->visit($node->left);
		$this->visit($node->right);
	}

	public function visit_Num($node) {
		self::$obj[$node->id] = $node;

		echo $node->id, "[label=\"".$node->token->value."\"]\n";
		//echo $node->id, ' -- ', $node->token->id,"\n";
	}

	public function visit_UnaryOp($node) {
		self::$obj[$node->id] = $node;

		echo $node->id, "[label=\"".$node->operator->value."\"]\n";
		echo $node->id, " -- ", $node->right->id,"\n";
		$this->visit($node->right);
	}

	public function visit_Compound($node) {
		self::$obj[$node->id] = $node;

		echo $node->id, "[label=\"BEGIN ... END\"]\n";

		foreach ($node->children as $child) {
			echo $node->id, " -- ", $child->id, "\n";
			$this->visit($child);
		}
	}

	public function visit_NoOp($node) {
		echo $node->id, "[label=\"NoOp\"]\n";
	}


	public function visit_Assign($node) {
		self::$obj[$node->id] = $node;

		echo $node->id, "[label=\"".$node->operator->value."\"]\n";

		echo $node->id, " -- { ", $node->left->id, " ", $node->right->id, " }\n";
		$this->visit($node->left);
		$this->visit($node->right);
	}

	public function visit_Variable($node) {
		self::$obj[$node->id] = $node;

		echo $node->id, "[label=\"".$node->token->value."\"]\n";
		//echo $node->id, ' -- ', $node->token->id,"\n";
	}


	public function translate() {
		$tree = $this->parser->parse();
		echo "graph \"Abstract Sintax Tree\" {\n";
		$this->visit($tree);
		echo "}\n";
	}

	public function __construct($parser) {
		$this->parser = $parser;
	}
}
