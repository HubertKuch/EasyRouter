# EasyRouter
EasyRouter is small PHP framework to fast build API.

## Import

    use EasyRouter\Router;  
    use EasyRouter\Request; 
    use EasyRouter\Reponse;

## Sample .htaccess file (required)
    rewriteEngine on
    RewriteCond %{SCRIPT_FILENAME} !-f
    RewriteCond %{SCRIPT_FILENAME} !-d
    RewriteCond %{SCRIPT_FILENAME} !-l
    RewriteRule ^(.*)$ index.php/$1

## Implemented HTTP methods
  Easy router implement four popular HTTP methods: GET, POST, PATCH, DELETE.
  
## Syntax
    Router::<method>(<endpoint>, <callback>);

    Router::listen();

## Example
    Router::GET('/api/v1/users', function(Request $req, Response $res){
        try {
            $users = $userRepo->findAll();
            
            $res 
                -> json($users)
                -> withStatus(200);
        } catch(ExampleException $e) {
            $res 
                -> json(makeErrorResponse("error"))
                -> withStatus(400);
        }
    });
    
## Response class
### Attributes
Response doesn't have any attributes.

### Methods:
    write($data)
    json(array $data): Response
    use(): void    

    withStatus(int $status): Response
    withCookie(string $key, string $value, ?array $options = array()): Response
    
    setHeader(string $key, string $value): Response
    
## Request class
### Attributes
    public array $body;
    public array $query;
    public array $cookies;
    public array $params;
    public array $headers;
### Methods
Request doesn't have any methods.

## Router class

### Attributes
Router doesn't have any public attributes.

### Methods
    use($setting): void
    middleware($middleware, callable $callback): void
    GET(string $endpoint, callable $callback): void
    POST(string $endpoint, callable $callback): void
    DELETE(string $endpoint, callable $callback): void
    PATCH(string $endpoint, callable $callback): void
    listen(): void
    
## Listen usage
On the end of declaring API call listen() method on Router object and then routes will be active.

## Use method
### Decoding JSON
If you want to decode json post body you can call this:

    Router::use(Router::JSON());

then your data will be in:

    $request->body[]; 

## Middleware
### What is middleware?
Middleware is a function who will be called before router callback,
if middleware returns true router callback will be call also when
middleware returns false you can specify error message and callback
was not called.

### How works middleware in EasyRouter?
#### Examples
If first callback returns true second callback will be called
otherwise not.

    Router::middleware(function(){ return true; }, function(){
        Router::GET('/', function(Request $req, Response $res){});
        Router::GET('/:id', function(Request $req, Response $res){});
    });

When first and second returns true then callback function will be called.

    Router::middleware([first, secend], function(){
        Router::GET('/', function(Request $req, Response $res){});
    });
