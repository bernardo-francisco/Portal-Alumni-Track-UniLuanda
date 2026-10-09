<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

abstract class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    protected function success($message, $route = null)
    {
        if ($route) {
            return redirect()->route($route)->with('success', $message);
        }
        return back()->with('success', $message);
    }

    protected function error($message, $route = null)
    {
        if ($route) {
            return redirect()->route($route)->with('error', $message);
        }
        return back()->with('error', $message);
    }

    protected function flashSuccess($message)
    {
        session()->flash('success', $message);
    }

    protected function flashError($message)
    {
        session()->flash('error', $message);
    }
}