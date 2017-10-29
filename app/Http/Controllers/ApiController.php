<?php
/**
 * Created by PhpStorm.
 * User: diwin
 * Date: 2017. 10. 24.
 * Time: 21:25
 */

namespace App\Http\Controllers;

/**
 * @SWG\Swagger(
 *   basePath="/",
 *   produces={"application/json"},
 *   consumes={"application/json"},
 *   @SWG\Info(
 *     title="Poke.me API",
 *     version="1.0.0"
 *   )
 * )
 */
class ApiController extends Controller
{
    /**
     * Create a new controller instance.
     *
     */
    public function __construct()
    {
        $this->middleware('auth:api');
    }
}