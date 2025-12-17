<?php

namespace App\Traits;

/**
 * Trait Viewable
 * يوفر وظيفة تتبع المشاهدات للموديلات
 */
trait Viewable
{
    /**
     * زيادة عداد المشاهدات
     */
    public function incrementViews(): void
    {
        $this->increment('views');
    }

    /**
     * الحصول على عدد المشاهدات
     */
    public function getViewsCount(): int
    {
        return $this->views ?? 0;
    }

    /**
     * تسجيل المشاهدة (يمكن توسيعها لتتبع الزوار الفريدين)
     */
    public function recordView(): void
    {
        $this->incrementViews();
    }
}
