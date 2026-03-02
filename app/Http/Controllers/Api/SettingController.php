<?php

namespace App\Http\Controllers\Api;
use App\Models\Page;

class SettingController extends ApiController
{
    public function getAboutUs()
    {
        $page = Page::where('type', Page::TYPE_ABOUT_US)->where('is_active', true)->first();
        return $this->successResponse($page);

    }

    public function getAboutService()
    {
        $page = Page::where('type', Page::TYPE_ABOUT_SERVICE)->where('is_active', true)->first();
        return $this->successResponse($page);
    }

    public function getPolicy()
    {
        $page = Page::where('type', Page::TYPE_POLICY)->where('is_active', true)->first();
        return $this->successResponse($page);
    }
}
