<?php declare(strict_types=1);

namespace Automation\Framework\Http;

use Automation\Framework\Application;
use Automation\Framework\Http\Bags\ServerBag;

class Request
{
    private array $headers;

    private string $uri;

    private string $base_uri;

    private string $path;

    private string $base_path;

    private string $method;

    private array $post;

    private string|null $old = null;

    public function __construct(
        private Application $app,
        private ServerBag $server
    ) {

    }

    public function parse(): void
    {
        foreach ($this->server->all() as $key => $value):
            if (str_starts_with($key, 'HTTP_')):
                $this->headers[$key] = $value;
            endif;
        endforeach;

        $script_name    = $this->server->get('SCRIPT_NAME');
        $request_uri    = trim($this->server->get('REQUEST_URI'), '\\/');
        $request_scheme = $this->server->get('REQUEST_SCHEME');
        $server_name    = $this->server->get('SERVER_NAME');

        $this->base_path = substr($script_name, 0, -strlen(basename($script_name)));
        $this->base_uri  = sprintf('%s://%s%s', $request_scheme, $server_name, $this->base_path);
        $this->uri       = sprintf('%s/%s', $this->base_uri, $request_uri);
        $this->path      = substr($this->uri, strlen($this->base_path) + strlen($this->base_uri));

        $this->method    = $this->server->get('REQUEST_METHOD');
        $this->post      = $_POST;
    }

    public function simulate(string $method, string $path, array $headers = []): void
    {
        $this->method  = strtoupper($method);
        $this->path    = trim($path, '\\/');
        $this->headers = $headers;
    }

    public function method(): string
    {
        return $this->method;
    }

    public function headers(): array
    {
        return $this->headers;
    }

    public function uri(): string
    {
        return $this->uri;
    }

    public function base_uri(): string
    {
        return $this->base_uri;
    }

    public function path(): string
    {
        return $this->path;
    }

    public function flash(): void
    {
        foreach ($this->post as $key => $value) {
            $this->app->session->set($key, $value);
        }
    }

    public function flash_except($except): void
    {
        $except = is_array($except) ? $except : func_get_args();

        $key = array_map(function ($key) use ($except) {
            if (in_array($key, $except)) return $key;
        }, array_keys($this->post));

        $except = reset($key);

        unset($this->post[$except]);

        $this->flash();
    }

    public function old(string $key): string|null
    {
        if ($this->app->session->missing($key)) {
            return $this->old;
        }

        $this->old = $this->app->session->pull($key);

        return $this->old;
    }
}