# CSSdoc: How it works

Under the hood, the software processes are split into a number of stages:

## Tokenisation

The input CSS is loaded into the tokeniser as a string, and a regular expression splits it up into categorised tokens.

## Parsing

The tokens are passed to the parser which then loops through and consumes each token through an object based Finite State Machine to create an internal object structure that represents the document. This enables irregular tokens to be ignored and the document to be parsed with more reliability.

Once parsed, the document will contain an array of child objects each representing each rule or declaration, which will in turn have their own child documents, rules, selectors, properties and values.

## Minification

Minification is performed by each object on its own structure, the command passed down each level from the one above.

## Compiling

The compilation process reconstructs the CSS from its object representation. Each object generates itself as a string, and then requests its children generate themselves. The result is all concatenated together and either output as a string or saved to the requested location.
