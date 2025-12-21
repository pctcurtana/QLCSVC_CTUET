<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Screen extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'route',
        'icon',
        'parent_id',
        'order',
        'is_active',
        'is_menu',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_menu' => 'boolean',
    ];

    /**
     * Parent screen (đệ quy)
     */
    public function parent()
    {
        return $this->belongsTo(Screen::class, 'parent_id');
    }

    /**
     * Children screens (đệ quy)
     */
    public function children()
    {
        return $this->hasMany(Screen::class, 'parent_id')->orderBy('order');
    }

    /**
     * Đệ quy lấy tất cả children và children của children
     */
    public function allChildren()
    {
        return $this->children()->with('allChildren');
    }

    /**
     * User permissions cho screen này
     */
    public function userPermissions()
    {
        return $this->hasMany(UserPermission::class);
    }

    /**
     * Lấy tất cả screens dạng tree
     */
    public static function getTree()
    {
        return self::whereNull('parent_id')
            ->where('is_active', true)
            ->orderBy('order')
            ->with('allChildren')
            ->get();
    }

    /**
     * Lấy tất cả screens dạng flat (phẳng) với level
     */
    public static function getFlatTree($parentId = null, $level = 0)
    {
        $result = [];
        $screens = self::where('parent_id', $parentId)
            ->where('is_active', true)
            ->orderBy('order')
            ->get();

        foreach ($screens as $screen) {
            $screen->level = $level;
            $result[] = $screen;
            $result = array_merge($result, self::getFlatTree($screen->id, $level + 1));
        }

        return $result;
    }

    /**
     * Lấy tất cả parent IDs (đệ quy lên trên)
     */
    public function getParentIds()
    {
        $parentIds = [];
        $parent = $this->parent;
        
        while ($parent) {
            $parentIds[] = $parent->id;
            $parent = $parent->parent;
        }
        
        return $parentIds;
    }

    /**
     * Lấy tất cả children IDs (đệ quy xuống dưới)
     */
    public function getChildrenIds()
    {
        $childrenIds = [];
        
        foreach ($this->children as $child) {
            $childrenIds[] = $child->id;
            $childrenIds = array_merge($childrenIds, $child->getChildrenIds());
        }
        
        return $childrenIds;
    }
}


