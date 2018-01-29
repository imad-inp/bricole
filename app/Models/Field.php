<?php
/**
 * LaraClassified - Geo Classified Ads CMS
 * Copyright (c) BedigitCom. All Rights Reserved
 *
 * Website: http://www.bedigit.com
 *
 * LICENSE
 * -------
 * This software is furnished under a license and may be used and copied
 * only in accordance with the terms of such license and with the inclusion
 * of the above copyright notice. If you Purchased from Codecanyon,
 * Please read the full License from here - http://codecanyon.net/licenses/standard
 */

namespace App\Models;

use App\Models\Scopes\ActiveScope;
use App\Models\Traits\TranslatedTrait;
use App\Observer\FieldObserver;

class Field extends BaseModel
{
    use TranslatedTrait;
    
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'fields';
    
    /**
     * The primary key for the model.
     *
     * @var string
     */
    // protected $primaryKey = 'id';
    protected $appends = ['tid', 'options'];
    
    /**
     * Indicates if the model should be timestamped.
     *
     * @var boolean
     */
    public $timestamps = false;
    
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'belongs_to',
        'translation_lang',
        'translation_of',
        'name',
        'type',
        'max',
        'default',
        'required',
        'help',
        'active',
    ];
    public $translatable = ['name', 'default', 'help'];
    
    /**
     * The attributes that should be hidden for arrays
     *
     * @var array
     */
    // protected $hidden = [];
    
    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    // protected $dates = [];
    
    /*
    |--------------------------------------------------------------------------
    | FUNCTIONS
    |--------------------------------------------------------------------------
    */
    protected static function boot()
    {
        parent::boot();
	
		Field::observe(FieldObserver::class);
		
        static::addGlobalScope(new ActiveScope());
    }
    
    public function getNameHtml()
    {
        $out = '';
    
        $currentUrl = preg_replace('#/(search)$#', '', url()->current());
        $editUrl = $currentUrl . '/' . $this->id . '/edit';
        
        $out .= '<a href="' . $editUrl . '" style="float:left;">' . $this->name . '</a>';
        $out .= ' ';
        $out .= '<span style="float:right;">';
        if (isset($this->type) && in_array($this->type, ['select', 'radio', 'checkbox_multiple'])) {
            $optionUrl = url(config('larapen.admin.route_prefix', 'admin') . '/field/' . $this->id . '/option');
            $out .= '<a class="btn btn-xs btn-danger" href="' . $optionUrl . '"><i class="fa fa-cog"></i> ' . ucfirst(__t('options')) . '</a>';
        }
        $catLinkUrl = url(config('larapen.admin.route_prefix', 'admin') . '/field/' . $this->id . '/category/create');
        $out .= ' ';
        $out .= '<a class="btn btn-xs btn-primary" href="' . $catLinkUrl . '"><i class="fa fa-plus"></i> ' . __t('Add to a Category') . '</a>';
        $out .= '</span>';
        
        return $out;
    }
    
    public function getRequiredHtml()
    {
        if (!isset($this->required)) return false;
        
        return checkboxDisplay($this->required);
    }
    
    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */
    public function lang()
    {
        return $this->hasOne(Category::class, 'translation_of', 'abbr');
    }
    
    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }
    
    /*
    public function options()
    {
        return $this->hasMany(FieldOption::class, 'field_id');
    }
    */
    
    /*
    |--------------------------------------------------------------------------
    | SCOPES
    |--------------------------------------------------------------------------
    */
    
    /*
    |--------------------------------------------------------------------------
    | ACCESORS
    |--------------------------------------------------------------------------
    */
    public function getOptionsAttribute()
    {
        // Get the Custom Field's Options
        $options = collect([]);
        if (in_array($this->type, ['checkbox_multiple', 'select', 'radio'])) {
            $options = FieldOption::where('translation_lang', $this->translation_lang)
                ->where('field_id', $this->tid)
                ->orderBy('lft', 'ASC')
                ->orderBy('value', 'ASC')
                ->get()
                ->keyBy('translation_of');
        }
        
        return $options;
    }
    
    /*
    |--------------------------------------------------------------------------
    | MUTATORS
    |--------------------------------------------------------------------------
    */
}
