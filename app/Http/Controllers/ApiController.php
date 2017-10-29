<?php
/**
 * Created by PhpStorm.
 * User: diwin
 * Date: 2017. 10. 24.
 * Time: 21:25
 */

namespace App\Http\Controllers;


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