# This is a basic pascal interpreter builded in PHP

#### The grammar used is:

    program : compound_statement DOT
    compound_statement : BEGIN statement_list END
    statement_list : statement
                | statement SEMI statement_list
    statement : compound_statement
           | assignment_statement
           | empty
    assignment_statement : variable ASSIGN expr
    empty :
    expr: term ((PLUS | MINUS) term)*
    term: factor ((MUL | DIV) factor)*
    factor : PLUS factor
        | MINUS factor
        | INTEGER
        | LPAREN expr RPAREN
        | variable
    variable: ID

#### HOW TO USE:

* **To interpret a source file:**

        ./pascal.php <sourcecode.pas>

    This will interpret te sourcecode.pas ans show the state of variables at the end of execution.


* **To test the Lexer use like this:**

        ./pascal.php --tokenize <sourcecode.pas>
        
    This will show the content of soucecode.pas and bellow all the tokens found, one each line.


* **To test the Parser use like this:**

        ./pascal.php --dot <sourcecode.pas> | dot -Tsvg > output.svg

	This will produce parse the sourcecode.pas, generate a DOT language stile graph,
	pass it to dot binary, that will produce a SVG image source, and finally write
	the SVG image source to output.svg file.

	The graph generated is a DOT style graph showing the "Abstract Syntax Tree".
	That is used as an Intermediate Representation (IR) by the interpreter.
	
	***Remember, you need to install GraphViz package on your distribution!***