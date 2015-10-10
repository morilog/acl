<?php
namespace Morilog\Acl\Middleware;

use Closure;
use Morilog\Acl\Acl as AclManager;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class AclCheck {

    /**
     * @var AclManager
     */
    private $aclManager;

    public function __construct(AclManager $aclManager)
    {
        $this->aclManager = $aclManager;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $routeName = $request->route()->getName();

        if ($this->aclManager->hasAccess($routeName) === false) {
            throw new AccessDeniedHttpException;
        }


        return $next($request);
    }

}
