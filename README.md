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

Here is a simple example how to write JSON files:

    $builder = new JsonBuilder($fh);

    $builder->write([
        'version' => '1.0',
        'users' => new JsonArray(function(JsonBuilder $builder) {
            foreach(getUsers() as $currUser) {
                yield ['name' => $currUser->name];
            }
        }),
    ]);

To force a specific JSON data type, you can use the following
classes:

* `JsonArray`
* `JsonObject`
* `JsonNumber`

### Resources

You can write a resource as JSON string without reading all the
stream data into memory:

    $builder->write(new JsonResourceString($fh))

You can optionally encode the data as JSON:

    $builder->write(new JsonResourceString($fh, true))
