<?php
class Rest
{
    private $request;
    private $baseEntry = '/api';

    public function __construct()
    {
        $this->request = new Request();
    }

    public function setBaseEntry($entry)
    {
        $this->baseEntry = $entry;
    }

    private function _validate_path($entry)
    {
        $paths = explode('/', $this->request->getPath());
        $ps = explode('/', $this->baseEntry . $entry);
        if (count($paths) != count($ps)) {
            return false;
        }
        for ($i = 0; $i < count($ps); $i++) {
            if ($ps[$i] !== $paths[$i] && $ps[$i][0] != ':') {
                return false;
            }
        }
        return true;
    }

    private function HandleRequest($callable, $middlewares, $entry)
    {
        $data = [];
        foreach ($middlewares as $middleware) {
            if (strpos($middleware, '.') !== false) {
                list($class, $middleware) = explode('.', $middleware);
            }
            if (preg_match('/\((.*?)\)/', $middleware, $match) == 1) {
                $params = $match[1] === '' ? [] : explode(',', $match[1]);
                foreach ($params as &$p) {
                    $p = trim($p);
                }
                $middleware = explode('(', $middleware)[0];
            } else {
                $params = [];
            }
            if ($class !== null) {
                $mdat = $class::{$middleware}($this->request, $params);
            } else {
                $mdat = $middleware($this->request, $params);
            }

            if (!is_array($mdat)) {
                continue;
            }
            foreach ($mdat as $name => $val) {
                $data[$name] = $val;
            }
        }

        $pathdata = [];
        if (strpos($entry, ':') !== false) {
            $paths = explode('/', $this->request->getPath());
            $ps = explode('/', $this->baseEntry . $entry);
            $pathdata = [];
            for ($i = 0; $i < count($ps); $i++) {
                if (!empty($ps[$i]) && $ps[$i][0] == ':') {
                    $pathdata[substr($ps[$i], 1)] = $paths[$i];
                }
            }
        }

        if (!is_callable($callable) && strpos($callable, '.') !== false) {
            $callable = explode('.', $callable);
            $callable[0]::{$callable[1]}(new RestData($this->request, $data, $pathdata));
        } else {
            $callable(new RestData($this->request, $data, $pathdata));
        }
    }

    private function checkData($arr)
    {
        return empty(array_diff_key($this->request->getBody(), $arr));
    }

    public function get($entry, $callable, $middlewares = [])
    {
        if ($this->request->getMethod() != 'GET' || !$this->_validate_path($entry)) {
            return;
        }
        $this->HandleRequest($callable, $middlewares, $entry);
    }

    public function post($entry, $callable, $middlewares = [])
    {
        if ($this->request->getMethod() != 'POST' || !$this->_validate_path($entry)) {
            return;
        }
        $this->HandleRequest($callable, $middlewares, $entry);
    }

    public function put($entry, $callable, $middlewares = [])
    {
        if ($this->request->getMethod() != 'PUT' || !$this->_validate_path($entry)) {
            return;
        }
        $this->HandleRequest($callable, $middlewares, $entry);
    }

    public function delete($entry, $callable, $middlewares = [])
    {
        if ($this->request->getMethod() != 'DELETE' || !$this->_validate_path($entry)) {
            return;
        }
        $this->HandleRequest($callable, $middlewares, $entry);
    }

    public function patch($entry, $callable, $middlewares = [])
    {
        if ($this->request->getMethod() != 'PATCH' || !$this->_validate_path($entry)) {
            return;
        }
        $this->HandleRequest($callable, $middlewares, $entry);
    }

    public function resource($entry, $callable, $middlewares = [])
    {
        if (!$this->_validate_path($entry)) {
            return;
        }
        $this->HandleRequest($callable, $middlewares, $entry);
    }
}

class RestData
{
    public Request $request;
    public array $middleware;
    public array $pathdata;

    public function __construct($request, $middleware, $pathdata)
    {
        $this->request = $request;
        $this->middleware = $middleware;
        $this->pathdata = $pathdata;
    }
}

class Request
{
    private $method;
    private $path;
    private $query;
    private $headers;
    private array $body;

    public function __construct(String $method = null, String $path = null, String $query = null, array $headers = null, String $body = null)
    {
        if (isset($method)) {
            $this->method = $method;
        } else {
            $this->setMethod();
        }

        if (!isset($path) && !isset($query)) {
            $this->setPathAndQuery();
        } else {
            if (isset($path)) {
                $this->path = $path;
            } else {
                $this->setPath();
            }

            if (isset($query)) {
                $this->query = $query;
            } else {
                $this->setQuery();
            }
        }


        if (isset($headers)) {
            $this->headers = $headers;
        } else {
            $this->setHeaders();
        }

        if (isset($body)) {
            $this->body = $body;
        } else {
            $this->setBody();
        }
    }

    public function getMethod()
    {
        return $this->method;
    }

    public function getPath()
    {
        return $this->path;
    }

    public function getQuery()
    {
        return $this->query;
    }

    public function getHeaders()
    {
        return $this->headers;
    }

    public function getBody(): array
    {
        return $this->body;
    }

    private function setMethod()
    {
        $this->method = strtoupper($_SERVER['REQUEST_METHOD']);
    }

    private function setPath()
    {
        $this->path = isset($_SERVER['REQUEST_URI']) ? explode('?', $_SERVER['REQUEST_URI'])[0] : '';
    }

    private function setPathAndQuery()
    {
        $this->setPath();
        $this->setQuery();
    }

    private function setQuery()
    {
        if (strpos($_SERVER['REQUEST_URI'], '?') !== false) {
            $path = explode('&', explode('?', $_SERVER['REQUEST_URI'])[1]);
            $query = [];
            foreach ($path as $p) {
                $split = explode('=', $p);
                if (isset($query[$split[0]])) {
                    if (is_array($query[$split[0]])) {
                        $query[$split[0]][] = ($split[1] ? $split[1] : '');
                    } else {
                        $query[$split[0]] = [$query[$split[0]], ($split[1] ? $split[1] : '')];
                    }
                } else {
                    $query[$split[0]] = (isset($split[1]) ? $split[1] : '');
                }
            }
            $this->query = $query;
        } else {
            $this->query = [];
        }
    }

    private function setHeaders()
    {
        $this->headers = getallheaders();
    }

    private function setBody()
    {
        $this->body = [];
        $body = file_get_contents('php://input');
        if ($body == "") {
            return;
        }
        if ($body[0] == "{" || $body[0] == "[") {
            $this->body = json_decode($body, true);
        } else {
            $body = explode('&', $body);
            foreach ($body as $b) {
                $split = explode('=', $b);
                if (!isset($this->body[$split[0]])) {
                    $this->body[$split[0]] = ($split[1] ? $split[1] : "");
                } elseif (is_string($this->body[$split[0]])) {
                    $this->body[$split[0]] = [$this->body[$split[0]], ($split[1] ? $split[1] : "")];
                } elseif (is_array($this->body[$split[0]])) {
                    $this->body[$split[0]][] = ($split[1] ? $split[1] : "");
                }
            }
        }
    }
}
