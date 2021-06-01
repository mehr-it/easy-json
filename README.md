# Easy JSON

## Parsing JSON files

Here is a simple example how to parse large JSON files:

    (new JsonParser($fh))
        ->eachItem('Library.Books', function($parser, $index) {
            // this callback will be invoked for each book            

            $parser
                ->value('Author', $author)
                ->value('ISBN', $isbn)
                ->consume();

            // do s.th. with author and ISBN data
 
        })
        ->parse();

The following example shows how values can be collected:

    (new JsonParser($fh))
        ->collectItemValues('Library.Books.ISBN', $isbns)
        ->parase();

    // $isbns now holds the ISBNs of all books

**_TODO:_** add more examples and details

## Writing JSON files
**_not implemented yet!_**