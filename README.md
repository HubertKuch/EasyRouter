# EasyRouter
EasyRouter is small PHP framework to fast build API.

## Import

    use EasyRouter\Router;  
    use EasyRouter\Request; 
    use EasyRouter\Response;  
## Implemented HTTP methods
  Easy router impelment four popular HTTP methods: GET, POST, PATCH, DELETE.
  
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
    GET(string $endpoint, callable $callback): void
    POST(string $endpoint, callable $callback): void
    DELETE(string $endpoint, callable $callback): void
    PATCH(string $endpoint, callable $callback): void
    listen(): void
    
## Listen usage
On the end of declaring API call listen() method on Router object and then routes will be active.
    
